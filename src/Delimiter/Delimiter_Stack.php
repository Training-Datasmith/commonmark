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
 * Additional emphasis processing code based on commonmark-java (https://github.com/atlassian/commonmark-java)
 *  - (c) Atlassian Pty Ltd
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace League\Common_Mark\Delimiter;

use League\Common_Mark\Delimiter\Processor\Cacheable_Delimiter_Processor_Interface;
use League\Common_Mark\Delimiter\Processor\Delimiter_Processor_Collection;
use League\Common_Mark\Node\Inline\Adjacent_Text_Merger;
use League\Common_Mark\Node\Node;
final class Delimiter_Stack
{
    /** @psalm-readonly-allow-private-mutation */
    private ?Delimiter_Interface $top = null;
    /** @psalm-readonly-allow-private-mutation */
    private ?Bracket $brackets = null;
    /**
     * @deprecated This property will be removed in 3.0 once all delimiters MUST have an index/position
     *
     * @var \SplObjectStorage<DelimiterInterface, int>|\WeakMap<DelimiterInterface, int>
     */
    private $missing_index_cache;
    private int $remaining_delimiters = 0;
    public function __construct(int $maximum_stack_size = PHP_INT_MAX)
    {
        $this->remaining_delimiters = $maximum_stack_size;
        if (\PHP_VERSION_ID >= 80000) {
            /** @psalm-suppress PropertyTypeCoercion */
            $this->missing_index_cache = new \WeakMap();
            // @phpstan-ignore-line
        } else {
            $this->missing_index_cache = new \Spl_Object_Storage();
            // @phpstan-ignore-line
        }
    }
    public function push(Delimiter_Interface $new_delimiter): void
    {
        if ($this->remaining_delimiters-- <= 0) {
            return;
        }
        $new_delimiter->set_previous($this->top);
        if ($this->top !== null) {
            $this->top->set_next($new_delimiter);
        }
        $this->top = $new_delimiter;
    }
    /**
     * @internal
     */
    public function add_bracket(Node $node, int $index, bool $image): void
    {
        if ($this->brackets !== null) {
            $this->brackets->set_has_next(true);
        }
        $this->brackets = new Bracket($node, $this->brackets, $index, $image);
    }
    /**
     * @psalm-immutable
     */
    public function get_last_bracket(): ?Bracket
    {
        return $this->brackets;
    }
    private function find_earliest(int $stack_bottom): ?Delimiter_Interface
    {
        // Move back to first relevant delim.
        $delimiter = $this->top;
        $last_checked = null;
        while ($delimiter !== null && self::get_index($delimiter) > $stack_bottom) {
            $last_checked = $delimiter;
            $delimiter = $delimiter->get_previous();
        }
        return $last_checked;
    }
    /**
     * @internal
     */
    public function remove_bracket(): void
    {
        if ($this->brackets === null) {
            return;
        }
        $this->brackets = $this->brackets->get_previous();
        if ($this->brackets !== null) {
            $this->brackets->set_has_next(false);
        }
    }
    public function remove_delimiter(Delimiter_Interface $delimiter): void
    {
        if ($delimiter->get_previous() !== null) {
            /** @psalm-suppress PossiblyNullReference */
            $delimiter->get_previous()->set_next($delimiter->get_next());
        }
        if ($delimiter->get_next() === null) {
            // top of stack
            $this->top = $delimiter->get_previous();
        } else {
            /** @psalm-suppress PossiblyNullReference */
            $delimiter->get_next()->set_previous($delimiter->get_previous());
        }
        // Nullify all references from the removed delimiter to other delimiters.
        // All references to this particular delimiter in the linked list should be gone,
        // but it's possible we're still hanging on to other references to things that
        // have been (or soon will be) removed, which may interfere with efficient
        // garbage collection by the PHP runtime.
        // Explicitly releasing these references should help to avoid possible
        // segfaults like in https://bugs.php.net/bug.php?id=68606.
        $delimiter->set_previous(null);
        $delimiter->set_next(null);
        // TODO: Remove the line below once PHP 7.4 support is dropped, as WeakMap won't hold onto the reference, making this unnecessary
        unset($this->missing_index_cache[$delimiter]);
    }
    private function remove_delimiter_and_node(Delimiter_Interface $delimiter): void
    {
        $delimiter->get_inline_node()->detach();
        $this->remove_delimiter($delimiter);
    }
    private function remove_delimiters_between(Delimiter_Interface $opener, Delimiter_Interface $closer): void
    {
        $delimiter = $closer->get_previous();
        $opener_position = self::get_index($opener);
        while ($delimiter !== null && self::get_index($delimiter) > $opener_position) {
            $previous = $delimiter->get_previous();
            $this->remove_delimiter($delimiter);
            $delimiter = $previous;
        }
    }
    /**
     * @param DelimiterInterface|int|null $stackBottom
     */
    public function remove_all($stack_bottom = null): void
    {
        $stack_bottom_position = \is_int($stack_bottom) ? $stack_bottom : self::get_index($stack_bottom);
        while ($this->top && $this->get_index($this->top) > $stack_bottom_position) {
            $this->remove_delimiter($this->top);
        }
    }
    /**
     * @deprecated This method is no longer used internally and will be removed in 3.0
     */
    public function remove_earlier_matches(string $character): void
    {
        $opener = $this->top;
        while ($opener !== null) {
            if ($opener->get_char() === $character) {
                $opener->set_active(false);
            }
            $opener = $opener->get_previous();
        }
    }
    /**
     * @internal
     */
    public function deactivate_link_openers(): void
    {
        $opener = $this->brackets;
        while ($opener !== null && $opener->is_active()) {
            $opener->set_active(false);
            $opener = $opener->get_previous();
        }
    }
    /**
     * @deprecated This method is no longer used internally and will be removed in 3.0
     *
     * @param string|string[] $characters
     */
    public function search_by_character($characters): ?Delimiter_Interface
    {
        if (!\is_array($characters)) {
            $characters = [$characters];
        }
        $opener = $this->top;
        while ($opener !== null) {
            if (\in_array($opener->get_char(), $characters, true)) {
                break;
            }
            $opener = $opener->get_previous();
        }
        return $opener;
    }
    /**
     * @param DelimiterInterface|int|null $stackBottom
     *
     * @todo change $stackBottom to an int in 3.0
     */
    public function process_delimiters($stack_bottom, Delimiter_Processor_Collection $processors): void
    {
        /** @var array<string, int> $openersBottom */
        $openers_bottom = [];
        $stack_bottom_position = \is_int($stack_bottom) ? $stack_bottom : self::get_index($stack_bottom);
        // Find first closer above stackBottom
        $closer = $this->find_earliest($stack_bottom_position);
        // Move forward, looking for closers, and handling each
        while ($closer !== null) {
            $closing_delimiter_char = $closer->get_char();
            $delimiter_processor = $processors->get_delimiter_processor($closing_delimiter_char);
            if (!$closer->can_close() || $delimiter_processor === null) {
                $closer = $closer->get_next();
                continue;
            }
            if ($delimiter_processor instanceof Cacheable_Delimiter_Processor_Interface) {
                $openers_bottom_cache_key = $delimiter_processor->get_cache_key($closer);
            } else {
                $openers_bottom_cache_key = $closing_delimiter_char;
            }
            $opening_delimiter_char = $delimiter_processor->get_opening_character();
            $use_delims = 0;
            $opener_found = false;
            $potential_opener_found = false;
            $opener = $closer->get_previous();
            while ($opener !== null && ($opener_position = self::get_index($opener)) > $stack_bottom_position && $opener_position >= ($openers_bottom[$openers_bottom_cache_key] ?? 0)) {
                if ($opener->can_open() && $opener->get_char() === $opening_delimiter_char) {
                    $potential_opener_found = true;
                    $use_delims = $delimiter_processor->get_delimiter_use($opener, $closer);
                    if ($use_delims > 0) {
                        $opener_found = true;
                        break;
                    }
                }
                $opener = $opener->get_previous();
            }
            if (!$opener_found) {
                // Set lower bound for future searches
                // TODO: Remove this conditional check in 3.0. It only exists to prevent behavioral BC breaks in 2.x.
                if ($potential_opener_found === false || $delimiter_processor instanceof Cacheable_Delimiter_Processor_Interface) {
                    $openers_bottom[$openers_bottom_cache_key] = self::get_index($closer);
                }
                if (!$potential_opener_found && !$closer->can_open()) {
                    // We can remove a closer that can't be an opener,
                    // once we've seen there's no matching opener.
                    $next = $closer->get_next();
                    $this->remove_delimiter($closer);
                    $closer = $next;
                } else {
                    $closer = $closer->get_next();
                }
                continue;
            }
            \assert($opener !== null);
            $opener_node = $opener->get_inline_node();
            $closer_node = $closer->get_inline_node();
            // Remove number of used delimiters from stack and inline nodes.
            $opener->set_length($opener->get_length() - $use_delims);
            $closer->set_length($closer->get_length() - $use_delims);
            $opener_node->set_literal(\substr($opener_node->get_literal(), 0, -$use_delims));
            $closer_node->set_literal(\substr($closer_node->get_literal(), 0, -$use_delims));
            $this->remove_delimiters_between($opener, $closer);
            // The delimiter processor can re-parent the nodes between opener and closer,
            // so make sure they're contiguous already. Exclusive because we want to keep opener/closer themselves.
            Adjacent_Text_Merger::merge_text_nodes_between_exclusive($opener_node, $closer_node);
            $delimiter_processor->process($opener_node, $closer_node, $use_delims);
            // No delimiter characters left to process, so we can remove delimiter and the now empty node.
            if ($opener->get_length() === 0) {
                $this->remove_delimiter_and_node($opener);
            }
            // phpcs:disable SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed
            if ($closer->get_length() === 0) {
                $next = $closer->get_next();
                $this->remove_delimiter_and_node($closer);
                $closer = $next;
            }
        }
        // Remove all delimiters
        $this->remove_all($stack_bottom_position);
    }
    /**
     * @internal
     */
    public function __destruct()
    {
        while ($this->top) {
            $this->remove_delimiter($this->top);
        }
        while ($this->brackets) {
            $this->remove_bracket();
        }
    }
    /**
     * @deprecated This method will be dropped in 3.0 once all delimiters MUST have an index/position
     */
    private function get_index(?Delimiter_Interface $delimiter): int
    {
        if ($delimiter === null) {
            return -1;
        }
        if (($index = $delimiter->get_index()) !== null) {
            return $index;
        }
        if (isset($this->missing_index_cache[$delimiter])) {
            return $this->missing_index_cache[$delimiter];
        }
        $prev = $delimiter->get_previous();
        $next = $delimiter->get_next();
        $i = 0;
        do {
            $i++;
            if ($prev === null) {
                break;
            }
            if ($prev->get_index() !== null) {
                return $this->missing_index_cache[$delimiter] = $prev->get_index() + $i;
            }
        } while ($prev = $prev->get_previous());
        $j = 0;
        do {
            $j++;
            if ($next === null) {
                break;
            }
            if ($next->get_index() !== null) {
                return $this->missing_index_cache[$delimiter] = $next->get_index() - $j;
            }
        } while ($next = $next->get_next());
        // No index was defined on this delimiter, and none could be guesstimated based on the stack.
        return $this->missing_index_cache[$delimiter] = $this->get_index($delimiter->get_previous()) + 1;
    }
}