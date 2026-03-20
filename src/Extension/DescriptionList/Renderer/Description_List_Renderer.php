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
namespace League\Common_Mark\Extension\Description_List\Renderer;

use League\Common_Mark\Extension\Description_List\Node\Description_List;
use League\Common_Mark\Node\Node;
use League\Common_Mark\Renderer\Child_Node_Renderer_Interface;
use League\Common_Mark\Renderer\Node_Renderer_Interface;
use League\Common_Mark\Util\Html_Element;
final class Description_List_Renderer implements Node_Renderer_Interface
{
    /**
     * @param DescriptionList $node
     *
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, Child_Node_Renderer_Interface $child_renderer): Html_Element
    {
        Description_List::assert_instance_of($node);
        $separator = $child_renderer->get_block_separator();
        return new Html_Element('dl', [], $separator . $child_renderer->render_nodes($node->children()) . $separator);
    }
}