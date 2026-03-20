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
namespace League\Common_Mark\Extension\Embed;

use League\Common_Mark\Node\Block\Abstract_Block;
final class Embed extends Abstract_Block
{
    private string $url;
    private ?string $embed_code;
    public function __construct(string $url, ?string $embed_code = null)
    {
        parent::__construct();
        $this->url = $url;
        $this->embed_code = $embed_code;
    }
    public function get_url(): string
    {
        return $this->url;
    }
    public function set_url(string $url): void
    {
        $this->url = $url;
    }
    public function get_embed_code(): ?string
    {
        return $this->embed_code;
    }
    public function set_embed_code(?string $embed_code): void
    {
        $this->embed_code = $embed_code;
    }
}