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

use League\Common_Mark\Node\Node;
use League\Common_Mark\Renderer\Child_Node_Renderer_Interface;
use League\Common_Mark\Renderer\Node_Renderer_Interface;
use League\Common_Mark\Util\Html_Element;
use League\Common_Mark\Xml\Xml_Node_Renderer_Interface;
use League\Config\Configuration_Aware_Interface;
use League\Config\Configuration_Interface;
/**
 * Renders the HeadingPermalink elements
 */
final class Heading_Permalink_Renderer implements Node_Renderer_Interface, Xml_Node_Renderer_Interface, Configuration_Aware_Interface
{
    public const DEFAULT_SYMBOL = '¶';
    /** @psalm-readonly-allow-private-mutation */
    private Configuration_Interface $config;
    public function set_configuration(Configuration_Interface $configuration): void
    {
        $this->config = $configuration;
    }
    /**
     * @param HeadingPermalink $node
     *
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, Child_Node_Renderer_Interface $child_renderer): \Stringable
    {
        Heading_Permalink::assert_instance_of($node);
        $slug = $node->get_slug();
        $fragment_prefix = (string) $this->config->get('heading_permalink/fragment_prefix');
        if ($fragment_prefix !== '') {
            $fragment_prefix .= '-';
        }
        $attrs = $node->data->get_data('attributes');
        $append_id = !$this->config->get('heading_permalink/apply_id_to_heading');
        if ($append_id) {
            $id_prefix = (string) $this->config->get('heading_permalink/id_prefix');
            if ($id_prefix !== '') {
                $id_prefix .= '-';
            }
            $attrs->set('id', $id_prefix . $slug);
        }
        $attrs->set('href', '#' . $fragment_prefix . $slug);
        $attrs->append('class', $this->config->get('heading_permalink/html_class'));
        $hidden = $this->config->get('heading_permalink/aria_hidden');
        if ($hidden) {
            $attrs->set('aria-hidden', 'true');
        }
        $attrs->set('title', $this->config->get('heading_permalink/title'));
        $symbol = $this->config->get('heading_permalink/symbol');
        \assert(\is_string($symbol));
        return new Html_Element('a', $attrs->export(), \htmlspecialchars($symbol), false);
    }
    public function get_xml_tag_name(Node $node): string
    {
        return 'heading_permalink';
    }
    /**
     * @param HeadingPermalink $node
     *
     * @return array<string, scalar>
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function get_xml_attributes(Node $node): array
    {
        Heading_Permalink::assert_instance_of($node);
        return ['slug' => $node->get_slug()];
    }
}