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
namespace League\Common_Mark\Extension\Common_Mark\Renderer\Block;

use League\Common_Mark\Extension\Common_Mark\Node\Block\List_Block;
use League\Common_Mark\Node\Node;
use League\Common_Mark\Renderer\Child_Node_Renderer_Interface;
use League\Common_Mark\Renderer\Node_Renderer_Interface;
use League\Common_Mark\Util\Html_Element;
use League\Common_Mark\Xml\Xml_Node_Renderer_Interface;
final class List_Block_Renderer implements Node_Renderer_Interface, Xml_Node_Renderer_Interface
{
    /**
     * @param ListBlock $node
     *
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, Child_Node_Renderer_Interface $child_renderer): \Stringable
    {
        List_Block::assert_instance_of($node);
        $list_data = $node->get_list_data();
        $tag = $list_data->type === List_Block::TYPE_BULLET ? 'ul' : 'ol';
        $attrs = $node->data->get('attributes');
        if ($list_data->start !== null && $list_data->start !== 1) {
            $attrs['start'] = (string) $list_data->start;
        }
        $inner_separator = $child_renderer->get_inner_separator();
        return new Html_Element($tag, $attrs, $inner_separator . $child_renderer->render_nodes($node->children()) . $inner_separator);
    }
    public function get_xml_tag_name(Node $node): string
    {
        return 'list';
    }
    /**
     * @param ListBlock $node
     *
     * @return array<string, scalar>
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function get_xml_attributes(Node $node): array
    {
        List_Block::assert_instance_of($node);
        $data = $node->get_list_data();
        if ($data->type === List_Block::TYPE_BULLET) {
            return ['type' => $data->type, 'tight' => $node->is_tight() ? 'true' : 'false'];
        }
        return ['type' => $data->type, 'start' => $data->start ?? 1, 'tight' => $node->is_tight(), 'delimiter' => $data->delimiter ?? List_Block::DELIM_PERIOD];
    }
}