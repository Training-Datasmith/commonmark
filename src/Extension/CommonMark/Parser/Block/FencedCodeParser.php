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

use League\Common_Mark\Extension\Common_Mark\Node\Block\Fenced_Code;
use League\Common_Mark\Parser\Block\Abstract_Block_Continue_Parser;
use League\Common_Mark\Parser\Block\Block_Continue;
use League\Common_Mark\Parser\Block\Block_Continue_Parser_Interface;
use League\Common_Mark\Parser\Cursor;
use League\Common_Mark\Util\Array_Collection;
use League\Common_Mark\Util\Regex_Helper;
final class Fenced_Code_Parser extends Abstract_Block_Continue_Parser
{
    /** @psalm-readonly */
    private Fenced_Code $block;
    /** @var ArrayCollection<string> */
    private Array_Collection $strings;
    public function __construct(int $fence_length, string $fence_char, int $fence_offset)
    {
        $this->block = new Fenced_Code($fence_length, $fence_char, $fence_offset);
        $this->strings = new Array_Collection();
    }
    public function get_block(): Fenced_Code
    {
        return $this->block;
    }
    public function try_continue(Cursor $cursor, Block_Continue_Parser_Interface $active_block_parser): \League\Common_Mark\Parser\Block\Block_Continue
    {
        // Check for closing code fence
        if (!$cursor->is_indented() && $cursor->get_next_non_space_character() === $this->block->get_char()) {
            $match = Regex_Helper::match_first('/^(?:`{3,}|~{3,})(?=[ \t]*$)/', $cursor->get_line(), $cursor->get_next_non_space_position());
            if ($match !== null && \strlen($match[0]) >= $this->block->get_length()) {
                // closing fence - we're at end of line, so we can finalize now
                return Block_Continue::finished();
            }
        }
        // Skip optional spaces of fence offset
        // Optimization: don't attempt to match if we're at a non-space position
        if ($cursor->get_next_non_space_position() > $cursor->get_position()) {
            $cursor->match('/^ {0,' . $this->block->get_offset() . '}/');
        }
        return Block_Continue::at($cursor);
    }
    public function add_line(string $line): void
    {
        $this->strings[] = $line;
    }
    public function close_block(): void
    {
        // first line becomes info string
        $first_line = $this->strings->first();
        if ($first_line === false) {
            $first_line = '';
        }
        $this->block->set_info(Regex_Helper::unescape(\trim($first_line)));
        if ($this->strings->count() === 1) {
            $this->block->set_literal('');
        } else {
            $this->block->set_literal(\implode("\n", $this->strings->slice(1)) . "\n");
        }
    }
}