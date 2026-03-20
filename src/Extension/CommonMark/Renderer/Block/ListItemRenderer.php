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

use League\Common_Mark\Extension\Common_Mark\Node\Block\List_Item;
use League\Common_Mark\Node\Block\Abstract_Block;
use League\Common_Mark\Node\Block\Paragraph;
use League\Common_Mark\Node\Block\Tight_Block_Interface;
use League\Common_Mark\Node\Node;
use League\Common_Mark\Renderer\Child_Node_Renderer_Interface;
use League\Common_Mark\Renderer\Node_Renderer_Interface;
use League\Common_Mark\Util\Html_Element;
use League\Common_Mark\Xml\Xml_Node_Renderer_Interface;
final class List_Item_Renderer implements Node_Renderer_Interface, Xml_Node_Renderer_Interface
{
    /**
     * @param ListItem $node
     *
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, Child_Node_Renderer_Interface $child_renderer): \Stringable
    {
        List_Item::assert_instance_of($node);
        $contents = $child_renderer->render_nodes($node->children());
        $in_tight_list = ($parent = $node->parent()) && $parent instanceof Tight_Block_Interface && $parent->is_tight();
        if ($this->needs_block_separator($node->first_child(), $in_tight_list)) {
            $contents = "\n" . $contents;
        }
        if ($this->needs_block_separator($node->last_child(), $in_tight_list)) {
            $contents .= "\n";
        }
        $attrs = $node->data->get('attributes');
        return new Html_Element('li', $attrs, $contents);
    }
    public function get_xml_tag_name(Node $node): string
    {
        return 'item';
    }
    /**
     * {@inheritDoc}
     */
    public function get_xml_attributes(Node $node): array
    {
        return [];
    }
    private function needs_block_separator(?Node $child, bool $in_tight_list): bool
    {
        if ($child instanceof Paragraph && $in_tight_list) {
            return false;
        }
        return $child instanceof Abstract_Block;
    }
}