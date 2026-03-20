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
namespace League\Common_Mark\Extension\Heading_Permalink;

use League\Common_Mark\Node\Inline\Abstract_Inline;
/**
 * Represents an anchor link within a heading
 */
final class Heading_Permalink extends Abstract_Inline
{
    /** @psalm-readonly */
    private string $slug;
    public function __construct(string $slug)
    {
        parent::__construct();
        $this->slug = $slug;
    }
    public function get_slug(): string
    {
        return $this->slug;
    }
}