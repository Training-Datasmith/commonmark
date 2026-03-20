<?php

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 * (c) Rezo Zero / Ambroise Maupate
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace League\Common_Mark\Extension\Footnote\Parser;

use League\Common_Mark\Parser\Block\Block_Start;
use League\Common_Mark\Parser\Block\Block_Start_Parser_Interface;
use League\Common_Mark\Parser\Cursor;
use League\Common_Mark\Parser\Markdown_Parser_State_Interface;
use League\Common_Mark\Reference\Reference;
use League\Common_Mark\Util\Regex_Helper;
final class Footnote_Start_Parser implements Block_Start_Parser_Interface
{
    public function try_start(Cursor $cursor, Markdown_Parser_State_Interface $parser_state): ?Block_Start
    {
        if ($cursor->is_indented() || $parser_state->get_last_matched_block_parser()->can_have_lazy_continuation_lines()) {
            return Block_Start::none();
        }
        $match = Regex_Helper::match_first('/^\[\^([^\s^\]]+)\]\:(?:\s|$)/', $cursor->get_line(), $cursor->get_next_non_space_position());
        if (!$match) {
            return Block_Start::none();
        }
        $cursor->advance_to_next_non_space_or_tab();
        $cursor->advance_by(\strlen($match[0]));
        $str = $cursor->get_remainder();
        \preg_replace('/^\[\^([^\s^\]]+)\]\:(?:\s|$)/', '', $str);
        if (\preg_match('/^\[\^([^\s^\]]+)\]\:(?:\s|$)/', $match[0], $matches) !== 1) {
            return Block_Start::none();
        }
        $reference = new Reference($matches[1], $matches[1], $matches[1]);
        $footnote_parser = new Footnote_Parser($reference);
        return Block_Start::of($footnote_parser)->at($cursor);
    }
}