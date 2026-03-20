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
namespace League\Common_Mark\Delimiter;

use League\Common_Mark\Node\Node;
final class Bracket
{
    private Node $node;
    private ?Bracket $previous;
    private bool $has_next = false;
    private int $position;
    private bool $image;
    private bool $active = true;
    public function __construct(Node $node, ?Bracket $previous, int $position, bool $image)
    {
        $this->node = $node;
        $this->previous = $previous;
        $this->position = $position;
        $this->image = $image;
    }
    public function get_node(): Node
    {
        return $this->node;
    }
    public function get_previous(): ?Bracket
    {
        return $this->previous;
    }
    public function has_next(): bool
    {
        return $this->has_next;
    }
    public function get_position(): int
    {
        return $this->position;
    }
    public function is_image(): bool
    {
        return $this->image;
    }
    /**
     * Only valid in the context of non-images (links)
     */
    public function is_active(): bool
    {
        return $this->active;
    }
    /**
     * @internal
     */
    public function set_has_next(bool $has_next): void
    {
        $this->has_next = $has_next;
    }
    /**
     * @internal
     */
    public function set_active(bool $active): void
    {
        $this->active = $active;
    }
}