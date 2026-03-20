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

use League\Common_Mark\Extension\Common_Mark\Node\Block\List_Block;
use League\Common_Mark\Extension\Common_Mark\Node\Block\List_Data;
use League\Common_Mark\Extension\Common_Mark\Node\Block\List_Item;
use League\Common_Mark\Node\Block\Abstract_Block;
use League\Common_Mark\Parser\Block\Abstract_Block_Continue_Parser;
use League\Common_Mark\Parser\Block\Block_Continue;
use League\Common_Mark\Parser\Block\Block_Continue_Parser_Interface;
use League\Common_Mark\Parser\Cursor;
final class List_Block_Parser extends Abstract_Block_Continue_Parser
{
    /** @psalm-readonly */
    private List_Block $block;
    public function __construct(List_Data $list_data)
    {
        $this->block = new List_Block($list_data);
    }
    public function get_block(): List_Block
    {
        return $this->block;
    }
    public function is_container(): bool
    {
        return true;
    }
    public function can_contain(Abstract_Block $child_block): bool
    {
        return $child_block instanceof List_Item;
    }
    public function try_continue(Cursor $cursor, Block_Continue_Parser_Interface $active_block_parser): \League\Common_Mark\Parser\Block\Block_Continue
    {
        // List blocks themselves don't have any markers, only list items. So try to stay in the list.
        // If there is a block start other than list item, canContain makes sure that this list is closed.
        return Block_Continue::at($cursor);
    }
    public function close_block(): void
    {
        $item = $this->block->first_child();
        while ($item instanceof Abstract_Block) {
            // check for non-final list item ending with blank line:
            if ($item->next() !== null && self::ends_with_blank_line($item)) {
                $this->block->set_tight(false);
                break;
            }
            // recurse into children of list item, to see if there are spaces between any of them
            $subitem = $item->first_child();
            while ($subitem instanceof Abstract_Block) {
                if ($subitem->next() && self::ends_with_blank_line($subitem)) {
                    $this->block->set_tight(false);
                    break 2;
                }
                $subitem = $subitem->next();
            }
            $item = $item->next();
        }
        $last_child = $this->block->last_child();
        if ($last_child instanceof Abstract_Block) {
            $this->block->set_end_line($last_child->get_end_line());
        }
    }
    private static function ends_with_blank_line(Abstract_Block $block): bool
    {
        $next = $block->next();
        return $next instanceof Abstract_Block && $block->get_end_line() !== $next->get_start_line() - 1;
    }
}