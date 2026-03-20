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
abstract class Abstract_Web_Resource extends Abstract_Inline
{
    protected string $url;
    public function __construct(string $url)
    {
        parent::__construct();
        $this->url = $url;
    }
    public function get_url(): string
    {
        return $this->url;
    }
    public function set_url(string $url): void
    {
        $this->url = $url;
    }
}