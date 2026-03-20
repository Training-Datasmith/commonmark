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
namespace League\Common_Mark\Extension\Common_Mark\Parser\Block;

use League\Common_Mark\Extension\Common_Mark\Node\Block\List_Block;
use League\Common_Mark\Extension\Common_Mark\Node\Block\List_Data;
use League\Common_Mark\Parser\Block\Block_Start;
use League\Common_Mark\Parser\Block\Block_Start_Parser_Interface;
use League\Common_Mark\Parser\Cursor;
use League\Common_Mark\Parser\Markdown_Parser_State_Interface;
use League\Common_Mark\Util\Regex_Helper;
use League\Config\Configuration_Aware_Interface;
use League\Config\Configuration_Interface;
final class List_Block_Start_Parser implements Block_Start_Parser_Interface, Configuration_Aware_Interface
{
    /** @psalm-readonly-allow-private-mutation */
    private ?Configuration_Interface $config = null;
    /**
     * @psalm-var non-empty-string|null
     *
     * @psalm-readonly-allow-private-mutation
     */
    private ?string $list_marker_regex = null;
    public function set_configuration(Configuration_Interface $configuration): void
    {
        $this->config = $configuration;
    }
    public function try_start(Cursor $cursor, Markdown_Parser_State_Interface $parser_state): ?Block_Start
    {
        if ($cursor->is_indented()) {
            return Block_Start::none();
        }
        $list_data = $this->parse_list($cursor, $parser_state->get_paragraph_content() !== null);
        if ($list_data === null) {
            return Block_Start::none();
        }
        $list_item_parser = new List_Item_Parser($list_data);
        // prepend the list block if needed
        $matched = $parser_state->get_last_matched_block_parser();
        if (!$matched instanceof List_Block_Parser || !$list_data->equals($matched->get_block()->get_list_data())) {
            $list_block_parser = new List_Block_Parser($list_data);
            // We start out with assuming a list is tight. If we find a blank line, we set it to loose later.
            // TODO for 3.0: Just make them tight by default in the block so we can remove this call
            $list_block_parser->get_block()->set_tight(true);
            return Block_Start::of($list_block_parser, $list_item_parser)->at($cursor);
        }
        return Block_Start::of($list_item_parser)->at($cursor);
    }
    private function parse_list(Cursor $cursor, bool $in_paragraph): ?List_Data
    {
        $indent = $cursor->get_indent();
        $tmp_cursor = clone $cursor;
        $tmp_cursor->advance_to_next_non_space_or_tab();
        $rest = $tmp_cursor->get_remainder();
        if (\preg_match($this->list_marker_regex ?? $this->generate_list_marker_regex(), $rest) === 1) {
            $data = new List_Data();
            $data->marker_offset = $indent;
            $data->type = List_Block::TYPE_BULLET;
            $data->delimiter = null;
            $data->bullet_char = $rest[0];
            $marker_length = 1;
        } elseif (($matches = Regex_Helper::match_first('/^(\d{1,9})([.)])/', $rest)) && (!$in_paragraph || $matches[1] === '1')) {
            $data = new List_Data();
            $data->marker_offset = $indent;
            $data->type = List_Block::TYPE_ORDERED;
            $data->start = (int) $matches[1];
            $data->delimiter = $matches[2] === '.' ? List_Block::DELIM_PERIOD : List_Block::DELIM_PAREN;
            $data->bullet_char = null;
            $marker_length = \strlen($matches[0]);
        } else {
            return null;
        }
        // Make sure we have spaces after
        $next_char = $tmp_cursor->peek($marker_length);
        if (!($next_char === null || $next_char === "\t" || $next_char === ' ')) {
            return null;
        }
        // If it interrupts paragraph, make sure first line isn't blank
        if ($in_paragraph && !Regex_Helper::match_at(Regex_Helper::REGEX_NON_SPACE, $rest, $marker_length)) {
            return null;
        }
        $cursor->advance_to_next_non_space_or_tab();
        // to start of marker
        $cursor->advance_by($marker_length, true);
        // to end of marker
        $data->padding = self::calculate_list_marker_padding($cursor, $marker_length);
        return $data;
    }
    private static function calculate_list_marker_padding(Cursor $cursor, int $marker_length): int
    {
        $start = $cursor->save_state();
        $spaces_start_col = $cursor->get_column();
        while ($cursor->get_column() - $spaces_start_col < 5) {
            if (!$cursor->advance_by_space_or_tab()) {
                break;
            }
        }
        $blank_item = $cursor->peek() === null;
        $spaces_after_marker = $cursor->get_column() - $spaces_start_col;
        if ($spaces_after_marker >= 5 || $spaces_after_marker < 1 || $blank_item) {
            $cursor->restore_state($start);
            $cursor->advance_by_space_or_tab();
            return $marker_length + 1;
        }
        return $marker_length + $spaces_after_marker;
    }
    /**
     * @psalm-return non-empty-string
     */
    private function generate_list_marker_regex(): string
    {
        // No configuration given - use the defaults
        if ($this->config === null) {
            return $this->list_marker_regex = '/^[*+-]/';
        }
        $markers = $this->config->get('commonmark/unordered_list_markers');
        \assert(\is_array($markers));
        return $this->list_marker_regex = '/^[' . \preg_quote(\implode('', $markers), '/') . ']/';
    }
}