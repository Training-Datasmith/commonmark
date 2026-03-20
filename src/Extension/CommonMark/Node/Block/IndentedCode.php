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
namespace League\Common_Mark\Extension\Common_Mark\Node\Block;

use League\Common_Mark\Node\Block\Abstract_Block;
use League\Common_Mark\Node\String_Container_Interface;
final class Indented_Code extends Abstract_Block implements String_Container_Interface
{
    private string $literal = '';
    public function get_literal(): string
    {
        return $this->literal;
    }
    public function set_literal(string $literal): void
    {
        $this->literal = $literal;
    }
}