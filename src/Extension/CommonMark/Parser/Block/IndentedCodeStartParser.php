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

use League\Common_Mark\Node\Block\Paragraph;
use League\Common_Mark\Parser\Block\Block_Start;
use League\Common_Mark\Parser\Block\Block_Start_Parser_Interface;
use League\Common_Mark\Parser\Cursor;
use League\Common_Mark\Parser\Markdown_Parser_State_Interface;
final class Indented_Code_Start_Parser implements Block_Start_Parser_Interface
{
    public function try_start(Cursor $cursor, Markdown_Parser_State_Interface $parser_state): ?Block_Start
    {
        if (!$cursor->is_indented()) {
            return Block_Start::none();
        }
        if ($parser_state->get_active_block_parser()->get_block() instanceof Paragraph) {
            return Block_Start::none();
        }
        if ($cursor->is_blank()) {
            return Block_Start::none();
        }
        $cursor->advance_by(Cursor::INDENT_LEVEL, true);
        return Block_Start::of(new Indented_Code_Parser())->at($cursor);
    }
}