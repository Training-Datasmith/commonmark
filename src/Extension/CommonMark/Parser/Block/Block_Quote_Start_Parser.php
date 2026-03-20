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
final class Block_Quote_Start_Parser implements Block_Start_Parser_Interface
{
    public function try_start(Cursor $cursor, Markdown_Parser_State_Interface $parser_state): ?Block_Start
    {
        if ($cursor->is_indented()) {
            return Block_Start::none();
        }
        if ($cursor->get_next_non_space_character() !== '>') {
            return Block_Start::none();
        }
        $cursor->advance_to_next_non_space_or_tab();
        $cursor->advance_by(1);
        $cursor->advance_by_space_or_tab();
        return Block_Start::of(new Block_Quote_Parser())->at($cursor);
    }
}