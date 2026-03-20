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
namespace League\Common_Mark\Extension\Front_Matter;

use League\Common_Mark\Extension\Front_Matter\Input\Markdown_Input_With_Front_Matter;
interface Front_Matter_Parser_Interface
{
    public function parse(string $markdown_content): Markdown_Input_With_Front_Matter;
}