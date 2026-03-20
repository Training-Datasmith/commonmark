<?php

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace League\Common_Mark\Extension\Description_List\Parser;

use League\Common_Mark\Extension\Description_List\Node\Description;
use League\Common_Mark\Extension\Description_List\Node\Description_List;
use League\Common_Mark\Extension\Description_List\Node\Description_Term;
use League\Common_Mark\Node\Block\Abstract_Block;
use League\Common_Mark\Parser\Block\Abstract_Block_Continue_Parser;
use League\Common_Mark\Parser\Block\Block_Continue;
use League\Common_Mark\Parser\Block\Block_Continue_Parser_Interface;
use League\Common_Mark\Parser\Cursor;
final class Description_List_Continue_Parser extends Abstract_Block_Continue_Parser
{
    private Description_List $block;
    public function __construct()
    {
        $this->block = new Description_List();
    }
    public function get_block(): Description_List
    {
        return $this->block;
    }
    public function try_continue(Cursor $cursor, Block_Continue_Parser_Interface $active_block_parser): \League\Common_Mark\Parser\Block\Block_Continue
    {
        return Block_Continue::at($cursor);
    }
    public function is_container(): bool
    {
        return true;
    }
    public function can_contain(Abstract_Block $child_block): bool
    {
        return $child_block instanceof Description_Term || $child_block instanceof Description;
    }
}