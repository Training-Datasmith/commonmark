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

use League\Common_Mark\Extension\Common_Mark\Node\Block\Html_Block;
use League\Common_Mark\Parser\Block\Abstract_Block_Continue_Parser;
use League\Common_Mark\Parser\Block\Block_Continue;
use League\Common_Mark\Parser\Block\Block_Continue_Parser_Interface;
use League\Common_Mark\Parser\Cursor;
use League\Common_Mark\Util\Regex_Helper;
final class Html_Block_Parser extends Abstract_Block_Continue_Parser
{
    /** @psalm-readonly */
    private Html_Block $block;
    private string $content = '';
    private bool $finished = false;
    /**
     * @psalm-param HtmlBlock::TYPE_* $blockType
     *
     * @phpstan-param HtmlBlock::TYPE_* $blockType
     */
    public function __construct(int $block_type)
    {
        $this->block = new Html_Block($block_type);
    }
    public function get_block(): Html_Block
    {
        return $this->block;
    }
    public function try_continue(Cursor $cursor, Block_Continue_Parser_Interface $active_block_parser): ?Block_Continue
    {
        if ($this->finished) {
            return Block_Continue::none();
        }
        if ($cursor->is_blank() && \in_array($this->block->get_type(), [Html_Block::TYPE_6_BLOCK_ELEMENT, Html_Block::TYPE_7_MISC_ELEMENT], true)) {
            return Block_Continue::none();
        }
        return Block_Continue::at($cursor);
    }
    public function add_line(string $line): void
    {
        if ($this->content !== '') {
            $this->content .= "\n";
        }
        $this->content .= $line;
        // Check for end condition
        // phpcs:disable SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed
        if ($this->block->get_type() <= Html_Block::TYPE_5_CDATA) {
            if (\preg_match(Regex_Helper::get_html_block_close_regex($this->block->get_type()), $line) === 1) {
                $this->finished = true;
            }
        }
    }
    public function close_block(): void
    {
        $this->block->set_literal($this->content);
        $this->content = '';
    }
}