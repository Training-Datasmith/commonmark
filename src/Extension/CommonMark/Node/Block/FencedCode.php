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
use League\Common_Mark\Node\String_Container_Interface;
final class Fenced_Code extends Abstract_Block implements String_Container_Interface
{
    private ?string $info = null;
    private string $literal = '';
    private int $length;
    private string $char;
    private int $offset;
    public function __construct(int $length, string $char, int $offset)
    {
        parent::__construct();
        $this->length = $length;
        $this->char = $char;
        $this->offset = $offset;
    }
    public function get_info(): ?string
    {
        return $this->info;
    }
    /**
     * @return string[]
     */
    public function get_info_words(): array
    {
        return \preg_split('/\s+/', $this->info ?? '') ?: [];
    }
    public function set_info(string $info): void
    {
        $this->info = $info;
    }
    public function get_literal(): string
    {
        return $this->literal;
    }
    public function set_literal(string $literal): void
    {
        $this->literal = $literal;
    }
    public function get_char(): string
    {
        return $this->char;
    }
    public function set_char(string $char): void
    {
        $this->char = $char;
    }
    public function get_length(): int
    {
        return $this->length;
    }
    public function set_length(int $length): void
    {
        $this->length = $length;
    }
    public function get_offset(): int
    {
        return $this->offset;
    }
    public function set_offset(int $offset): void
    {
        $this->offset = $offset;
    }
}