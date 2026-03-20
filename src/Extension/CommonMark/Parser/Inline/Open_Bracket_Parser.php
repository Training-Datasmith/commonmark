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

use League\Common_Mark\Node\Inline\Text;
use League\Common_Mark\Parser\Inline\Inline_Parser_Interface;
use League\Common_Mark\Parser\Inline\Inline_Parser_Match;
use League\Common_Mark\Parser\Inline_Parser_Context;
final class Open_Bracket_Parser implements Inline_Parser_Interface
{
    public function get_match_definition(): Inline_Parser_Match
    {
        return Inline_Parser_Match::string('[');
    }
    public function parse(Inline_Parser_Context $inline_context): bool
    {
        $inline_context->get_cursor()->advance_by(1);
        $node = new Text('[', ['delim' => true]);
        $inline_context->get_container()->append_child($node);
        // Add entry to stack for this opener
        $inline_context->get_delimiter_stack()->add_bracket($node, $inline_context->get_cursor()->get_position(), false);
        return true;
    }
}