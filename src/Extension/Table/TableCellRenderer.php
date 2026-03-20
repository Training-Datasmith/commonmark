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

use League\Common_Mark\Extension\Attributes\Util\Attributes_Helper;
use League\Common_Mark\Node\Node;
use League\Common_Mark\Renderer\Child_Node_Renderer_Interface;
use League\Common_Mark\Renderer\Node_Renderer_Interface;
use League\Common_Mark\Util\Html_Element;
use League\Common_Mark\Xml\Xml_Node_Renderer_Interface;
final class Table_Cell_Renderer implements Node_Renderer_Interface, Xml_Node_Renderer_Interface
{
    private const DEFAULT_ATTRIBUTES = [Table_Cell::ALIGN_LEFT => ['align' => 'left'], Table_Cell::ALIGN_CENTER => ['align' => 'center'], Table_Cell::ALIGN_RIGHT => ['align' => 'right']];
    /** @var array<TableCell::ALIGN_*, array<string, string|string[]|bool>> */
    private array $alignment_attributes;
    /**
     * @param array<TableCell::ALIGN_*, array<string, string|string[]|bool>> $alignmentAttributes
     */
    public function __construct(array $alignment_attributes = self::DEFAULT_ATTRIBUTES)
    {
        $this->alignment_attributes = $alignment_attributes;
    }
    /**
     * @param TableCell $node
     *
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, Child_Node_Renderer_Interface $child_renderer): \Stringable
    {
        Table_Cell::assert_instance_of($node);
        $attrs = $node->data->get('attributes');
        if (($alignment = $node->get_align()) !== null) {
            $attrs = Attributes_Helper::merge_attributes($attrs, $this->alignment_attributes[$alignment]);
        }
        $tag = $node->get_type() === Table_Cell::TYPE_HEADER ? 'th' : 'td';
        return new Html_Element($tag, $attrs, $child_renderer->render_nodes($node->children()));
    }
    public function get_xml_tag_name(Node $node): string
    {
        return 'table_cell';
    }
    /**
     * @param TableCell $node
     *
     * @return array<string, scalar>
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function get_xml_attributes(Node $node): array
    {
        Table_Cell::assert_instance_of($node);
        $ret = ['type' => $node->get_type()];
        if (($align = $node->get_align()) !== null) {
            $ret['align'] = $align;
        }
        return $ret;
    }
}