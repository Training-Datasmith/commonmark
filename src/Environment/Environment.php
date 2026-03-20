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
namespace League\Common_Mark\Environment;

use League\Common_Mark\Delimiter\Delimiter_Parser;
use League\Common_Mark\Delimiter\Processor\Delimiter_Processor_Collection;
use League\Common_Mark\Delimiter\Processor\Delimiter_Processor_Interface;
use League\Common_Mark\Event\Document_Parsed_Event;
use League\Common_Mark\Event\Listener_Data;
use League\Common_Mark\Exception\Already_Initialized_Exception;
use League\Common_Mark\Extension\Common_Mark\Common_Mark_Core_Extension;
use League\Common_Mark\Extension\Configurable_Extension_Interface;
use League\Common_Mark\Extension\Extension_Interface;
use League\Common_Mark\Extension\Github_Flavored_Markdown_Extension;
use League\Common_Mark\Normalizer\Slug_Normalizer;
use League\Common_Mark\Normalizer\Text_Normalizer_Interface;
use League\Common_Mark\Normalizer\Unique_Slug_Normalizer;
use League\Common_Mark\Normalizer\Unique_Slug_Normalizer_Interface;
use League\Common_Mark\Parser\Block\Block_Start_Parser_Interface;
use League\Common_Mark\Parser\Block\Skip_Lines_Starting_With_Letters_Parser;
use League\Common_Mark\Parser\Inline\Inline_Parser_Interface;
use League\Common_Mark\Renderer\Node_Renderer_Interface;
use League\Common_Mark\Util\Html_Filter;
use League\Common_Mark\Util\Prioritized_List;
use League\Config\Configuration;
use League\Config\Configuration_Aware_Interface;
use League\Config\Configuration_Interface;
use Nette\Schema\Expect;
use Psr\Event_Dispatcher\Event_Dispatcher_Interface;
use Psr\Event_Dispatcher\Listener_Provider_Interface;
use Psr\Event_Dispatcher\Stoppable_Event_Interface;
final class Environment implements Environment_Interface, Environment_Builder_Interface, Listener_Provider_Interface
{
    /**
     * @var ExtensionInterface[]
     *
     * @psalm-readonly-allow-private-mutation
     */
    private array $extensions = [];
    /**
     * @var ExtensionInterface[]
     *
     * @psalm-readonly-allow-private-mutation
     */
    private array $uninitialized_extensions = [];
    /** @psalm-readonly-allow-private-mutation */
    private bool $extensions_initialized = false;
    /**
     * @var PrioritizedList<BlockStartParserInterface>
     *
     * @psalm-readonly
     */
    private Prioritized_List $block_start_parsers;
    /**
     * @var PrioritizedList<InlineParserInterface>
     *
     * @psalm-readonly
     */
    private Prioritized_List $inline_parsers;
    /** @psalm-readonly */
    private Delimiter_Processor_Collection $delimiter_processors;
    /**
     * @var array<string, PrioritizedList<NodeRendererInterface>>
     *
     * @psalm-readonly-allow-private-mutation
     */
    private array $renderers_by_class = [];
    /**
     * @var PrioritizedList<ListenerData>
     *
     * @psalm-readonly-allow-private-mutation
     */
    private Prioritized_List $listener_data;
    private ?Event_Dispatcher_Interface $event_dispatcher = null;
    /** @psalm-readonly */
    private Configuration $config;
    private ?Text_Normalizer_Interface $slug_normalizer = null;
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        $this->config = self::create_default_configuration();
        $this->config->merge($config);
        $this->block_start_parsers = new Prioritized_List();
        $this->inline_parsers = new Prioritized_List();
        $this->listener_data = new Prioritized_List();
        $this->delimiter_processors = new Delimiter_Processor_Collection();
        // Performance optimization: always include a block "parser" that aborts parsing if a line starts with a letter
        // and is therefore unlikely to match any lines as a block start.
        $this->add_block_start_parser(new Skip_Lines_Starting_With_Letters_Parser(), 249);
    }
    public function get_configuration(): Configuration_Interface
    {
        return $this->config->reader();
    }
    /**
     * @deprecated Environment::mergeConfig() is deprecated since league/commonmark v2.0 and will be removed in v3.0. Configuration should be set when instantiating the environment instead.
     *
     * @param array<string, mixed> $config
     */
    public function merge_config(array $config): void
    {
        @\trigger_error('Environment::mergeConfig() is deprecated since league/commonmark v2.0 and will be removed in v3.0. Configuration should be set when instantiating the environment instead.', \E_USER_DEPRECATED);
        $this->assert_uninitialized('Failed to modify configuration.');
        $this->config->merge($config);
    }
    public function add_block_start_parser(Block_Start_Parser_Interface $parser, int $priority = 0): Environment_Builder_Interface
    {
        $this->assert_uninitialized('Failed to add block start parser.');
        $this->block_start_parsers->add($parser, $priority);
        $this->inject_environment_and_configuration_if_needed($parser);
        return $this;
    }
    public function add_inline_parser(Inline_Parser_Interface $parser, int $priority = 0): Environment_Builder_Interface
    {
        $this->assert_uninitialized('Failed to add inline parser.');
        $this->inline_parsers->add($parser, $priority);
        $this->inject_environment_and_configuration_if_needed($parser);
        return $this;
    }
    public function add_delimiter_processor(Delimiter_Processor_Interface $processor): Environment_Builder_Interface
    {
        $this->assert_uninitialized('Failed to add delimiter processor.');
        $this->delimiter_processors->add($processor);
        $this->inject_environment_and_configuration_if_needed($processor);
        return $this;
    }
    public function add_renderer(string $node_class, Node_Renderer_Interface $renderer, int $priority = 0): Environment_Builder_Interface
    {
        $this->assert_uninitialized('Failed to add renderer.');
        if (!isset($this->renderers_by_class[$node_class])) {
            $this->renderers_by_class[$node_class] = new Prioritized_List();
        }
        $this->renderers_by_class[$node_class]->add($renderer, $priority);
        $this->inject_environment_and_configuration_if_needed($renderer);
        return $this;
    }
    /**
     * {@inheritDoc}
     */
    public function get_block_start_parsers(): iterable
    {
        if (!$this->extensions_initialized) {
            $this->initialize_extensions();
        }
        return $this->block_start_parsers->getIterator();
    }
    public function get_delimiter_processors(): Delimiter_Processor_Collection
    {
        if (!$this->extensions_initialized) {
            $this->initialize_extensions();
        }
        return $this->delimiter_processors;
    }
    /**
     * {@inheritDoc}
     */
    public function get_renderers_for_class(string $node_class): iterable
    {
        if (!$this->extensions_initialized) {
            $this->initialize_extensions();
        }
        // If renderers are defined for this specific class, return them immediately
        if (isset($this->renderers_by_class[$node_class])) {
            return $this->renderers_by_class[$node_class];
        }
        /** @psalm-suppress TypeDoesNotContainType -- Bug: https://github.com/vimeo/psalm/issues/3332 */
        while (\class_exists($parent ??= $node_class) && $parent = \get_parent_class($parent)) {
            if (!isset($this->renderers_by_class[$parent])) {
                continue;
            }
            // "Cache" this result to avoid future loops
            return $this->renderers_by_class[$node_class] = $this->renderers_by_class[$parent];
        }
        return [];
    }
    /**
     * {@inheritDoc}
     */
    public function get_extensions(): iterable
    {
        return $this->extensions;
    }
    /**
     * Add a single extension
     *
     * @return $this
     */
    public function add_extension(Extension_Interface $extension): Environment_Builder_Interface
    {
        $this->assert_uninitialized('Failed to add extension.');
        $this->extensions[] = $extension;
        $this->uninitialized_extensions[] = $extension;
        if ($extension instanceof Configurable_Extension_Interface) {
            $extension->configure_schema($this->config);
        }
        return $this;
    }
    private function initialize_extensions(): void
    {
        // Initialize the slug normalizer
        $this->get_slug_normalizer();
        // Ask all extensions to register their components
        while (\count($this->uninitialized_extensions) > 0) {
            foreach ($this->uninitialized_extensions as $i => $extension) {
                $extension->register($this);
                unset($this->uninitialized_extensions[$i]);
            }
        }
        $this->extensions_initialized = true;
        // Create the special delimiter parser if any processors were registered
        if ($this->delimiter_processors->count() > 0) {
            $this->inline_parsers->add(new Delimiter_Parser($this->delimiter_processors), PHP_INT_MIN);
        }
    }
    private function inject_environment_and_configuration_if_needed(object $object): void
    {
        if ($object instanceof Environment_Aware_Interface) {
            $object->set_environment($this);
        }
        if ($object instanceof Configuration_Aware_Interface) {
            $object->set_configuration($this->config->reader());
        }
    }
    /**
     * @deprecated Instantiate the environment and add the extension yourself
     *
     * @param array<string, mixed> $config
     */
    public static function create_common_mark_environment(array $config = []): Environment
    {
        $environment = new self($config);
        $environment->add_extension(new Common_Mark_Core_Extension());
        return $environment;
    }
    /**
     * @deprecated Instantiate the environment and add the extension yourself
     *
     * @param array<string, mixed> $config
     */
    public static function create_gfm_environment(array $config = []): Environment
    {
        $environment = new self($config);
        $environment->add_extension(new Common_Mark_Core_Extension());
        $environment->add_extension(new Github_Flavored_Markdown_Extension());
        return $environment;
    }
    public function add_event_listener(string $event_class, callable $listener, int $priority = 0): Environment_Builder_Interface
    {
        $this->assert_uninitialized('Failed to add event listener.');
        $this->listener_data->add(new Listener_Data($event_class, $listener), $priority);
        if (\is_object($listener)) {
            $this->inject_environment_and_configuration_if_needed($listener);
        } elseif (\is_array($listener) && \is_object($listener[0])) {
            $this->inject_environment_and_configuration_if_needed($listener[0]);
        }
        return $this;
    }
    public function dispatch(object $event): object
    {
        if (!$this->extensions_initialized) {
            $this->initialize_extensions();
        }
        if ($this->event_dispatcher !== null) {
            return $this->event_dispatcher->dispatch($event);
        }
        foreach ($this->get_listeners_for_event($event) as $listener) {
            if ($event instanceof Stoppable_Event_Interface && $event->is_propagation_stopped()) {
                return $event;
            }
            $listener($event);
        }
        return $event;
    }
    public function set_event_dispatcher(Event_Dispatcher_Interface $dispatcher): void
    {
        $this->event_dispatcher = $dispatcher;
    }
    /**
     * {@inheritDoc}
     *
     * @return iterable<callable>
     */
    public function get_listeners_for_event(object $event): iterable
    {
        foreach ($this->listener_data as $listener_data) {
            \assert($listener_data instanceof Listener_Data);
            /** @psalm-suppress ArgumentTypeCoercion */
            if (!\is_a($event, $listener_data->get_event())) {
                continue;
            }
            yield function (object $event) use ($listener_data) {
                if (!$this->extensions_initialized) {
                    $this->initialize_extensions();
                }
                return \call_user_func($listener_data->get_listener(), $event);
            };
        }
    }
    /**
     * @return iterable<InlineParserInterface>
     */
    public function get_inline_parsers(): iterable
    {
        if (!$this->extensions_initialized) {
            $this->initialize_extensions();
        }
        return $this->inline_parsers->getIterator();
    }
    public function get_slug_normalizer(): Text_Normalizer_Interface
    {
        if ($this->slug_normalizer === null) {
            $normalizer = $this->config->get('slug_normalizer/instance');
            \assert($normalizer instanceof Text_Normalizer_Interface);
            $this->inject_environment_and_configuration_if_needed($normalizer);
            if ($this->config->get('slug_normalizer/unique') !== Unique_Slug_Normalizer_Interface::DISABLED && !$normalizer instanceof Unique_Slug_Normalizer) {
                $normalizer = new Unique_Slug_Normalizer($normalizer);
            }
            if ($normalizer instanceof Unique_Slug_Normalizer) {
                if ($this->config->get('slug_normalizer/unique') === Unique_Slug_Normalizer_Interface::PER_DOCUMENT) {
                    $this->add_event_listener(Document_Parsed_Event::class, [$normalizer, 'clearHistory'], -1000);
                }
            }
            $this->slug_normalizer = $normalizer;
        }
        return $this->slug_normalizer;
    }
    /**
     * @throws AlreadyInitializedException
     */
    private function assert_uninitialized(string $message): void
    {
        if ($this->extensions_initialized) {
            throw new Already_Initialized_Exception($message . ' Extensions have already been initialized.');
        }
    }
    public static function create_default_configuration(): Configuration
    {
        return new Configuration(['html_input' => Expect::any_of(Html_Filter::STRIP, Html_Filter::ALLOW, Html_Filter::ESCAPE)->default(Html_Filter::ALLOW), 'allow_unsafe_links' => Expect::bool(true), 'max_nesting_level' => Expect::type('int')->default(PHP_INT_MAX), 'max_delimiters_per_line' => Expect::type('int')->default(PHP_INT_MAX), 'renderer' => Expect::structure(['block_separator' => Expect::string("\n"), 'inner_separator' => Expect::string("\n"), 'soft_break' => Expect::string("\n")]), 'slug_normalizer' => Expect::structure(['instance' => Expect::type(Text_Normalizer_Interface::class)->default(new Slug_Normalizer()), 'max_length' => Expect::int()->min(0)->default(255), 'unique' => Expect::any_of(Unique_Slug_Normalizer_Interface::DISABLED, Unique_Slug_Normalizer_Interface::PER_ENVIRONMENT, Unique_Slug_Normalizer_Interface::PER_DOCUMENT)->default(Unique_Slug_Normalizer_Interface::PER_DOCUMENT)])]);
    }
}