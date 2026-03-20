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
namespace League\Common_Mark\Extension\Autolink;

use League\Common_Mark\Extension\Common_Mark\Node\Inline\Link;
use League\Common_Mark\Parser\Inline\Inline_Parser_Interface;
use League\Common_Mark\Parser\Inline\Inline_Parser_Match;
use League\Common_Mark\Parser\Inline_Parser_Context;
final class Email_Autolink_Parser implements Inline_Parser_Interface
{
    private const REGEX = '[A-Za-z0-9.\-_+]+@[A-Za-z0-9\-_]+\.[A-Za-z0-9\-_.]+';
    public function get_match_definition(): Inline_Parser_Match
    {
        return Inline_Parser_Match::regex(self::REGEX);
    }
    public function parse(Inline_Parser_Context $inline_context): bool
    {
        $email = $inline_context->get_full_match();
        // The last character cannot be - or _
        if (\in_array(\substr($email, -1), ['-', '_'], true)) {
            return false;
        }
        // Does the URL end with punctuation that should be stripped?
        if (str_ends_with($email, '.')) {
            $email = \substr($email, 0, -1);
        }
        $inline_context->get_cursor()->advance_by(\strlen($email));
        $inline_context->get_container()->append_child(new Link('mailto:' . $email, $email));
        return true;
    }
}