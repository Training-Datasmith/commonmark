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
namespace League\Common_Mark\Extension\Inlines_Only;

use League\Common_Mark\Node\Block\Document;
use League\Common_Mark\Node\Node;
use League\Common_Mark\Renderer\Child_Node_Renderer_Interface;
use League\Common_Mark\Renderer\Node_Renderer_Interface;
/**
 * Simply renders child elements as-is, adding newlines as needed.
 */
final class Child_Renderer implements Node_Renderer_Interface
{
    public function render(Node $node, Child_Node_Renderer_Interface $child_renderer): string
    {
        $out = $child_renderer->render_nodes($node->children());
        if (!$node instanceof Document) {
            $out .= $child_renderer->get_block_separator();
        }
        return $out;
    }
}