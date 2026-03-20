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

use League\Common_Mark\Extension\Common_Mark\Node\Block\List_Item;
use League\Common_Mark\Extension\Table_Of_Contents\Node\Table_Of_Contents;
final class Flat_Normalizer_Strategy implements Normalizer_Strategy_Interface
{
    /** @psalm-readonly */
    private Table_Of_Contents $toc;
    public function __construct(Table_Of_Contents $toc)
    {
        $this->toc = $toc;
    }
    public function add_item(int $level, List_Item $list_item_to_add): void
    {
        $this->toc->append_child($list_item_to_add);
    }
}