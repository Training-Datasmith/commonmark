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
final class Delimiter implements Delimiter_Interface
{
    /** @psalm-readonly */
    private string $char;
    /** @psalm-readonly-allow-private-mutation */
    private int $length;
    /** @psalm-readonly */
    private int $original_length;
    /** @psalm-readonly */
    private Abstract_String_Container $inline_node;
    /** @psalm-readonly-allow-private-mutation */
    private ?Delimiter_Interface $previous = null;
    /** @psalm-readonly-allow-private-mutation */
    private ?Delimiter_Interface $next = null;
    /** @psalm-readonly */
    private bool $can_open;
    /** @psalm-readonly */
    private bool $can_close;
    /** @psalm-readonly-allow-private-mutation */
    private bool $active;
    /** @psalm-readonly */
    private ?int $index = null;
    public function __construct(string $char, int $num_delims, Abstract_String_Container $node, bool $can_open, bool $can_close, ?int $index = null)
    {
        $this->char = $char;
        $this->length = $num_delims;
        $this->original_length = $num_delims;
        $this->inline_node = $node;
        $this->can_open = $can_open;
        $this->can_close = $can_close;
        $this->active = true;
        $this->index = $index;
    }
    public function can_close(): bool
    {
        return $this->can_close;
    }
    public function can_open(): bool
    {
        return $this->can_open;
    }
    public function is_active(): bool
    {
        return $this->active;
    }
    public function set_active(bool $active): void
    {
        $this->active = $active;
    }
    public function get_char(): string
    {
        return $this->char;
    }
    public function get_index(): ?int
    {
        return $this->index;
    }
    public function get_next(): ?Delimiter_Interface
    {
        return $this->next;
    }
    public function set_next(?Delimiter_Interface $next): void
    {
        $this->next = $next;
    }
    public function get_length(): int
    {
        return $this->length;
    }
    public function set_length(int $length): void
    {
        $this->length = $length;
    }
    public function get_original_length(): int
    {
        return $this->original_length;
    }
    public function get_inline_node(): Abstract_String_Container
    {
        return $this->inline_node;
    }
    public function get_previous(): ?Delimiter_Interface
    {
        return $this->previous;
    }
    public function set_previous(?Delimiter_Interface $previous): void
    {
        $this->previous = $previous;
    }
}