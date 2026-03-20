<?php

declare (strict_types=1);
/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace League\Common_Mark\Extension\Common_Mark\Parser\Block;

use League\Common_Mark\Extension\Common_Mark\Node\Block\Indented_Code;
use League\Common_Mark\Parser\Block\Abstract_Block_Continue_Parser;
use League\Common_Mark\Parser\Block\Block_Continue;
use League\Common_Mark\Parser\Block\Block_Continue_Parser_Interface;
use League\Common_Mark\Parser\Cursor;
use League\Common_Mark\Util\Array_Collection;
final class Indented_Code_Parser extends Abstract_Block_Continue_Parser
{
    /** @psalm-readonly */
    private Indented_Code $block;
    /** @var ArrayCollection<string> */
    private Array_Collection $strings;
    public function __construct()
    {
        $this->block = new Indented_Code();
        $this->strings = new Array_Collection();
    }
    public function get_block(): Indented_Code
    {
        return $this->block;
    }
    public function try_continue(Cursor $cursor, Block_Continue_Parser_Interface $active_block_parser): ?Block_Continue
    {
        if ($cursor->is_indented()) {
            $cursor->advance_by(Cursor::INDENT_LEVEL, true);
            return Block_Continue::at($cursor);
        }
        if ($cursor->is_blank()) {
            $cursor->advance_to_next_non_space_or_tab();
            return Block_Continue::at($cursor);
        }
        return Block_Continue::none();
    }
    public function add_line(string $line): void
    {
        $this->strings[] = $line;
    }
    public function close_block(): void
    {
        $lines = $this->strings->to_array();
        // Note that indented code block cannot be empty, so $lines will always have at least one non-empty element
        while (\preg_match('/^[ \t]*$/', \end($lines))) {
            // @phpstan-ignore-line
            \array_pop($lines);
        }
        $this->block->set_literal(\implode("\n", $lines) . "\n");
        $this->block->set_end_line($this->block->get_start_line() + \count($lines) - 1);
    }
}