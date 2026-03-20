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
namespace League\Common_Mark\Extension\Highlight;

use League\Common_Mark\Node\Node;
use League\Common_Mark\Renderer\Child_Node_Renderer_Interface;
use League\Common_Mark\Renderer\Node_Renderer_Interface;
use League\Common_Mark\Util\Html_Element;
use League\Common_Mark\Xml\Xml_Node_Renderer_Interface;
final class Mark_Renderer implements Node_Renderer_Interface, Xml_Node_Renderer_Interface
{
    /**
     * @param Mark $node
     *
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, Child_Node_Renderer_Interface $child_renderer): \Stringable
    {
        Mark::assert_instance_of($node);
        return new Html_Element('mark', $node->data->get('attributes'), $child_renderer->render_nodes($node->children()));
    }
    public function get_xml_tag_name(Node $node): string
    {
        return 'mark';
    }
    /**
     * {@inheritDoc}
     */
    public function get_xml_attributes(Node $node): array
    {
        return [];
    }
}