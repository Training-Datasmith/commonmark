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

use League\Common_Mark\Extension\Common_Mark\Node\Block\Block_Quote;
use League\Common_Mark\Node\Block\Abstract_Block;
use League\Common_Mark\Parser\Block\Abstract_Block_Continue_Parser;
use League\Common_Mark\Parser\Block\Block_Continue;
use League\Common_Mark\Parser\Block\Block_Continue_Parser_Interface;
use League\Common_Mark\Parser\Cursor;
final class Block_Quote_Parser extends Abstract_Block_Continue_Parser
{
    /** @psalm-readonly */
    private Block_Quote $block;
    public function __construct()
    {
        $this->block = new Block_Quote();
    }
    public function get_block(): Block_Quote
    {
        return $this->block;
    }
    public function is_container(): bool
    {
        return true;
    }
    public function can_contain(Abstract_Block $child_block): bool
    {
        return true;
    }
    public function try_continue(Cursor $cursor, Block_Continue_Parser_Interface $active_block_parser): ?Block_Continue
    {
        if (!$cursor->is_indented() && $cursor->get_next_non_space_character() === '>') {
            $cursor->advance_to_next_non_space_or_tab();
            $cursor->advance_by(1);
            $cursor->advance_by_space_or_tab();
            return Block_Continue::at($cursor);
        }
        return Block_Continue::none();
    }
}