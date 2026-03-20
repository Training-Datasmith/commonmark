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

use League\Common_Mark\Delimiter\Bracket;
use League\Common_Mark\Environment\Environment_Aware_Interface;
use League\Common_Mark\Environment\Environment_Interface;
use League\Common_Mark\Extension\Common_Mark\Node\Inline\Abstract_Web_Resource;
use League\Common_Mark\Extension\Common_Mark\Node\Inline\Image;
use League\Common_Mark\Extension\Common_Mark\Node\Inline\Link;
use League\Common_Mark\Extension\Mention\Mention;
use League\Common_Mark\Node\Inline\Adjacent_Text_Merger;
use League\Common_Mark\Node\Inline\Text;
use League\Common_Mark\Parser\Cursor;
use League\Common_Mark\Parser\Inline\Inline_Parser_Interface;
use League\Common_Mark\Parser\Inline\Inline_Parser_Match;
use League\Common_Mark\Parser\Inline_Parser_Context;
use League\Common_Mark\Reference\Reference_Interface;
use League\Common_Mark\Reference\Reference_Map_Interface;
use League\Common_Mark\Util\Link_Parser_Helper;
use League\Common_Mark\Util\Regex_Helper;
final class Close_Bracket_Parser implements Inline_Parser_Interface, Environment_Aware_Interface
{
    /** @psalm-readonly-allow-private-mutation */
    private Environment_Interface $environment;
    public function get_match_definition(): Inline_Parser_Match
    {
        return Inline_Parser_Match::string(']');
    }
    public function parse(Inline_Parser_Context $inline_context): bool
    {
        // Look through stack of delimiters for a [ or !
        $opener = $inline_context->get_delimiter_stack()->get_last_bracket();
        if ($opener === null) {
            return false;
        }
        if (!$opener->is_image() && !$opener->is_active()) {
            // no matched opener; remove from stack
            $inline_context->get_delimiter_stack()->remove_bracket();
            return false;
        }
        $cursor = $inline_context->get_cursor();
        $start_pos = $cursor->get_position();
        $previous_state = $cursor->save_state();
        $cursor->advance_by(1);
        // Check to see if we have a link/image
        // Inline link?
        if ($result = $this->try_parse_inline_link_and_title($cursor)) {
            $link = $result;
        } elseif ($link = $this->try_parse_reference($cursor, $inline_context->get_reference_map(), $opener, $start_pos)) {
            $reference = $link;
            $link = ['url' => $link->get_destination(), 'title' => $link->get_title()];
        } else {
            // No match; remove this opener from stack
            $inline_context->get_delimiter_stack()->remove_bracket();
            $cursor->restore_state($previous_state);
            return false;
        }
        $inline = $this->create_inline($link['url'], $link['title'], $opener->is_image(), $reference ?? null);
        $opener->get_node()->replace_with($inline);
        while (($label = $inline->next()) !== null) {
            // Is there a Mention or Link contained within this link?
            // CommonMark does not allow nested links, so we'll restore the original text.
            if ($label instanceof Mention) {
                $label->replace_with($replacement = new Text($label->get_prefix() . $label->get_identifier()));
                $inline->append_child($replacement);
            } elseif ($label instanceof Link) {
                foreach ($label->children() as $child) {
                    $label->insert_before($child);
                }
                $label->detach();
            } else {
                $inline->append_child($label);
            }
        }
        // Process delimiters such as emphasis inside link/image
        $delimiter_stack = $inline_context->get_delimiter_stack();
        $stack_bottom = $opener->get_position();
        $delimiter_stack->process_delimiters($stack_bottom, $this->environment->get_delimiter_processors());
        $delimiter_stack->remove_bracket();
        $delimiter_stack->remove_all($stack_bottom);
        // Merge any adjacent Text nodes together
        Adjacent_Text_Merger::merge_child_nodes($inline);
        // processEmphasis will remove this and later delimiters.
        // Now, for a link, we also remove earlier link openers (no links in links)
        if (!$opener->is_image()) {
            $inline_context->get_delimiter_stack()->deactivate_link_openers();
        }
        return true;
    }
    public function set_environment(Environment_Interface $environment): void
    {
        $this->environment = $environment;
    }
    /**
     * @return array<string, string>|null
     */
    private function try_parse_inline_link_and_title(Cursor $cursor): ?array
    {
        if ($cursor->get_current_character() !== '(') {
            return null;
        }
        $previous_state = $cursor->save_state();
        $cursor->advance_by(1);
        $cursor->advance_to_next_non_space_or_newline();
        if (($dest = Link_Parser_Helper::parse_link_destination($cursor)) === null) {
            $cursor->restore_state($previous_state);
            return null;
        }
        $cursor->advance_to_next_non_space_or_newline();
        $previous_character = $cursor->peek(-1);
        // We know from previous lines that we've advanced at least one space so far, so this next call should never be null
        \assert(\is_string($previous_character));
        $title = '';
        // make sure there's a space before the title:
        if (\preg_match(Regex_Helper::REGEX_WHITESPACE_CHAR, $previous_character)) {
            $title = Link_Parser_Helper::parse_link_title($cursor) ?? '';
        }
        $cursor->advance_to_next_non_space_or_newline();
        if ($cursor->get_current_character() !== ')') {
            $cursor->restore_state($previous_state);
            return null;
        }
        $cursor->advance_by(1);
        return ['url' => $dest, 'title' => $title];
    }
    private function try_parse_reference(Cursor $cursor, Reference_Map_Interface $reference_map, Bracket $opener, int $start_pos): ?Reference_Interface
    {
        $save_pos = $cursor->save_state();
        $before_label = $cursor->get_position();
        $n = Link_Parser_Helper::parse_link_label($cursor);
        if ($n > 2) {
            $start = $before_label + 1;
            $length = $n - 2;
        } elseif (!$opener->has_next()) {
            // Empty or missing second label means to use the first label as the reference.
            // The reference must not contain a bracket. If we know there's a bracket, we don't even bother checking it.
            $start = $opener->get_position();
            $length = $start_pos - $start;
        } else {
            $cursor->restore_state($save_pos);
            return null;
        }
        $reference_label = $cursor->get_substring($start, $length);
        if ($n === 0) {
            // If shortcut reference link, rewind before spaces we skipped
            $cursor->restore_state($save_pos);
        }
        return $reference_map->get($reference_label);
    }
    private function create_inline(string $url, string $title, bool $is_image, ?Reference_Interface $reference = null): Abstract_Web_Resource
    {
        if ($is_image) {
            $inline = new Image($url, null, $title);
        } else {
            $inline = new Link($url, null, $title);
        }
        if ($reference) {
            $inline->data->set('reference', $reference);
        }
        return $inline;
    }
}