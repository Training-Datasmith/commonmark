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
namespace League\Common_Mark\Extension\Default_Attributes;

use League\Common_Mark\Event\Document_Parsed_Event;
use League\Common_Mark\Extension\Attributes\Util\Attributes_Helper;
use League\Config\Configuration_Aware_Interface;
use League\Config\Configuration_Interface;
final class Apply_Default_Attributes_Processor implements Configuration_Aware_Interface
{
    private Configuration_Interface $config;
    public function on_document_parsed(Document_Parsed_Event $event): void
    {
        /** @var array<string, array<string, mixed>> $map */
        $map = $this->config->get('default_attributes');
        // Don't bother iterating if no default attributes are configured
        if (!$map) {
            return;
        }
        foreach ($event->get_document()->iterator() as $node) {
            // Check to see if any default attributes were defined
            if (($attributes_to_apply = $map[\get_class($node)] ?? []) === []) {
                continue;
            }
            $new_attributes = [];
            foreach ($attributes_to_apply as $name => $value) {
                if (\is_callable($value)) {
                    $value = $value($node);
                    // Callables are allowed to return `null` indicating that no changes should be made
                    if ($value !== null) {
                        $new_attributes[$name] = $value;
                    }
                } else {
                    $new_attributes[$name] = $value;
                }
            }
            // Merge these attributes into the node
            if (\count($new_attributes) > 0) {
                $node->data->set('attributes', Attributes_Helper::merge_attributes($node, $new_attributes));
            }
        }
    }
    public function set_configuration(Configuration_Interface $configuration): void
    {
        $this->config = $configuration;
    }
}