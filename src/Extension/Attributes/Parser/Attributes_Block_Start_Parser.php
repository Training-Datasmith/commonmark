<?php

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 * (c) 2015 Martin Hasoň <martin.hason@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace League\Common_Mark\Extension\Attributes\Parser;

use League\Common_Mark\Extension\Attributes\Util\Attributes_Helper;
use League\Common_Mark\Parser\Block\Block_Start;
use League\Common_Mark\Parser\Block\Block_Start_Parser_Interface;
use League\Common_Mark\Parser\Cursor;
use League\Common_Mark\Parser\Markdown_Parser_State_Interface;
final class Attributes_Block_Start_Parser implements Block_Start_Parser_Interface
{
    public function try_start(Cursor $cursor, Markdown_Parser_State_Interface $parser_state): ?Block_Start
    {
        $original_position = $cursor->get_position();
        $attributes = Attributes_Helper::parse_attributes($cursor);
        if ($attributes === [] && $original_position === $cursor->get_position()) {
            return Block_Start::none();
        }
        if ($cursor->get_next_non_space_character() !== null) {
            return Block_Start::none();
        }
        return Block_Start::of(new Attributes_Block_Continue_Parser($attributes, $parser_state->get_active_block_parser()->get_block()))->at($cursor);
    }
}