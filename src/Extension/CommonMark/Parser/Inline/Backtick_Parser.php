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

use League\Common_Mark\Extension\Common_Mark\Node\Inline\Code;
use League\Common_Mark\Node\Inline\Text;
use League\Common_Mark\Parser\Cursor;
use League\Common_Mark\Parser\Inline\Inline_Parser_Interface;
use League\Common_Mark\Parser\Inline\Inline_Parser_Match;
use League\Common_Mark\Parser\Inline_Parser_Context;
final class Backtick_Parser implements Inline_Parser_Interface
{
    /**
     * Max bound for backtick code span delimiters.
     *
     * @see https://github.com/commonmark/cmark/commit/8ed5c9d
     */
    private const MAX_BACKTICKS = 1000;
    /** @var \WeakReference<Cursor>|null */
    private ?\WeakReference $last_cursor = null;
    private bool $last_cursor_scanned = false;
    /** @var array<int, int> backtick count => position of known ender */
    private array $seen_backticks = [];
    public function get_match_definition(): Inline_Parser_Match
    {
        return Inline_Parser_Match::regex('`+');
    }
    public function parse(Inline_Parser_Context $inline_context): bool
    {
        $ticks = $inline_context->get_full_match();
        $cursor = $inline_context->get_cursor();
        $cursor->advance_by($inline_context->get_full_match_length());
        $current_position = $cursor->get_position();
        $previous_state = $cursor->save_state();
        if ($this->find_matching_ticks(\strlen($ticks), $cursor)) {
            $code = $cursor->get_substring($current_position, $cursor->get_position() - $current_position - \strlen($ticks));
            $c = \preg_replace('/\n/m', ' ', $code) ?? '';
            if ($c !== '' && $c[0] === ' ' && str_ends_with($c, ' ') && \preg_match('/[^ ]/', $c)) {
                $c = \substr($c, 1, -1);
            }
            $inline_context->get_container()->append_child(new Code($c));
            return true;
        }
        // If we got here, we didn't match a closing backtick sequence
        $cursor->restore_state($previous_state);
        $inline_context->get_container()->append_child(new Text($ticks));
        return true;
    }
    /**
     * Locates the matching closer for a backtick code span.
     *
     * Leverages some caching to avoid traversing the same cursor multiple times when
     * we've already seen all the potential backtick closers.
     *
     * @see https://github.com/commonmark/cmark/commit/8ed5c9d
     *
     * @param int    $openTickLength Number of backticks in the opening sequence
     * @param Cursor $cursor         Cursor to scan
     *
     * @return bool True if a matching closer was found, false otherwise
     */
    private function find_matching_ticks(int $open_tick_length, Cursor $cursor): bool
    {
        // Reset the seenBackticks cache if this is a new cursor
        if ($this->last_cursor === null || $this->last_cursor->get() !== $cursor) {
            $this->seen_backticks = [];
            $this->last_cursor = \WeakReference::create($cursor);
            $this->last_cursor_scanned = false;
        }
        if ($open_tick_length > self::MAX_BACKTICKS) {
            return false;
        }
        // Return if we already know there's no closer
        if ($this->last_cursor_scanned && isset($this->seen_backticks[$open_tick_length]) && $this->seen_backticks[$open_tick_length] <= $cursor->get_position()) {
            return false;
        }
        while ($ticks = $cursor->match('/`{1,' . self::MAX_BACKTICKS . '}/m')) {
            $num_ticks = \strlen($ticks);
            // Did we find the closer?
            if ($num_ticks === $open_tick_length) {
                return true;
            }
            // Store position of closer
            if ($num_ticks <= self::MAX_BACKTICKS) {
                $this->seen_backticks[$num_ticks] = $cursor->get_position() - $num_ticks;
            }
        }
        // Got through whole input without finding closer
        $this->last_cursor_scanned = true;
        return false;
    }
}