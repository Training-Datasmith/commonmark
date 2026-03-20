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

use League\Common_Mark\Parser\Block\Block_Start;
use League\Common_Mark\Parser\Block\Block_Start_Parser_Interface;
use League\Common_Mark\Parser\Cursor;
use League\Common_Mark\Parser\Markdown_Parser_State_Interface;
use League\Common_Mark\Util\Regex_Helper;
final class Thematic_Break_Start_Parser implements Block_Start_Parser_Interface
{
    public function try_start(Cursor $cursor, Markdown_Parser_State_Interface $parser_state): ?Block_Start
    {
        if ($cursor->is_indented()) {
            return Block_Start::none();
        }
        $match = Regex_Helper::match_at(Regex_Helper::REGEX_THEMATIC_BREAK, $cursor->get_line(), $cursor->get_next_non_space_position());
        if ($match === null) {
            return Block_Start::none();
        }
        // Advance to the end of the string, consuming the entire line (of the thematic break)
        $cursor->advance_to_end();
        return Block_Start::of(new Thematic_Break_Parser())->at($cursor);
    }
}