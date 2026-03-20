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

use League\Common_Mark\Delimiter\Delimiter;
use League\Common_Mark\Parser\Inline\Inline_Parser_Interface;
use League\Common_Mark\Parser\Inline\Inline_Parser_Match;
use League\Common_Mark\Parser\Inline_Parser_Context;
use League\Common_Mark\Util\Regex_Helper;
final class Quote_Parser implements Inline_Parser_Interface
{
    /**
     * @deprecated This constant is no longer used and will be removed in a future major release
     */
    public const DOUBLE_QUOTES = [Quote::DOUBLE_QUOTE, Quote::DOUBLE_QUOTE_OPENER, Quote::DOUBLE_QUOTE_CLOSER];
    /**
     * @deprecated This constant is no longer used and will be removed in a future major release
     */
    public const SINGLE_QUOTES = [Quote::SINGLE_QUOTE, Quote::SINGLE_QUOTE_OPENER, Quote::SINGLE_QUOTE_CLOSER];
    public function get_match_definition(): Inline_Parser_Match
    {
        return Inline_Parser_Match::one_of(Quote::SINGLE_QUOTE, Quote::DOUBLE_QUOTE);
    }
    /**
     * Normalizes any quote characters found and manually adds them to the delimiter stack
     */
    public function parse(Inline_Parser_Context $inline_context): bool
    {
        $char = $inline_context->get_full_match();
        $cursor = $inline_context->get_cursor();
        $index = $cursor->get_position();
        $char_before = $cursor->peek(-1);
        if ($char_before === null) {
            $char_before = "\n";
        }
        $cursor->advance();
        $char_after = $cursor->get_current_character();
        if ($char_after === null) {
            $char_after = "\n";
        }
        [$left_flanking, $right_flanking] = $this->determine_flanking($char_before, $char_after);
        $can_open = $left_flanking && !$right_flanking;
        $can_close = $right_flanking;
        $node = new Quote($char, ['delim' => true]);
        $inline_context->get_container()->append_child($node);
        // Add entry to stack to this opener
        $inline_context->get_delimiter_stack()->push(new Delimiter($char, 1, $node, $can_open, $can_close, $index));
        return true;
    }
    /**
     * @return bool[]
     */
    private function determine_flanking(string $char_before, string $char_after): array
    {
        $after_is_whitespace = \preg_match('/\pZ|\s/u', $char_after);
        $after_is_punctuation = \preg_match(Regex_Helper::REGEX_PUNCTUATION, $char_after);
        $before_is_whitespace = \preg_match('/\pZ|\s/u', $char_before);
        $before_is_punctuation = \preg_match(Regex_Helper::REGEX_PUNCTUATION, $char_before);
        $left_flanking = !$after_is_whitespace && !($after_is_punctuation && !$before_is_whitespace && !$before_is_punctuation);
        $right_flanking = !$before_is_whitespace && !($before_is_punctuation && !$after_is_whitespace && !$after_is_punctuation);
        return [$left_flanking, $right_flanking];
    }
}