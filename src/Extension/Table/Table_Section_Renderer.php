<?php

declare (strict_types=1);
/*
 * This is part of the league/commonmark package.
 *
 * (c) Martin Hasoň <martin.hason@gmail.com>
 * (c) Webuni s.r.o. <info@webuni.cz>
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace League\Common_Mark\Extension\Table;

use League\Common_Mark\Node\Node;
use League\Common_Mark\Renderer\Child_Node_Renderer_Interface;
use League\Common_Mark\Renderer\Node_Renderer_Interface;
use League\Common_Mark\Util\Html_Element;
use League\Common_Mark\Xml\Xml_Node_Renderer_Interface;
final class Table_Section_Renderer implements Node_Renderer_Interface, Xml_Node_Renderer_Interface
{
    /**
     * @param TableSection $node
     *
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, Child_Node_Renderer_Interface $child_renderer)
    {
        Table_Section::assert_instance_of($node);
        if (!$node->has_children()) {
            return '';
        }
        $attrs = $node->data->get('attributes');
        $separator = $child_renderer->get_inner_separator();
        $tag = $node->get_type() === Table_Section::TYPE_HEAD ? 'thead' : 'tbody';
        return new Html_Element($tag, $attrs, $separator . $child_renderer->render_nodes($node->children()) . $separator);
    }
    public function get_xml_tag_name(Node $node): string
    {
        return 'table_section';
    }
    /**
     * @param TableSection $node
     *
     * @return array<string, scalar>
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function get_xml_attributes(Node $node): array
    {
        Table_Section::assert_instance_of($node);
        return ['type' => $node->get_type()];
    }
}