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
namespace League\Common_Mark\Extension\Disallowed_Raw_Html;

use League\Common_Mark\Node\Node;
use League\Common_Mark\Renderer\Child_Node_Renderer_Interface;
use League\Common_Mark\Renderer\Node_Renderer_Interface;
use League\Config\Configuration_Aware_Interface;
use League\Config\Configuration_Interface;
final class Disallowed_Raw_Html_Renderer implements Node_Renderer_Interface, Configuration_Aware_Interface
{
    /** @psalm-readonly */
    private Node_Renderer_Interface $inner_renderer;
    /** @psalm-readonly-allow-private-mutation */
    private Configuration_Interface $config;
    public function __construct(Node_Renderer_Interface $inner_renderer)
    {
        $this->inner_renderer = $inner_renderer;
    }
    public function render(Node $node, Child_Node_Renderer_Interface $child_renderer): ?string
    {
        $rendered = (string) $this->inner_renderer->render($node, $child_renderer);
        if ($rendered === '') {
            return '';
        }
        $tags = (array) $this->config->get('disallowed_raw_html/disallowed_tags');
        if (\count($tags) === 0) {
            return $rendered;
        }
        $regex = \sprintf('/<(\/?(?:%s)[\s\/>])/i', \implode('|', \array_map('preg_quote', $tags)));
        // Match these types of tags: <title> </title> <title x="sdf"> <title/> <title />
        return \preg_replace($regex, '&lt;$1', $rendered);
    }
    public function set_configuration(Configuration_Interface $configuration): void
    {
        $this->config = $configuration;
        if ($this->inner_renderer instanceof Configuration_Aware_Interface) {
            $this->inner_renderer->set_configuration($configuration);
        }
    }
}