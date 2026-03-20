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
namespace League\Common_Mark\Extension\Embed;

use League\Common_Mark\Environment\Environment_Builder_Interface;
use League\Common_Mark\Event\Document_Parsed_Event;
use League\Common_Mark\Extension\Configurable_Extension_Interface;
use League\Config\Configuration_Builder_Interface;
use Nette\Schema\Expect;
final class Embed_Extension implements Configurable_Extension_Interface
{
    public function configure_schema(Configuration_Builder_Interface $builder): void
    {
        $builder->add_schema('embed', Expect::structure(['adapter' => Expect::type(Embed_Adapter_Interface::class), 'allowed_domains' => Expect::array_of('string')->default([]), 'fallback' => Expect::any_of('link', 'remove')->default('link')]));
    }
    public function register(Environment_Builder_Interface $environment): void
    {
        $adapter = $environment->get_configuration()->get('embed.adapter');
        \assert($adapter instanceof Embed_Adapter_Interface);
        $allowed_domains = $environment->get_configuration()->get('embed.allowed_domains');
        if ($allowed_domains !== []) {
            $adapter = new Domain_Filtering_Adapter($adapter, $allowed_domains);
        }
        $environment->add_block_start_parser(new Embed_Start_Parser(), 300)->add_event_listener(Document_Parsed_Event::class, new Embed_Processor($adapter, $environment->get_configuration()->get('embed.fallback')), 1010)->add_renderer(Embed::class, new Embed_Renderer());
    }
}