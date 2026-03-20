<?php

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace League\Common_Mark\Extension\Description_List\Parser;

use League\Common_Mark\Extension\Description_List\Node\Description;
use League\Common_Mark\Node\Block\Paragraph;
use League\Common_Mark\Parser\Block\Block_Start;
use League\Common_Mark\Parser\Block\Block_Start_Parser_Interface;
use League\Common_Mark\Parser\Cursor;
use League\Common_Mark\Parser\Markdown_Parser_State_Interface;
final class Description_Start_Parser implements Block_Start_Parser_Interface
{
    public function try_start(Cursor $cursor, Markdown_Parser_State_Interface $parser_state): ?Block_Start
    {
        if ($cursor->is_indented()) {
            return Block_Start::none();
        }
        $cursor->advance_to_next_non_space_or_tab();
        if ($cursor->match('/^:[ \t]+/') === null) {
            return Block_Start::none();
        }
        $terms = $parser_state->get_paragraph_content();
        $active_block = $parser_state->get_active_block_parser()->get_block();
        if ($terms !== null && $terms !== '') {
            // New description; tight; term(s) sitting in pending block that we will replace
            return Block_Start::of(...[new Description_List_Continue_Parser()], ...self::split_terms($terms), ...[new Description_Continue_Parser(true, $cursor->get_position())])->at($cursor)->replace_active_block_parser();
        }
        if ($active_block instanceof Paragraph && $active_block->parent() instanceof Description) {
            // Additional description in the same list as the parent description
            return Block_Start::of(new Description_Continue_Parser(true, $cursor->get_position()))->at($cursor);
        }
        if ($active_block->last_child() instanceof Paragraph) {
            // New description; loose; term(s) sitting in previous closed paragraph block
            return Block_Start::of(new Description_Continue_Parser(false, $cursor->get_position()))->at($cursor);
        }
        // No preceding terms
        return Block_Start::none();
    }
    /**
     * @return array<int, DescriptionTermContinueParser>
     */
    private static function split_terms(string $terms): array
    {
        $ret = [];
        foreach (\explode("\n", $terms) as $term) {
            $ret[] = new Description_Term_Continue_Parser($term);
        }
        return $ret;
    }
}