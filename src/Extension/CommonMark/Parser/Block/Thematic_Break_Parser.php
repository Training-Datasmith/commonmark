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

use League\Common_Mark\Extension\Common_Mark\Node\Block\Thematic_Break;
use League\Common_Mark\Parser\Block\Abstract_Block_Continue_Parser;
use League\Common_Mark\Parser\Block\Block_Continue;
use League\Common_Mark\Parser\Block\Block_Continue_Parser_Interface;
use League\Common_Mark\Parser\Cursor;
final class Thematic_Break_Parser extends Abstract_Block_Continue_Parser
{
    /** @psalm-readonly */
    private Thematic_Break $block;
    public function __construct()
    {
        $this->block = new Thematic_Break();
    }
    public function get_block(): Thematic_Break
    {
        return $this->block;
    }
    public function try_continue(Cursor $cursor, Block_Continue_Parser_Interface $active_block_parser): ?Block_Continue
    {
        // a horizontal rule can never container > 1 line, so fail to match
        return Block_Continue::none();
    }
}