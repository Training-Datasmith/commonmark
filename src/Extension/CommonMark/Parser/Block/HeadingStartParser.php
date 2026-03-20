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
class Heading_Start_Parser implements Block_Start_Parser_Interface
{
    public function try_start(Cursor $cursor, Markdown_Parser_State_Interface $parser_state): ?Block_Start
    {
        if ($cursor->is_indented() || !\in_array($cursor->get_next_non_space_character(), ['#', '-', '='], true)) {
            return Block_Start::none();
        }
        $cursor->advance_to_next_non_space_or_tab();
        if ($atx_heading = self::get_atx_header($cursor)) {
            return Block_Start::of($atx_heading)->at($cursor);
        }
        $setext_heading_level = self::get_setext_heading_level($cursor);
        if ($setext_heading_level > 0) {
            $content = $parser_state->get_paragraph_content();
            if ($content !== null) {
                $cursor->advance_to_end();
                return Block_Start::of(new Heading_Parser($setext_heading_level, $content))->at($cursor)->replace_active_block_parser();
            }
        }
        return Block_Start::none();
    }
    private static function get_atx_header(Cursor $cursor): ?Heading_Parser
    {
        $match = Regex_Helper::match_first('/^#{1,6}(?:[ \t]+|$)/', $cursor->get_remainder());
        if (!$match) {
            return null;
        }
        $cursor->advance_to_next_non_space_or_tab();
        $cursor->advance_by(\strlen($match[0]));
        $level = \strlen(\trim($match[0]));
        $str = $cursor->get_remainder();
        $str = \preg_replace('/^[ \t]*#+[ \t]*$/', '', $str);
        \assert(\is_string($str));
        $str = \preg_replace('/[ \t]+#+[ \t]*$/', '', $str);
        \assert(\is_string($str));
        return new Heading_Parser($level, $str);
    }
    private static function get_setext_heading_level(Cursor $cursor): int
    {
        $match = Regex_Helper::match_first('/^(?:=+|-+)[ \t]*$/', $cursor->get_remainder());
        if ($match === null) {
            return 0;
        }
        return $match[0][0] === '=' ? 1 : 2;
    }
}