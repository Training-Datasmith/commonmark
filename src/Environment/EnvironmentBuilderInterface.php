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

use League\Common_Mark\Delimiter\Processor\Delimiter_Processor_Interface;
use League\Common_Mark\Exception\Already_Initialized_Exception;
use League\Common_Mark\Extension\Extension_Interface;
use League\Common_Mark\Node\Node;
use League\Common_Mark\Parser\Block\Block_Start_Parser_Interface;
use League\Common_Mark\Parser\Inline\Inline_Parser_Interface;
use League\Common_Mark\Renderer\Node_Renderer_Interface;
use League\Config\Configuration_Provider_Interface;
/**
 * Interface for building the Environment with any extensions, parsers, listeners, etc. that it may need
 */
interface Environment_Builder_Interface extends Configuration_Provider_Interface
{
    /**
     * Registers the given extension with the Environment
     *
     * @throws AlreadyInitializedException if the Environment has already been initialized
     */
    public function add_extension(Extension_Interface $extension): Environment_Builder_Interface;
    /**
     * Registers the given block start parser with the Environment
     *
     * @param BlockStartParserInterface $parser   Block parser instance
     * @param int                       $priority Priority (a higher number will be executed earlier)
     *
     *
     * @throws AlreadyInitializedException if the Environment has already been initialized
     */
    public function add_block_start_parser(Block_Start_Parser_Interface $parser, int $priority = 0): Environment_Builder_Interface;
    /**
     * Registers the given inline parser with the Environment
     *
     * @param InlineParserInterface $parser   Inline parser instance
     * @param int                   $priority Priority (a higher number will be executed earlier)
     *
     *
     * @throws AlreadyInitializedException if the Environment has already been initialized
     */
    public function add_inline_parser(Inline_Parser_Interface $parser, int $priority = 0): Environment_Builder_Interface;
    /**
     * Registers the given delimiter processor with the Environment
     *
     * @param DelimiterProcessorInterface $processor Delimiter processors instance
     *
     * @throws AlreadyInitializedException if the Environment has already been initialized
     */
    public function add_delimiter_processor(Delimiter_Processor_Interface $processor): Environment_Builder_Interface;
    /**
     * Registers the given node renderer with the Environment
     *
     * @param string                $nodeClass The fully-qualified node element class name the renderer below should handle
     * @param NodeRendererInterface $renderer  The renderer responsible for rendering the type of element given above
     * @param int                   $priority  Priority (a higher number will be executed earlier)
     *
     * @psalm-param class-string<Node> $nodeClass
     *
     *
     * @throws AlreadyInitializedException if the Environment has already been initialized
     */
    public function add_renderer(string $node_class, Node_Renderer_Interface $renderer, int $priority = 0): Environment_Builder_Interface;
    /**
     * Registers the given event listener
     *
     * @param class-string $eventClass Fully-qualified class name of the event this listener should respond to
     * @param callable     $listener   Listener to be executed
     * @param int          $priority   Priority (a higher number will be executed earlier)
     *
     *
     * @throws AlreadyInitializedException if the Environment has already been initialized
     */
    public function add_event_listener(string $event_class, callable $listener, int $priority = 0): Environment_Builder_Interface;
}