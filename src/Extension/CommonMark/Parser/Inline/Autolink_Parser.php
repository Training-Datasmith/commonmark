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

use League\Common_Mark\Extension\Common_Mark\Node\Inline\Link;
use League\Common_Mark\Parser\Inline\Inline_Parser_Interface;
use League\Common_Mark\Parser\Inline\Inline_Parser_Match;
use League\Common_Mark\Parser\Inline_Parser_Context;
use League\Common_Mark\Util\Url_Encoder;
final class Autolink_Parser implements Inline_Parser_Interface
{
    private const EMAIL_REGEX = '<([a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*)>';
    private const OTHER_LINK_REGEX = '<([A-Za-z][A-Za-z0-9.+-]{1,31}:[^<>\x00-\x20]*)>';
    public function get_match_definition(): Inline_Parser_Match
    {
        return Inline_Parser_Match::regex(self::EMAIL_REGEX . '|' . self::OTHER_LINK_REGEX);
    }
    public function parse(Inline_Parser_Context $inline_context): bool
    {
        $inline_context->get_cursor()->advance_by($inline_context->get_full_match_length());
        $matches = $inline_context->get_matches();
        if ($matches[1] !== '') {
            $inline_context->get_container()->append_child(new Link('mailto:' . Url_Encoder::unescape_and_encode($matches[1]), $matches[1]));
            return true;
        }
        if ($matches[2] !== '') {
            $inline_context->get_container()->append_child(new Link(Url_Encoder::unescape_and_encode($matches[2]), $matches[2]));
            return true;
        }
        return false;
        // This should never happen
    }
}