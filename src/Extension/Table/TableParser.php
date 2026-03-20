<?php

declare (strict_types=1);
/*
 * This is part of the league/commonmark package.
 *
 * (c) Martin Hasoň <martin.hason@gmail.com>
 * (c) Webuni s.r.o. <info@webuni.cz>
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace League\Common_Mark\Extension\Table;

use League\Common_Mark\Parser\Block\Abstract_Block_Continue_Parser;
use League\Common_Mark\Parser\Block\Block_Continue;
use League\Common_Mark\Parser\Block\Block_Continue_Parser_Interface;
use League\Common_Mark\Parser\Block\Block_Continue_Parser_With_Inlines_Interface;
use League\Common_Mark\Parser\Cursor;
use League\Common_Mark\Parser\Inline_Parser_Engine_Interface;
use League\Common_Mark\Util\Array_Collection;
final class Table_Parser extends Abstract_Block_Continue_Parser implements Block_Continue_Parser_With_Inlines_Interface
{
    /**
     * @internal
     */
    public const DEFAULT_MAX_AUTOCOMPLETED_CELLS = 10000;
    /** @psalm-readonly */
    private Table $block;
    /**
     * @var ArrayCollection<string>
     *
     * @psalm-readonly-allow-private-mutation
     */
    private Array_Collection $body_lines;
    /**
     * @var array<int, string|null>
     * @psalm-var array<int, TableCell::ALIGN_*|null>
     * @phpstan-var array<int, TableCell::ALIGN_*|null>
     *
     * @psalm-readonly
     */
    private array $columns;
    /**
     * @var array<int, string>
     *
     * @psalm-readonly-allow-private-mutation
     */
    private array $header_cells;
    /** @psalm-readonly-allow-private-mutation */
    private bool $next_is_separator_line = true;
    private int $remaining_autocompleted_cells;
    /**
     * @param array<int, string|null> $columns
     * @param array<int, string>      $headerCells
     *
     * @psalm-param array<int, TableCell::ALIGN_*|null> $columns
     *
     * @phpstan-param array<int, TableCell::ALIGN_*|null> $columns
     */
    public function __construct(array $columns, array $header_cells, int $remaining_autocompleted_cells = self::DEFAULT_MAX_AUTOCOMPLETED_CELLS)
    {
        $this->block = new Table();
        $this->body_lines = new Array_Collection();
        $this->columns = $columns;
        $this->header_cells = $header_cells;
        $this->remaining_autocompleted_cells = $remaining_autocompleted_cells;
    }
    public function can_have_lazy_continuation_lines(): bool
    {
        return true;
    }
    public function get_block(): Table
    {
        return $this->block;
    }
    public function try_continue(Cursor $cursor, Block_Continue_Parser_Interface $active_block_parser): ?Block_Continue
    {
        if (!str_contains($cursor->get_line(), '|')) {
            return Block_Continue::none();
        }
        return Block_Continue::at($cursor);
    }
    public function add_line(string $line): void
    {
        if ($this->next_is_separator_line) {
            $this->next_is_separator_line = false;
        } else {
            $this->body_lines[] = $line;
        }
    }
    public function parse_inlines(Inline_Parser_Engine_Interface $inline_parser): void
    {
        $header_columns = \count($this->header_cells);
        $head = new Table_Section(Table_Section::TYPE_HEAD);
        $this->block->append_child($head);
        $header_row = new Table_Row();
        $head->append_child($header_row);
        for ($i = 0; $i < $header_columns; $i++) {
            $cell = $this->header_cells[$i];
            $table_cell = $this->parse_cell($cell, $i, $inline_parser);
            $table_cell->set_type(Table_Cell::TYPE_HEADER);
            $header_row->append_child($table_cell);
        }
        $body = null;
        foreach ($this->body_lines as $row_line) {
            $cells = self::split($row_line);
            $row = new Table_Row();
            // Body can not have more columns than head
            for ($i = 0; $i < $header_columns; $i++) {
                // It can have less columns though, in which case we'll autocomplete the empty ones (up to some limit)
                if (!isset($cells[$i]) && $this->remaining_autocompleted_cells-- <= 0) {
                    // Too many cells were auto-completed, so we'll just stop here
                    return;
                }
                $cell = $cells[$i] ?? '';
                $table_cell = $this->parse_cell($cell, $i, $inline_parser);
                $row->append_child($table_cell);
            }
            if ($body === null) {
                // It's valid to have a table without body. In that case, don't add an empty TableBody node.
                $body = new Table_Section();
                $this->block->append_child($body);
            }
            $body->append_child($row);
        }
    }
    private function parse_cell(string $cell, int $column, Inline_Parser_Engine_Interface $inline_parser): Table_Cell
    {
        $table_cell = new Table_Cell(Table_Cell::TYPE_DATA, $this->columns[$column] ?? null);
        if ($cell !== '') {
            $inline_parser->parse(\trim($cell), $table_cell);
        }
        return $table_cell;
    }
    /**
     * @internal
     *
     * @return array<int, string>
     */
    public static function split(string $line): array
    {
        $cursor = new Cursor(\trim($line));
        if ($cursor->get_current_character() === '|') {
            $cursor->advance_by(1);
        }
        $cells = [];
        $sb = '';
        while (!$cursor->is_at_end()) {
            switch ($c = $cursor->get_current_character()) {
                case '\\':
                    if ($cursor->peek() === '|') {
                        // Pipe is special for table parsing. An escaped pipe doesn't result in a new cell, but is
                        // passed down to inline parsing as an unescaped pipe. Note that that applies even for the `\|`
                        // in an input like `\\|` - in other words, table parsing doesn't support escaping backslashes.
                        $sb .= '|';
                        $cursor->advance_by(1);
                    } else {
                        // Preserve backslash before other characters or at end of line.
                        $sb .= '\\';
                    }
                    break;
                case '|':
                    $cells[] = $sb;
                    $sb = '';
                    break;
                default:
                    $sb .= $c;
            }
            $cursor->advance_by(1);
        }
        if ($sb !== '') {
            $cells[] = $sb;
        }
        return $cells;
    }
}