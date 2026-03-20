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
class List_Item extends Abstract_Block
{
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
}