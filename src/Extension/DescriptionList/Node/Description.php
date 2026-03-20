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
namespace League\Common_Mark\Extension\Description_List\Node;

use League\Common_Mark\Node\Block\Abstract_Block;
use League\Common_Mark\Node\Block\Tight_Block_Interface;
class Description extends Abstract_Block implements Tight_Block_Interface
{
    private bool $tight;
    public function __construct(bool $tight = false)
    {
        parent::__construct();
        $this->tight = $tight;
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