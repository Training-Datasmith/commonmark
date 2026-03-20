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
namespace League\Common_Mark\Extension\External_Link;

use League\Common_Mark\Environment\Environment_Builder_Interface;
use League\Common_Mark\Event\Document_Parsed_Event;
use League\Common_Mark\Extension\Configurable_Extension_Interface;
use League\Config\Configuration_Builder_Interface;
use Nette\Schema\Expect;
final class External_Link_Extension implements Configurable_Extension_Interface
{
    public function configure_schema(Configuration_Builder_Interface $builder): void
    {
        $apply_options = [External_Link_Processor::APPLY_NONE, External_Link_Processor::APPLY_ALL, External_Link_Processor::APPLY_INTERNAL, External_Link_Processor::APPLY_EXTERNAL];
        $builder->add_schema('external_link', Expect::structure(['internal_hosts' => Expect::type('string|string[]'), 'open_in_new_window' => Expect::bool(false), 'html_class' => Expect::string()->default(''), 'nofollow' => Expect::any_of(...$apply_options)->default(External_Link_Processor::APPLY_NONE), 'noopener' => Expect::any_of(...$apply_options)->default(External_Link_Processor::APPLY_EXTERNAL), 'noreferrer' => Expect::any_of(...$apply_options)->default(External_Link_Processor::APPLY_EXTERNAL)]));
    }
    public function register(Environment_Builder_Interface $environment): void
    {
        $environment->add_event_listener(Document_Parsed_Event::class, new External_Link_Processor($environment->get_configuration()), -50);
    }
}