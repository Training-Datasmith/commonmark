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

use League\Common_Mark\Environment\Environment_Builder_Interface;
use League\Common_Mark\Extension\Configurable_Extension_Interface;
use League\Common_Mark\Renderer\Html_Decorator;
use League\Config\Configuration_Builder_Interface;
use Nette\Schema\Expect;
final class Table_Extension implements Configurable_Extension_Interface
{
    public function configure_schema(Configuration_Builder_Interface $builder): void
    {
        $attribute_array_schema = Expect::array_of(
            Expect::type('string|string[]|bool'),
            // attribute value(s)
            'string'
        )->merge_defaults(false);
        $builder->add_schema('table', Expect::structure(['wrap' => Expect::structure(['enabled' => Expect::bool()->default(false), 'tag' => Expect::string()->default('div'), 'attributes' => Expect::array_of(Expect::string())]), 'alignment_attributes' => Expect::structure(['left' => (clone $attribute_array_schema)->default(['align' => 'left']), 'center' => (clone $attribute_array_schema)->default(['align' => 'center']), 'right' => (clone $attribute_array_schema)->default(['align' => 'right'])]), 'max_autocompleted_cells' => Expect::int()->min(0)->default(Table_Parser::DEFAULT_MAX_AUTOCOMPLETED_CELLS)]));
    }
    public function register(Environment_Builder_Interface $environment): void
    {
        $table_renderer = new Table_Renderer();
        if ($environment->get_configuration()->get('table/wrap/enabled')) {
            $table_renderer = new Html_Decorator($table_renderer, $environment->get_configuration()->get('table/wrap/tag'), $environment->get_configuration()->get('table/wrap/attributes'));
        }
        $environment->add_block_start_parser(new Table_Start_Parser($environment->get_configuration()->get('table/max_autocompleted_cells')))->add_renderer(Table::class, $table_renderer)->add_renderer(Table_Section::class, new Table_Section_Renderer())->add_renderer(Table_Row::class, new Table_Row_Renderer())->add_renderer(Table_Cell::class, new Table_Cell_Renderer($environment->get_configuration()->get('table/alignment_attributes')));
    }
}