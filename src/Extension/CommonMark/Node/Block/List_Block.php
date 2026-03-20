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
use League\Common_Mark\Node\Block\Tight_Block_Interface;
class List_Block extends Abstract_Block implements Tight_Block_Interface
{
    public const TYPE_BULLET = 'bullet';
    public const TYPE_ORDERED = 'ordered';
    public const DELIM_PERIOD = 'period';
    public const DELIM_PAREN = 'paren';
    protected bool $tight = false;
    // TODO Make lists tight by default in v3
    /** @psalm-readonly */
    protected List_Data $list_data;
    public function __construct(List_Data $list_data)
    {
        parent::__construct();
        $this->list_data = $list_data;
    }
    public function get_list_data(): List_Data
    {
        return $this->list_data;
    }
    public function is_tight(): bool
    {
        return $this->tight;
    }
    public function set_tight(bool $tight): void
    {
        $this->tight = $tight;
    }
}