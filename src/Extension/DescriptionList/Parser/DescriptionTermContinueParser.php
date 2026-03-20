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
namespace League\Common_Mark\Extension\Description_List\Parser;

use League\Common_Mark\Extension\Description_List\Node\Description_Term;
use League\Common_Mark\Parser\Block\Abstract_Block_Continue_Parser;
use League\Common_Mark\Parser\Block\Block_Continue;
use League\Common_Mark\Parser\Block\Block_Continue_Parser_Interface;
use League\Common_Mark\Parser\Block\Block_Continue_Parser_With_Inlines_Interface;
use League\Common_Mark\Parser\Cursor;
use League\Common_Mark\Parser\Inline_Parser_Engine_Interface;
final class Description_Term_Continue_Parser extends Abstract_Block_Continue_Parser implements Block_Continue_Parser_With_Inlines_Interface
{
    private Description_Term $block;
    private string $term;
    public function __construct(string $term)
    {
        $this->block = new Description_Term();
        $this->term = $term;
    }
    public function get_block(): Description_Term
    {
        return $this->block;
    }
    public function try_continue(Cursor $cursor, Block_Continue_Parser_Interface $active_block_parser): \League\Common_Mark\Parser\Block\Block_Continue
    {
        return Block_Continue::finished();
    }
    public function parse_inlines(Inline_Parser_Engine_Interface $inline_parser): void
    {
        if ($this->term !== '') {
            $inline_parser->parse($this->term, $this->block);
        }
    }
}