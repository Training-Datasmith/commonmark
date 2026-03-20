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
namespace League\Common_Mark\Extension\Attributes\Util;

use League\Common_Mark\Node\Node;
use League\Common_Mark\Parser\Cursor;
use League\Common_Mark\Util\Regex_Helper;
/**
 * @internal
 */
final class Attributes_Helper
{
    private const SINGLE_ATTRIBUTE = '\s*([.]-?[_a-z][^\s.}]*|[#][^\s}]+|' . Regex_Helper::PARTIAL_ATTRIBUTENAME . Regex_Helper::PARTIAL_ATTRIBUTEVALUESPEC . ')\s*';
    private const ATTRIBUTE_LIST = '/^{:?(' . self::SINGLE_ATTRIBUTE . ')+}/i';
    /**
     * @return array<string, mixed>
     */
    public static function parse_attributes(Cursor $cursor): array
    {
        $state = $cursor->save_state();
        $cursor->advance_to_next_non_space_or_newline();
        // Quick check to see if we might have attributes
        if ($cursor->get_character() !== '{') {
            $cursor->restore_state($state);
            return [];
        }
        // Attempt to match the entire attribute list expression
        // While this is less performant than checking for '{' now and '}' later, it simplifies
        // matching individual attributes since they won't need to look ahead for the closing '}'
        // while dealing with the fact that attributes can technically contain curly braces.
        // So we'll just match the start and end braces up front.
        $attribute_expression = $cursor->match(self::ATTRIBUTE_LIST);
        if ($attribute_expression === null) {
            $cursor->restore_state($state);
            return [];
        }
        // Trim the leading '{' or '{:' and the trailing '}'
        $attribute_expression = \ltrim(\substr($attribute_expression, 1, -1), ':');
        $attribute_cursor = new Cursor($attribute_expression);
        /** @var array<string, mixed> $attributes */
        $attributes = [];
        while ($attribute = \trim((string) $attribute_cursor->match('/^' . self::SINGLE_ATTRIBUTE . '/i'))) {
            if ($attribute[0] === '#') {
                $attributes['id'] = \substr($attribute, 1);
                continue;
            }
            if ($attribute[0] === '.') {
                $attributes['class'][] = \substr($attribute, 1);
                continue;
            }
            /** @psalm-suppress PossiblyUndefinedArrayOffset */
            [$name, $value] = \explode('=', $attribute, 2);
            if ($value === 'true') {
                $attributes[$name] = true;
                continue;
            }
            $first = $value[0];
            $last = \substr($value, -1);
            if ($first === '"' && $last === '"' || $first === "'" && $last === "'" && \strlen($value) > 1) {
                $value = \substr($value, 1, -1);
            }
            if (\strtolower(\trim($name)) === 'class') {
                foreach (\array_filter(\explode(' ', \trim($value))) as $class) {
                    $attributes['class'][] = $class;
                }
            } else {
                $attributes[\trim($name)] = \trim($value);
            }
        }
        if (isset($attributes['class'])) {
            $attributes['class'] = \implode(' ', (array) $attributes['class']);
        }
        return $attributes;
    }
    /**
     * @param Node|array<string, mixed> $attributes1
     * @param Node|array<string, mixed> $attributes2
     *
     * @return array<string, mixed>
     */
    public static function merge_attributes($attributes1, $attributes2): array
    {
        $attributes = [];
        foreach ([$attributes1, $attributes2] as $arg) {
            if ($arg instanceof Node) {
                $arg = $arg->data->get('attributes');
            }
            /** @var array<string, mixed> $arg */
            $arg = (array) $arg;
            if (isset($arg['class'])) {
                if (\is_string($arg['class'])) {
                    $arg['class'] = \array_filter(\explode(' ', \trim($arg['class'])));
                }
                foreach ($arg['class'] as $class) {
                    $attributes['class'][] = $class;
                }
                unset($arg['class']);
            }
            $attributes = \array_merge($attributes, $arg);
        }
        if (isset($attributes['class'])) {
            $attributes['class'] = \implode(' ', $attributes['class']);
        }
        return $attributes;
    }
    /**
     * @param array<string, mixed> $attributes
     * @param list<string>         $allowList
     *
     * @return array<string, mixed>
     */
    public static function filter_attributes(array $attributes, array $allow_list, bool $allow_unsafe_links): array
    {
        $allow_list = \array_fill_keys($allow_list, true);
        foreach ($attributes as $name => $value) {
            $attr_name_lower = \strtolower($name);
            // Remove any unsafe links
            if (!$allow_unsafe_links && ($attr_name_lower === 'href' || $attr_name_lower === 'src') && \is_string($value) && Regex_Helper::is_link_potentially_unsafe($value)) {
                unset($attributes[$name]);
                continue;
            }
            // No allowlist?
            if ($allow_list === []) {
                // Just remove JS event handlers
                if (\str_starts_with($attr_name_lower, 'on')) {
                    unset($attributes[$name]);
                }
                continue;
            }
            // Remove any attributes not in that allowlist (case-sensitive)
            if (!isset($allow_list[$name])) {
                unset($attributes[$name]);
            }
        }
        return $attributes;
    }
}