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
namespace League\Common_Mark\Extension\Heading_Permalink;

use League\Common_Mark\Environment\Environment_Builder_Interface;
use League\Common_Mark\Event\Document_Parsed_Event;
use League\Common_Mark\Extension\Configurable_Extension_Interface;
use League\Config\Configuration_Builder_Interface;
use Nette\Schema\Expect;
/**
 * Extension which automatically anchor links to heading elements
 */
final class Heading_Permalink_Extension implements Configurable_Extension_Interface
{
    public function configure_schema(Configuration_Builder_Interface $builder): void
    {
        $builder->add_schema('heading_permalink', Expect::structure(['min_heading_level' => Expect::int()->min(1)->max(6)->default(1), 'max_heading_level' => Expect::int()->min(1)->max(6)->default(6), 'insert' => Expect::any_of(Heading_Permalink_Processor::INSERT_BEFORE, Heading_Permalink_Processor::INSERT_AFTER, Heading_Permalink_Processor::INSERT_NONE)->default(Heading_Permalink_Processor::INSERT_BEFORE), 'id_prefix' => Expect::string()->default('content'), 'apply_id_to_heading' => Expect::bool()->default(false), 'heading_class' => Expect::string()->default(''), 'fragment_prefix' => Expect::string()->default('content'), 'html_class' => Expect::string()->default('heading-permalink'), 'title' => Expect::string()->default('Permalink'), 'symbol' => Expect::string()->default(Heading_Permalink_Renderer::DEFAULT_SYMBOL), 'aria_hidden' => Expect::bool()->default(true)]));
    }
    public function register(Environment_Builder_Interface $environment): void
    {
        $environment->add_event_listener(Document_Parsed_Event::class, new Heading_Permalink_Processor(), -100);
        $environment->add_renderer(Heading_Permalink::class, new Heading_Permalink_Renderer());
    }
}