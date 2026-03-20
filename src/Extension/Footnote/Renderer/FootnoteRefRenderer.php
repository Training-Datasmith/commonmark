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

use League\Common_Mark\Extension\Footnote\Node\Footnote_Ref;
use League\Common_Mark\Node\Node;
use League\Common_Mark\Renderer\Child_Node_Renderer_Interface;
use League\Common_Mark\Renderer\Node_Renderer_Interface;
use League\Common_Mark\Util\Html_Element;
use League\Common_Mark\Xml\Xml_Node_Renderer_Interface;
use League\Config\Configuration_Aware_Interface;
use League\Config\Configuration_Interface;
final class Footnote_Ref_Renderer implements Node_Renderer_Interface, Xml_Node_Renderer_Interface, Configuration_Aware_Interface
{
    private Configuration_Interface $config;
    /**
     * @param FootnoteRef $node
     *
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, Child_Node_Renderer_Interface $child_renderer): \Stringable
    {
        Footnote_Ref::assert_instance_of($node);
        $attrs = $node->data->get_data('attributes');
        $attrs->append('class', $this->config->get('footnote/ref_class'));
        $attrs->set('href', \mb_strtolower($node->get_reference()->get_destination(), 'UTF-8'));
        $attrs->set('role', 'doc-noteref');
        $id_prefix = $this->config->get('footnote/ref_id_prefix');
        return new Html_Element('sup', ['id' => $id_prefix . \mb_strtolower($node->get_reference()->get_label(), 'UTF-8')], new Html_Element('a', $attrs->export(), $node->get_reference()->get_title()), true);
    }
    public function set_configuration(Configuration_Interface $configuration): void
    {
        $this->config = $configuration;
    }
    public function get_xml_tag_name(Node $node): string
    {
        return 'footnote_ref';
    }
    /**
     * @param FootnoteRef $node
     *
     * @return array<string, scalar>
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function get_xml_attributes(Node $node): array
    {
        Footnote_Ref::assert_instance_of($node);
        return ['reference' => $node->get_reference()->get_label()];
    }
}