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
final class Fenced_Code_Start_Parser implements Block_Start_Parser_Interface
{
    public function try_start(Cursor $cursor, Markdown_Parser_State_Interface $parser_state): ?Block_Start
    {
        if ($cursor->is_indented() || !\in_array($cursor->get_next_non_space_character(), ['`', '~'], true)) {
            return Block_Start::none();
        }
        $indent = $cursor->get_indent();
        $fence = $cursor->match('/^[ \t]*(?:`{3,}(?!.*`)|~{3,})/');
        if ($fence === null) {
            return Block_Start::none();
        }
        // fenced code block
        $fence = \ltrim($fence, " \t");
        return Block_Start::of(new Fenced_Code_Parser(\strlen($fence), $fence[0], $indent))->at($cursor);
    }
}