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
namespace League\Common_Mark\Extension\Embed;

use League\Common_Mark\Parser\Block\Block_Start;
use League\Common_Mark\Parser\Block\Block_Start_Parser_Interface;
use League\Common_Mark\Parser\Cursor;
use League\Common_Mark\Parser\Markdown_Parser_State_Interface;
use League\Common_Mark\Util\Link_Parser_Helper;
class Embed_Start_Parser implements Block_Start_Parser_Interface
{
    public function try_start(Cursor $cursor, Markdown_Parser_State_Interface $parser_state): ?Block_Start
    {
        if ($cursor->is_indented() || $parser_state->get_paragraph_content() !== null || !$parser_state->get_active_block_parser()->is_container()) {
            return Block_Start::none();
        }
        // 0-3 leading spaces are okay
        $cursor->advance_to_next_non_space_or_tab();
        // The line must begin with "https://"
        if (!str_starts_with($cursor->get_remainder(), 'https://')) {
            return Block_Start::none();
        }
        // A valid link must be found next
        if (($dest = Link_Parser_Helper::parse_link_destination($cursor)) === null) {
            return Block_Start::none();
        }
        // Skip any trailing whitespace
        $cursor->advance_to_next_non_space_or_tab();
        // We must be at the end of the line; otherwise, this link was not by itself
        if (!$cursor->is_at_end()) {
            return Block_Start::none();
        }
        return Block_Start::of(new Embed_Parser($dest))->at($cursor);
    }
}