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
final class Table_Renderer implements Node_Renderer_Interface, Xml_Node_Renderer_Interface
{
    /**
     * @param Table $node
     *
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, Child_Node_Renderer_Interface $child_renderer): \Stringable
    {
        Table::assert_instance_of($node);
        $attrs = $node->data->get('attributes');
        $separator = $child_renderer->get_inner_separator();
        $children = $child_renderer->render_nodes($node->children());
        return new Html_Element('table', $attrs, $separator . \trim($children) . $separator);
    }
    public function get_xml_tag_name(Node $node): string
    {
        return 'table';
    }
    /**
     * {@inheritDoc}
     */
    public function get_xml_attributes(Node $node): array
    {
        return [];
    }
}