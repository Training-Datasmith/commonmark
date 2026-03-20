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
namespace League\Common_Mark\Extension\Highlight;

use League\Common_Mark\Delimiter\Delimiter_Interface;
use League\Common_Mark\Delimiter\Processor\Delimiter_Processor_Interface;
use League\Common_Mark\Node\Inline\Abstract_String_Container;
class Mark_Delimiter_Processor implements Delimiter_Processor_Interface
{
    public function get_opening_character(): string
    {
        return '=';
    }
    public function get_closing_character(): string
    {
        return '=';
    }
    public function get_min_length(): int
    {
        return 2;
    }
    public function get_delimiter_use(Delimiter_Interface $opener, Delimiter_Interface $closer): int
    {
        if ($opener->get_length() > 2 && $closer->get_length() > 2) {
            return 0;
        }
        if ($opener->get_length() !== $closer->get_length()) {
            return 0;
        }
        // $opener and $closer are the same length so we just return one of them
        return $opener->get_length();
    }
    public function process(Abstract_String_Container $opener, Abstract_String_Container $closer, int $delimiter_use): void
    {
        $mark = new Mark(\str_repeat('=', $delimiter_use));
        $next = $opener->next();
        while ($next !== null && $next !== $closer) {
            $tmp = $next->next();
            $mark->append_child($next);
            $next = $tmp;
        }
        $opener->insert_after($mark);
    }
    public function get_cache_key(Delimiter_Interface $closer): string
    {
        return '=' . $closer->get_length();
    }
}