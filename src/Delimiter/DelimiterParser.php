<?php

declare (strict_types=1);
namespace League\Common_Mark\Delimiter;

use League\Common_Mark\Delimiter\Processor\Delimiter_Processor_Collection;
use League\Common_Mark\Delimiter\Processor\Delimiter_Processor_Interface;
use League\Common_Mark\Node\Inline\Text;
use League\Common_Mark\Parser\Inline\Inline_Parser_Interface;
use League\Common_Mark\Parser\Inline\Inline_Parser_Match;
use League\Common_Mark\Parser\Inline_Parser_Context;
use League\Common_Mark\Util\Regex_Helper;
/**
 * Delimiter parsing is implemented as an Inline Parser with the lowest-possible priority
 *
 * @internal
 */
final class Delimiter_Parser implements Inline_Parser_Interface
{
    private Delimiter_Processor_Collection $collection;
    public function __construct(Delimiter_Processor_Collection $collection)
    {
        $this->collection = $collection;
    }
    public function get_match_definition(): Inline_Parser_Match
    {
        return Inline_Parser_Match::one_of(...$this->collection->get_delimiter_characters());
    }
    public function parse(Inline_Parser_Context $inline_context): bool
    {
        $character = $inline_context->get_full_match();
        $num_delims = 0;
        $cursor = $inline_context->get_cursor();
        $processor = $this->collection->get_delimiter_processor($character);
        \assert($processor !== null);
        // Delimiter processor should never be null here
        $char_before = $cursor->peek(-1);
        if ($char_before === null) {
            $char_before = "\n";
        }
        while ($cursor->peek($num_delims) === $character) {
            ++$num_delims;
        }
        if ($num_delims < $processor->get_min_length()) {
            return false;
        }
        $cursor->advance_by($num_delims);
        $char_after = $cursor->get_current_character();
        if ($char_after === null) {
            $char_after = "\n";
        }
        [$can_open, $can_close] = self::determine_can_open_or_close($char_before, $char_after, $character, $processor);
        if (!($can_open || $can_close)) {
            $inline_context->get_container()->append_child(new Text(\str_repeat($character, $num_delims)));
            return true;
        }
        $node = new Text(\str_repeat($character, $num_delims), ['delim' => true]);
        $inline_context->get_container()->append_child($node);
        // Add entry to stack to this opener
        $delimiter = new Delimiter($character, $num_delims, $node, $can_open, $can_close, $inline_context->get_cursor()->get_position());
        $inline_context->get_delimiter_stack()->push($delimiter);
        return true;
    }
    /**
     * @return bool[]
     */
    private static function determine_can_open_or_close(string $char_before, string $char_after, string $character, Delimiter_Processor_Interface $delimiter_processor): array
    {
        $after_is_whitespace = \preg_match(Regex_Helper::REGEX_UNICODE_WHITESPACE_CHAR, $char_after);
        $after_is_punctuation = \preg_match(Regex_Helper::REGEX_PUNCTUATION, $char_after);
        $before_is_whitespace = \preg_match(Regex_Helper::REGEX_UNICODE_WHITESPACE_CHAR, $char_before);
        $before_is_punctuation = \preg_match(Regex_Helper::REGEX_PUNCTUATION, $char_before);
        $left_flanking = !$after_is_whitespace && (!$after_is_punctuation || $before_is_whitespace || $before_is_punctuation);
        $right_flanking = !$before_is_whitespace && (!$before_is_punctuation || $after_is_whitespace || $after_is_punctuation);
        if ($character === '_') {
            $can_open = $left_flanking && (!$right_flanking || $before_is_punctuation);
            $can_close = $right_flanking && (!$left_flanking || $after_is_punctuation);
        } else {
            $can_open = $left_flanking && $character === $delimiter_processor->get_opening_character();
            $can_close = $right_flanking && $character === $delimiter_processor->get_closing_character();
        }
        return [$can_open, $can_close];
    }
}