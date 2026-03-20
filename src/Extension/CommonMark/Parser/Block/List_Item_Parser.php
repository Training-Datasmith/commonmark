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

use League\Common_Mark\Extension\Common_Mark\Node\Block\List_Data;
use League\Common_Mark\Extension\Common_Mark\Node\Block\List_Item;
use League\Common_Mark\Node\Block\Abstract_Block;
use League\Common_Mark\Parser\Block\Abstract_Block_Continue_Parser;
use League\Common_Mark\Parser\Block\Block_Continue;
use League\Common_Mark\Parser\Block\Block_Continue_Parser_Interface;
use League\Common_Mark\Parser\Cursor;
final class List_Item_Parser extends Abstract_Block_Continue_Parser
{
    /** @psalm-readonly */
    private List_Item $block;
    public function __construct(List_Data $list_data)
    {
        $this->block = new List_Item($list_data);
    }
    public function get_block(): List_Item
    {
        return $this->block;
    }
    public function is_container(): bool
    {
        return true;
    }
    public function can_contain(Abstract_Block $child_block): bool
    {
        return !$child_block instanceof List_Item;
    }
    public function try_continue(Cursor $cursor, Block_Continue_Parser_Interface $active_block_parser): ?Block_Continue
    {
        if ($cursor->is_blank()) {
            if ($this->block->first_child() === null) {
                // Blank line after empty list item
                return Block_Continue::none();
            }
            $cursor->advance_to_next_non_space_or_tab();
            return Block_Continue::at($cursor);
        }
        $content_indent = $this->block->get_list_data()->marker_offset + $this->get_block()->get_list_data()->padding;
        if ($cursor->get_indent() >= $content_indent) {
            $cursor->advance_by($content_indent, true);
            return Block_Continue::at($cursor);
        }
        // Note: We'll hit this case for lazy continuation lines, they will get added later.
        return Block_Continue::none();
    }
    public function close_block(): void
    {
        if (($last_child = $this->block->last_child()) instanceof Abstract_Block) {
            $this->block->set_end_line($last_child->get_end_line());
        } else {
            // Empty list item
            $this->block->set_end_line($this->block->get_start_line());
        }
    }
}