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
namespace League\Common_Mark\Event;

/**
 * @internal
 *
 * @psalm-immutable
 */
final class Listener_Data
{
    /** @var class-string */
    private string $event;
    /** @var callable */
    private $listener;
    /**
     * @param class-string $event
     */
    public function __construct(string $event, callable $listener)
    {
        $this->event = $event;
        $this->listener = $listener;
    }
    /**
     * @return class-string
     */
    public function get_event(): string
    {
        return $this->event;
    }
    public function get_listener(): callable
    {
        return $this->listener;
    }
}