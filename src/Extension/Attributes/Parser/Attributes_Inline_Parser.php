<?php

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 * (c) 2015 Martin Hasoň <martin.hason@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace League\Common_Mark\Extension\Attributes\Parser;

use League\Common_Mark\Extension\Attributes\Node\Attributes_Inline;
use League\Common_Mark\Extension\Attributes\Util\Attributes_Helper;
use League\Common_Mark\Node\String_Container_Interface;
use League\Common_Mark\Parser\Inline\Inline_Parser_Interface;
use League\Common_Mark\Parser\Inline\Inline_Parser_Match;
use League\Common_Mark\Parser\Inline_Parser_Context;
final class Attributes_Inline_Parser implements Inline_Parser_Interface
{
    public function get_match_definition(): Inline_Parser_Match
    {
        return Inline_Parser_Match::string('{');
    }
    public function parse(Inline_Parser_Context $inline_context): bool
    {
        $cursor = $inline_context->get_cursor();
        $char = (string) $cursor->peek(-1);
        $attributes = Attributes_Helper::parse_attributes($cursor);
        if ($attributes === []) {
            return false;
        }
        if ($char === ' ' && ($prev = $inline_context->get_container()->last_child()) instanceof String_Container_Interface) {
            $prev->set_literal(\rtrim($prev->get_literal(), ' '));
        }
        if ($char === '') {
            $cursor->advance_to_next_non_space_or_newline();
        }
        $node = new Attributes_Inline($attributes, $char === ' ' || $char === '');
        $inline_context->get_container()->append_child($node);
        return true;
    }
}