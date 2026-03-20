<?php

declare (strict_types=1);
/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * Original code based on the CommonMark JS reference parser (https://bitly.com/commonmark-js)
 *  - (c) John MacFarlane
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace League\Common_Mark\Extension\Common_Mark\Parser\Inline;

use League\Common_Mark\Node\Inline\Newline;
use League\Common_Mark\Node\Inline\Text;
use League\Common_Mark\Parser\Inline\Inline_Parser_Interface;
use League\Common_Mark\Parser\Inline\Inline_Parser_Match;
use League\Common_Mark\Parser\Inline_Parser_Context;
use League\Common_Mark\Util\Regex_Helper;
final class Escapable_Parser implements Inline_Parser_Interface
{
    public function get_match_definition(): Inline_Parser_Match
    {
        return Inline_Parser_Match::string('\\');
    }
    public function parse(Inline_Parser_Context $inline_context): bool
    {
        $cursor = $inline_context->get_cursor();
        $next_char = $cursor->peek();
        if ($next_char === "\n") {
            $cursor->advance_by(2);
            $inline_context->get_container()->append_child(new Newline(Newline::HARDBREAK));
            return true;
        }
        if ($next_char !== null && Regex_Helper::is_escapable($next_char)) {
            $cursor->advance_by(2);
            $inline_context->get_container()->append_child(new Text($next_char));
            return true;
        }
        $cursor->advance_by(1);
        $inline_context->get_container()->append_child(new Text('\\'));
        return true;
    }
}