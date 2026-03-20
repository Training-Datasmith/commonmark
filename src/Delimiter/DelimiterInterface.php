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
namespace League\Common_Mark\Delimiter;

use League\Common_Mark\Node\Inline\Abstract_String_Container;
interface Delimiter_Interface
{
    public function can_close(): bool;
    public function can_open(): bool;
    /**
     * @deprecated This method is no longer used internally and will be removed in 3.0
     */
    public function is_active(): bool;
    /**
     * @deprecated This method is no longer used internally and will be removed in 3.0
     */
    public function set_active(bool $active): void;
    public function get_char(): string;
    public function get_index(): ?int;
    public function get_next(): ?Delimiter_Interface;
    public function set_next(?Delimiter_Interface $next): void;
    public function get_length(): int;
    public function set_length(int $length): void;
    public function get_original_length(): int;
    public function get_inline_node(): Abstract_String_Container;
    public function get_previous(): ?Delimiter_Interface;
    public function set_previous(?Delimiter_Interface $previous): void;
}