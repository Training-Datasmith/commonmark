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

use League\Common_Mark\Extension\Common_Mark\Node\Block\Fenced_Code;
use League\Common_Mark\Node\Node;
use League\Common_Mark\Renderer\Child_Node_Renderer_Interface;
use League\Common_Mark\Renderer\Node_Renderer_Interface;
use League\Common_Mark\Util\Html_Element;
use League\Common_Mark\Util\Xml;
use League\Common_Mark\Xml\Xml_Node_Renderer_Interface;
final class Fenced_Code_Renderer implements Node_Renderer_Interface, Xml_Node_Renderer_Interface
{
    /**
     * @param FencedCode $node
     *
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, Child_Node_Renderer_Interface $child_renderer): \Stringable
    {
        Fenced_Code::assert_instance_of($node);
        $attrs = $node->data->get_data('attributes');
        $info_words = $node->get_info_words();
        if (\count($info_words) !== 0 && $info_words[0] !== '') {
            $class = $info_words[0];
            if (!\str_starts_with($class, 'language-')) {
                $class = 'language-' . $class;
            }
            $attrs->append('class', $class);
        }
        return new Html_Element('pre', [], new Html_Element('code', $attrs->export(), Xml::escape($node->get_literal())));
    }
    public function get_xml_tag_name(Node $node): string
    {
        return 'code_block';
    }
    /**
     * @param FencedCode $node
     *
     * @return array<string, scalar>
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function get_xml_attributes(Node $node): array
    {
        Fenced_Code::assert_instance_of($node);
        if (($info = $node->get_info()) === null || $info === '') {
            return [];
        }
        return ['info' => $info];
    }
}