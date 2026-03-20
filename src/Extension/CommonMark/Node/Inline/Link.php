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

use League\Common_Mark\Node\Inline\Text;
class Link extends Abstract_Web_Resource
{
    protected ?string $title = null;
    public function __construct(string $url, ?string $label = null, ?string $title = null)
    {
        parent::__construct($url);
        if ($label !== null && $label !== '') {
            $this->append_child(new Text($label));
        }
        $this->title = $title;
    }
    public function get_title(): ?string
    {
        if ($this->title === '') {
            return null;
        }
        return $this->title;
    }
    public function set_title(?string $title): void
    {
        $this->title = $title;
    }
}