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
namespace League\Common_Mark\Extension\Common_Mark\Node\Inline;

use League\Common_Mark\Node\Inline\Abstract_Inline;
use League\Common_Mark\Node\Inline\Delimited_Interface;
final class Strong extends Abstract_Inline implements Delimited_Interface
{
    private string $delimiter;
    public function __construct(string $delimiter = '**')
    {
        parent::__construct();
        $this->delimiter = $delimiter;
    }
    public function get_opening_delimiter(): string
    {
        return $this->delimiter;
    }
    public function get_closing_delimiter(): string
    {
        return $this->delimiter;
    }
}