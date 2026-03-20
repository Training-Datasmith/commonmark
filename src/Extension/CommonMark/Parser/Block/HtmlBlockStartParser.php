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
use League\Common_Mark\Node\Block\Paragraph;
use League\Common_Mark\Parser\Block\Block_Start;
use League\Common_Mark\Parser\Block\Block_Start_Parser_Interface;
use League\Common_Mark\Parser\Cursor;
use League\Common_Mark\Parser\Markdown_Parser_State_Interface;
use League\Common_Mark\Util\Regex_Helper;
final class Html_Block_Start_Parser implements Block_Start_Parser_Interface
{
    public function try_start(Cursor $cursor, Markdown_Parser_State_Interface $parser_state): ?Block_Start
    {
        if ($cursor->is_indented() || $cursor->get_next_non_space_character() !== '<') {
            return Block_Start::none();
        }
        $tmp_cursor = clone $cursor;
        $tmp_cursor->advance_to_next_non_space_or_tab();
        $line = $tmp_cursor->get_remainder();
        for ($block_type = 1; $block_type <= 7; $block_type++) {
            /** @psalm-var HtmlBlock::TYPE_* $blockType */
            /** @phpstan-var HtmlBlock::TYPE_* $blockType */
            $match = Regex_Helper::match_at(Regex_Helper::get_html_block_open_regex($block_type), $line);
            if ($match !== null && ($block_type < 7 || $this->is_type7block_allowed($parser_state))) {
                return Block_Start::of(new Html_Block_Parser($block_type))->at($cursor);
            }
        }
        return Block_Start::none();
    }
    private function is_type7block_allowed(Markdown_Parser_State_Interface $parser_state): bool
    {
        // Type 7 blocks can't interrupt paragraphs
        if ($parser_state->get_last_matched_block_parser()->get_block() instanceof Paragraph) {
            return false;
        }
        // Even lazy ones
        return !$parser_state->get_active_block_parser()->can_have_lazy_continuation_lines();
    }
}