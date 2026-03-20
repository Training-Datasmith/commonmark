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
namespace League\Common_Mark\Extension\Mention;

use League\Common_Mark\Environment\Environment_Builder_Interface;
use League\Common_Mark\Extension\Configurable_Extension_Interface;
use League\Common_Mark\Extension\Mention\Generator\Mention_Generator_Interface;
use League\Config\Configuration_Builder_Interface;
use League\Config\Exception\Invalid_Configuration_Exception;
use Nette\Schema\Expect;
final class Mention_Extension implements Configurable_Extension_Interface
{
    public function configure_schema(Configuration_Builder_Interface $builder): void
    {
        $is_a_valid_partial_regex = static function (string $regex): bool {
            $regex = '/' . $regex . '/i';
            return @\preg_match($regex, '') !== false;
        };
        $builder->add_schema('mentions', Expect::array_of(Expect::structure(['prefix' => Expect::string()->required(), 'pattern' => Expect::string()->assert($is_a_valid_partial_regex, 'Pattern must not include starting/ending delimiters (like "/")')->required(), 'generator' => Expect::any_of(Expect::type(Mention_Generator_Interface::class), Expect::string(), Expect::type('callable'))->required()])));
    }
    public function register(Environment_Builder_Interface $environment): void
    {
        $mentions = $environment->get_configuration()->get('mentions');
        foreach ($mentions as $name => $mention) {
            if ($mention['generator'] instanceof Mention_Generator_Interface) {
                $environment->add_inline_parser(new Mention_Parser($name, $mention['prefix'], $mention['pattern'], $mention['generator']));
            } elseif (\is_string($mention['generator'])) {
                $environment->add_inline_parser(Mention_Parser::create_with_string_template($name, $mention['prefix'], $mention['pattern'], $mention['generator']));
            } elseif (\is_callable($mention['generator'])) {
                $environment->add_inline_parser(Mention_Parser::create_with_callback($name, $mention['prefix'], $mention['pattern'], $mention['generator']));
            } else {
                throw new Invalid_Configuration_Exception(\sprintf('The "generator" provided for the "%s" MentionParser configuration must be a string template, callable, or an object that implements %s.', $name, Mention_Generator_Interface::class));
            }
        }
    }
}