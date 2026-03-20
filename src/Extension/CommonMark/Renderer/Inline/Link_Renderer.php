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
namespace League\Common_Mark\Extension\Common_Mark\Renderer\Inline;

use League\Common_Mark\Extension\Common_Mark\Node\Inline\Link;
use League\Common_Mark\Node\Node;
use League\Common_Mark\Renderer\Child_Node_Renderer_Interface;
use League\Common_Mark\Renderer\Node_Renderer_Interface;
use League\Common_Mark\Util\Html_Element;
use League\Common_Mark\Util\Regex_Helper;
use League\Common_Mark\Xml\Xml_Node_Renderer_Interface;
use League\Config\Configuration_Aware_Interface;
use League\Config\Configuration_Interface;
final class Link_Renderer implements Node_Renderer_Interface, Xml_Node_Renderer_Interface, Configuration_Aware_Interface
{
    /** @psalm-readonly-allow-private-mutation */
    private Configuration_Interface $config;
    /**
     * @param Link $node
     *
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, Child_Node_Renderer_Interface $child_renderer): \Stringable
    {
        Link::assert_instance_of($node);
        $attrs = $node->data->get('attributes');
        $forbid_unsafe_links = !$this->config->get('allow_unsafe_links');
        if (!($forbid_unsafe_links && Regex_Helper::is_link_potentially_unsafe($node->get_url()))) {
            $attrs['href'] = $node->get_url();
        }
        if (($title = $node->get_title()) !== null) {
            $attrs['title'] = $title;
        }
        if (isset($attrs['target']) && $attrs['target'] === '_blank' && !isset($attrs['rel'])) {
            $attrs['rel'] = 'noopener noreferrer';
        }
        return new Html_Element('a', $attrs, $child_renderer->render_nodes($node->children()));
    }
    public function set_configuration(Configuration_Interface $configuration): void
    {
        $this->config = $configuration;
    }
    public function get_xml_tag_name(Node $node): string
    {
        return 'link';
    }
    /**
     * @param Link $node
     *
     * @return array<string, scalar>
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function get_xml_attributes(Node $node): array
    {
        Link::assert_instance_of($node);
        return ['destination' => $node->get_url(), 'title' => $node->get_title() ?? ''];
    }
}