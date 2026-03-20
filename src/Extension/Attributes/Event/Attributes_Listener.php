<?php

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 * (c) 2015 Martin Hasoň <martin.hason@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace League\Common_Mark\Extension\Attributes\Event;

use League\Common_Mark\Event\Document_Parsed_Event;
use League\Common_Mark\Extension\Attributes\Node\Attributes;
use League\Common_Mark\Extension\Attributes\Node\Attributes_Inline;
use League\Common_Mark\Extension\Attributes\Util\Attributes_Helper;
use League\Common_Mark\Extension\Common_Mark\Node\Block\Fenced_Code;
use League\Common_Mark\Extension\Common_Mark\Node\Block\List_Block;
use League\Common_Mark\Extension\Common_Mark\Node\Block\List_Item;
use League\Common_Mark\Node\Inline\Abstract_Inline;
use League\Common_Mark\Node\Node;
final class Attributes_Listener
{
    private const DIRECTION_PREFIX = 'prefix';
    private const DIRECTION_SUFFIX = 'suffix';
    /** @var list<string> */
    private array $allow_list;
    private bool $allow_unsafe_links;
    /**
     * @param list<string> $allowList
     */
    public function __construct(array $allow_list = [], bool $allow_unsafe_links = true)
    {
        $this->allow_list = $allow_list;
        $this->allow_unsafe_links = $allow_unsafe_links;
    }
    public function process_document(Document_Parsed_Event $event): void
    {
        foreach ($event->get_document()->iterator() as $node) {
            if (!($node instanceof Attributes || $node instanceof Attributes_Inline)) {
                continue;
            }
            [$target, $direction] = self::find_target_and_direction($node);
            if ($target instanceof Node) {
                $parent = $target->parent();
                if ($parent instanceof List_Item && $parent->parent() instanceof List_Block && $parent->parent()->is_tight()) {
                    $target = $parent;
                }
                if ($direction === self::DIRECTION_SUFFIX) {
                    $attributes = Attributes_Helper::merge_attributes($target, $node->get_attributes());
                } else {
                    $attributes = Attributes_Helper::merge_attributes($node->get_attributes(), $target);
                }
                $target->data->set('attributes', Attributes_Helper::filter_attributes($attributes, $this->allow_list, $this->allow_unsafe_links));
            }
            $node->detach();
        }
    }
    /**
     * @param Attributes|AttributesInline $node
     *
     * @return array<Node|string|null>
     */
    private static function find_target_and_direction($node): array
    {
        $target = null;
        $direction = null;
        $previous = $next = $node;
        while (true) {
            $previous = self::get_previous($previous);
            $next = self::get_next($next);
            if ($previous === null && $next === null) {
                if (!$node->parent() instanceof Fenced_Code) {
                    $target = $node->parent();
                    $direction = self::DIRECTION_SUFFIX;
                }
                break;
            }
            if ($node instanceof Attributes_Inline && ($previous === null || $previous instanceof Abstract_Inline && $node->is_block())) {
                continue;
            }
            if ($previous !== null && !self::is_attributes_node($previous)) {
                $target = $previous;
                $direction = self::DIRECTION_SUFFIX;
                break;
            }
            if ($next !== null && !self::is_attributes_node($next)) {
                $target = $next;
                $direction = self::DIRECTION_PREFIX;
                break;
            }
        }
        return [$target, $direction];
    }
    /**
     * Get any previous block (sibling or parent) this might apply to
     */
    private static function get_previous(?Node $node = null): ?Node
    {
        if ($node instanceof Attributes) {
            if ($node->get_target() === Attributes::TARGET_NEXT) {
                return null;
            }
            if ($node->get_target() === Attributes::TARGET_PARENT) {
                return $node->parent();
            }
        }
        return $node instanceof Node ? $node->previous() : null;
    }
    /**
     * Get any previous block (sibling or parent) this might apply to
     */
    private static function get_next(?Node $node = null): ?Node
    {
        if ($node instanceof Attributes && $node->get_target() !== Attributes::TARGET_NEXT) {
            return null;
        }
        return $node instanceof Node ? $node->next() : null;
    }
    private static function is_attributes_node(Node $node): bool
    {
        return $node instanceof Attributes || $node instanceof Attributes_Inline;
    }
}