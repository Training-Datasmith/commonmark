<?php

declare (strict_types=1);
/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * Original code based on the CommonMark JS reference parser (https://bitly.com/commonmark-js)
 *  - (c) John MacFarlane
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace League\Common_Mark\Extension\Common_Mark\Node\Block;

use League\Common_Mark\Node\Block\Abstract_Block;
final class Heading extends Abstract_Block
{
    private int $level;
    public function __construct(int $level)
    {
        parent::__construct();
        $this->level = $level;
    }
    public function get_level(): int
    {
        return $this->level;
    }
    public function set_level(int $level): void
    {
        $this->level = $level;
    }
}