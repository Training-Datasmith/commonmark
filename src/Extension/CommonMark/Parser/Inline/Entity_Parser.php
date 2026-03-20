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
use League\Common_Mark\Util\Html5entity_Decoder;
use League\Common_Mark\Util\Regex_Helper;
final class Entity_Parser implements Inline_Parser_Interface
{
    public function get_match_definition(): Inline_Parser_Match
    {
        return Inline_Parser_Match::regex(Regex_Helper::PARTIAL_ENTITY);
    }
    public function parse(Inline_Parser_Context $inline_context): bool
    {
        $entity = $inline_context->get_full_match();
        $inline_context->get_cursor()->advance_by($inline_context->get_full_match_length());
        $inline_context->get_container()->append_child(new Text(Html5entity_Decoder::decode($entity)));
        return true;
    }
}