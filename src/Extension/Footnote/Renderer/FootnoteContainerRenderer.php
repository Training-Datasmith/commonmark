<?php

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 * (c) Rezo Zero / Ambroise Maupate
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace League\Common_Mark\Extension\Footnote\Renderer;

use League\Common_Mark\Extension\Footnote\Node\Footnote_Container;
use League\Common_Mark\Node\Node;
use League\Common_Mark\Renderer\Child_Node_Renderer_Interface;
use League\Common_Mark\Renderer\Node_Renderer_Interface;
use League\Common_Mark\Util\Html_Element;
use League\Common_Mark\Xml\Xml_Node_Renderer_Interface;
use League\Config\Configuration_Aware_Interface;
use League\Config\Configuration_Interface;
final class Footnote_Container_Renderer implements Node_Renderer_Interface, Xml_Node_Renderer_Interface, Configuration_Aware_Interface
{
    private Configuration_Interface $config;
    /**
     * @param FootnoteContainer $node
     *
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, Child_Node_Renderer_Interface $child_renderer): \Stringable
    {
        Footnote_Container::assert_instance_of($node);
        $attrs = $node->data->get_data('attributes');
        $attrs->append('class', $this->config->get('footnote/container_class'));
        $attrs->set('role', 'doc-endnotes');
        $contents = new Html_Element('ol', [], $child_renderer->render_nodes($node->children()));
        if ($this->config->get('footnote/container_add_hr')) {
            $contents = [new Html_Element('hr', [], null, true), $contents];
        }
        return new Html_Element('div', $attrs->export(), $contents);
    }
    public function set_configuration(Configuration_Interface $configuration): void
    {
        $this->config = $configuration;
    }
    public function get_xml_tag_name(Node $node): string
    {
        return 'footnote_container';
    }
    /**
     * @return array<string, scalar>
     */
    public function get_xml_attributes(Node $node): array
    {
        return [];
    }
}