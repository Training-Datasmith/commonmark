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
final class Relative_Normalizer_Strategy implements Normalizer_Strategy_Interface
{
    /** @psalm-readonly */
    private Table_Of_Contents $toc;
    /**
     * @var array<int, ListItem>
     *
     * @psalm-readonly-allow-private-mutation
     */
    private array $list_item_stack = [];
    public function __construct(Table_Of_Contents $toc)
    {
        $this->toc = $toc;
    }
    public function add_item(int $level, List_Item $list_item_to_add): void
    {
        $previous_level = \array_key_last($this->list_item_stack);
        // Pop the stack if we're too deep
        while ($previous_level !== null && $level < $previous_level) {
            \array_pop($this->list_item_stack);
            $previous_level = \array_key_last($this->list_item_stack);
        }
        $last_list_item = \end($this->list_item_stack);
        // Need to go one level deeper? Add that level
        if ($last_list_item !== false && $level > $previous_level) {
            $target_list_block = new List_Block($last_list_item->get_list_data());
            $target_list_block->set_start_line($list_item_to_add->get_start_line());
            $target_list_block->set_end_line($list_item_to_add->get_end_line());
            $last_list_item->append_child($target_list_block);
            // Otherwise we're at the right level
            // If there's no stack we're adding this item directly to the TOC element
        } elseif ($last_list_item === false) {
            $target_list_block = $this->toc;
            // Otherwise add it to the last list item
        } else {
            $target_list_block = $last_list_item->parent();
        }
        $target_list_block->append_child($list_item_to_add);
        $this->list_item_stack[$level] = $list_item_to_add;
    }
}