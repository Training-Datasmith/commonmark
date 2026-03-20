<?php

declare (strict_types=1);
/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * Original code based on the CommonMark JS reference parser (http://bitly.com/commonmark-js)
 *  - (c) John MacFarlane
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace League\Common_Mark\Extension\Smart_Punct;

use League\Common_Mark\Node\Inline\Text;
use League\Common_Mark\Parser\Inline\Inline_Parser_Interface;
use League\Common_Mark\Parser\Inline\Inline_Parser_Match;
use League\Common_Mark\Parser\Inline_Parser_Context;
final class Dash_Parser implements Inline_Parser_Interface
{
    private const EN_DASH = '–';
    private const EM_DASH = '—';
    public function get_match_definition(): Inline_Parser_Match
    {
        return Inline_Parser_Match::regex('(?<!-)(-{2,})');
    }
    public function parse(Inline_Parser_Context $inline_context): bool
    {
        $count = $inline_context->get_full_match_length();
        $inline_context->get_cursor()->advance_by($count);
        $en_count = 0;
        $em_count = 0;
        if ($count % 3 === 0) {
            // If divisible by 3, use all em dashes
            $em_count = (int) ($count / 3);
        } elseif ($count % 2 === 0) {
            // If divisible by 2, use all en dashes
            $en_count = (int) ($count / 2);
        } elseif ($count % 3 === 2) {
            // If 2 extra dashes, use en dash for last 2; em dashes for rest
            $em_count = (int) (($count - 2) / 3);
            $en_count = 1;
        } else {
            // Use en dashes for last 4 hyphens; em dashes for rest
            $em_count = (int) (($count - 4) / 3);
            $en_count = 2;
        }
        $inline_context->get_container()->append_child(new Text(\str_repeat(self::EM_DASH, $em_count) . \str_repeat(self::EN_DASH, $en_count)));
        return true;
    }
}