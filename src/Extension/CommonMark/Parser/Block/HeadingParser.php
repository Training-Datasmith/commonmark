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

use League\Common_Mark\Extension\Common_Mark\Node\Block\Heading;
use League\Common_Mark\Parser\Block\Abstract_Block_Continue_Parser;
use League\Common_Mark\Parser\Block\Block_Continue;
use League\Common_Mark\Parser\Block\Block_Continue_Parser_Interface;
use League\Common_Mark\Parser\Block\Block_Continue_Parser_With_Inlines_Interface;
use League\Common_Mark\Parser\Cursor;
use League\Common_Mark\Parser\Inline_Parser_Engine_Interface;
final class Heading_Parser extends Abstract_Block_Continue_Parser implements Block_Continue_Parser_With_Inlines_Interface
{
    /** @psalm-readonly */
    private Heading $block;
    private string $content;
    public function __construct(int $level, string $content)
    {
        $this->block = new Heading($level);
        $this->content = $content;
    }
    public function get_block(): Heading
    {
        return $this->block;
    }
    public function try_continue(Cursor $cursor, Block_Continue_Parser_Interface $active_block_parser): ?Block_Continue
    {
        return Block_Continue::none();
    }
    public function parse_inlines(Inline_Parser_Engine_Interface $inline_parser): void
    {
        $inline_parser->parse($this->content, $this->block);
    }
}