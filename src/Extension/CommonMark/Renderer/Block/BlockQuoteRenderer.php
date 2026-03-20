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

use League\Common_Mark\Extension\Common_Mark\Node\Block\Block_Quote;
use League\Common_Mark\Node\Node;
use League\Common_Mark\Renderer\Child_Node_Renderer_Interface;
use League\Common_Mark\Renderer\Node_Renderer_Interface;
use League\Common_Mark\Util\Html_Element;
use League\Common_Mark\Xml\Xml_Node_Renderer_Interface;
final class Block_Quote_Renderer implements Node_Renderer_Interface, Xml_Node_Renderer_Interface
{
    /**
     * @param BlockQuote $node
     *
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, Child_Node_Renderer_Interface $child_renderer): \Stringable
    {
        Block_Quote::assert_instance_of($node);
        $attrs = $node->data->get('attributes');
        $filling = $child_renderer->render_nodes($node->children());
        $inner_separator = $child_renderer->get_inner_separator();
        if ($filling === '') {
            return new Html_Element('blockquote', $attrs, $inner_separator);
        }
        return new Html_Element('blockquote', $attrs, $inner_separator . $filling . $inner_separator);
    }
    public function get_xml_tag_name(Node $node): string
    {
        return 'block_quote';
    }
    /**
     * @param BlockQuote $node
     *
     * @return array<string, scalar>
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function get_xml_attributes(Node $node): array
    {
        return [];
    }
}