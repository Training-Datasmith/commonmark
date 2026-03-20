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
namespace League\Common_Mark\Extension\Embed;

use League\Common_Mark\Node\Block\Abstract_Block;
use League\Common_Mark\Parser\Block\Block_Continue;
use League\Common_Mark\Parser\Block\Block_Continue_Parser_Interface;
use League\Common_Mark\Parser\Cursor;
class Embed_Parser implements Block_Continue_Parser_Interface
{
    private Embed $embed;
    public function __construct(string $url)
    {
        $this->embed = new Embed($url);
    }
    public function get_block(): Abstract_Block
    {
        return $this->embed;
    }
    public function is_container(): bool
    {
        return false;
    }
    public function can_have_lazy_continuation_lines(): bool
    {
        return false;
    }
    public function can_contain(Abstract_Block $child_block): bool
    {
        return false;
    }
    public function try_continue(Cursor $cursor, Block_Continue_Parser_Interface $active_block_parser): ?Block_Continue
    {
        return Block_Continue::none();
    }
    public function add_line(string $line): void
    {
    }
    public function close_block(): void
    {
    }
}