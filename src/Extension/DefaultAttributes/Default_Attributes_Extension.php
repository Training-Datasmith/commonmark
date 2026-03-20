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
namespace League\Common_Mark\Extension\Default_Attributes;

use League\Common_Mark\Environment\Environment_Builder_Interface;
use League\Common_Mark\Event\Document_Parsed_Event;
use League\Common_Mark\Extension\Configurable_Extension_Interface;
use League\Config\Configuration_Builder_Interface;
use Nette\Schema\Expect;
final class Default_Attributes_Extension implements Configurable_Extension_Interface
{
    public function configure_schema(Configuration_Builder_Interface $builder): void
    {
        $builder->add_schema('default_attributes', Expect::array_of(Expect::array_of(
            Expect::type('string|string[]|bool|callable'),
            // attribute value(s)
            'string'
        ), 'string')->default([]));
    }
    public function register(Environment_Builder_Interface $environment): void
    {
        $environment->add_event_listener(Document_Parsed_Event::class, [new Apply_Default_Attributes_Processor(), 'onDocumentParsed']);
    }
}