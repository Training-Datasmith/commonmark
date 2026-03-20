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
namespace League\Common_Mark\Extension\Table_Of_Contents\Normalizer;

use League\Common_Mark\Extension\Common_Mark\Node\Block\List_Block;
use League\Common_Mark\Extension\Common_Mark\Node\Block\List_Item;
use League\Common_Mark\Extension\Table_Of_Contents\Node\Table_Of_Contents;
final class As_Is_Normalizer_Strategy implements Normalizer_Strategy_Interface
{
    /** @psalm-readonly-allow-private-mutation */
    private List_Block $parent_list_block;
    /** @psalm-readonly-allow-private-mutation */
    private int $parent_level = 1;
    /** @psalm-readonly-allow-private-mutation */
    private ?List_Item $last_list_item = null;
    public function __construct(Table_Of_Contents $toc)
    {
        $this->parent_list_block = $toc;
    }
    public function add_item(int $level, List_Item $list_item_to_add): void
    {
        while ($level > $this->parent_level) {
            // Descend downwards, creating new ListBlocks if needed, until we reach the correct depth
            if ($this->last_list_item === null) {
                $this->last_list_item = new List_Item($this->parent_list_block->get_list_data());
                $this->parent_list_block->append_child($this->last_list_item);
            }
            $new_list_block = new List_Block($this->parent_list_block->get_list_data());
            $new_list_block->set_start_line($list_item_to_add->get_start_line());
            $new_list_block->set_end_line($list_item_to_add->get_end_line());
            $this->last_list_item->append_child($new_list_block);
            $this->parent_list_block = $new_list_block;
            $this->last_list_item = null;
            $this->parent_level++;
        }
        while ($level < $this->parent_level) {
            // Search upwards for the previous parent list block
            $search = $this->parent_list_block;
            while ($search = $search->parent()) {
                if ($search instanceof List_Block) {
                    $this->parent_list_block = $search;
                    break;
                }
            }
            $this->parent_level--;
        }
        $this->parent_list_block->append_child($list_item_to_add);
        $this->last_list_item = $list_item_to_add;
    }
}