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

use League\Common_Mark\Parser\Block\Block_Start;
use League\Common_Mark\Parser\Block\Block_Start_Parser_Interface;
use League\Common_Mark\Parser\Block\Paragraph_Parser;
use League\Common_Mark\Parser\Cursor;
use League\Common_Mark\Parser\Markdown_Parser_State_Interface;
final class Table_Start_Parser implements Block_Start_Parser_Interface
{
    private int $max_autocompleted_cells;
    public function __construct(int $max_autocompleted_cells = Table_Parser::DEFAULT_MAX_AUTOCOMPLETED_CELLS)
    {
        $this->max_autocompleted_cells = $max_autocompleted_cells;
    }
    public function try_start(Cursor $cursor, Markdown_Parser_State_Interface $parser_state): ?Block_Start
    {
        $paragraph = $parser_state->get_paragraph_content();
        if ($paragraph === null || !str_contains($paragraph, '|')) {
            return Block_Start::none();
        }
        $columns = self::parse_separator($cursor);
        if (\count($columns) === 0) {
            return Block_Start::none();
        }
        $last_line_break = \strrpos($paragraph, "\n");
        $last_line = $last_line_break === false ? $paragraph : \substr($paragraph, $last_line_break + 1);
        $header_cells = Table_Parser::split($last_line);
        if (\count($header_cells) > \count($columns)) {
            return Block_Start::none();
        }
        $cursor->advance_to_end();
        $parsers = [];
        if ($last_line_break !== false) {
            $p = new Paragraph_Parser();
            $p->add_line(\substr($paragraph, 0, $last_line_break));
            $parsers[] = $p;
        }
        $parsers[] = new Table_Parser($columns, $header_cells, $this->max_autocompleted_cells);
        return Block_Start::of(...$parsers)->at($cursor)->replace_active_block_parser();
    }
    /**
     * @return array<int, string|null>
     *
     * @psalm-return array<int, TableCell::ALIGN_*|null>
     *
     * @phpstan-return array<int, TableCell::ALIGN_*|null>
     */
    private static function parse_separator(Cursor $cursor): array
    {
        $columns = [];
        $pipes = 0;
        $valid = false;
        while (!$cursor->is_at_end()) {
            switch ($c = $cursor->get_current_character()) {
                case '|':
                    $cursor->advance_by(1);
                    $pipes++;
                    if ($pipes > 1) {
                        // More than one adjacent pipe not allowed
                        return [];
                    }
                    // Need at least one pipe, even for a one-column table
                    $valid = true;
                    break;
                case '-':
                case ':':
                    if ($pipes === 0 && \count($columns) > 0) {
                        // Need a pipe after the first column (first column doesn't need to start with one)
                        return [];
                    }
                    $left = false;
                    $right = false;
                    if ($c === ':') {
                        $left = true;
                        $cursor->advance_by(1);
                    }
                    if ($cursor->match('/^-+/') === null) {
                        // Need at least one dash
                        return [];
                    }
                    if ($cursor->get_current_character() === ':') {
                        $right = true;
                        $cursor->advance_by(1);
                    }
                    $columns[] = self::get_alignment($left, $right);
                    // Next, need another pipe
                    $pipes = 0;
                    break;
                case ' ':
                case "\t":
                    // White space is allowed between pipes and columns
                    $cursor->advance_to_next_non_space_or_tab();
                    break;
                default:
                    // Any other character is invalid
                    return [];
            }
        }
        if (!$valid) {
            return [];
        }
        return $columns;
    }
    /**
     * @psalm-return TableCell::ALIGN_*|null
     *
     * @phpstan-return TableCell::ALIGN_*|null
     *
     * @psalm-pure
     */
    private static function get_alignment(bool $left, bool $right): ?string
    {
        if ($left && $right) {
            return Table_Cell::ALIGN_CENTER;
        }
        if ($left) {
            return Table_Cell::ALIGN_LEFT;
        }
        if ($right) {
            return Table_Cell::ALIGN_RIGHT;
        }
        return null;
    }
}