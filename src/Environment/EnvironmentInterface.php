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
namespace League\Common_Mark\Environment;

use League\Common_Mark\Delimiter\Processor\Delimiter_Processor_Collection;
use League\Common_Mark\Extension\Extension_Interface;
use League\Common_Mark\Node\Node;
use League\Common_Mark\Normalizer\Text_Normalizer_Interface;
use League\Common_Mark\Parser\Block\Block_Start_Parser_Interface;
use League\Common_Mark\Parser\Inline\Inline_Parser_Interface;
use League\Common_Mark\Renderer\Node_Renderer_Interface;
use League\Config\Configuration_Provider_Interface;
use Psr\Event_Dispatcher\Event_Dispatcher_Interface;
interface Environment_Interface extends Configuration_Provider_Interface, Event_Dispatcher_Interface
{
    /**
     * Get all registered extensions
     *
     * @return ExtensionInterface[]
     */
    public function get_extensions(): iterable;
    /**
     * @return iterable<BlockStartParserInterface>
     */
    public function get_block_start_parsers(): iterable;
    /**
     * @return iterable<InlineParserInterface>
     */
    public function get_inline_parsers(): iterable;
    public function get_delimiter_processors(): Delimiter_Processor_Collection;
    /**
     * @psalm-param class-string<Node> $nodeClass
     *
     * @return iterable<NodeRendererInterface>
     */
    public function get_renderers_for_class(string $node_class): iterable;
    public function get_slug_normalizer(): Text_Normalizer_Interface;
}