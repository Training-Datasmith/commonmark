<?php

declare (strict_types=1);
/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com> and uAfrica.com (http://uafrica.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace League\Common_Mark\Extension\Strikethrough;

use League\Common_Mark\Environment\Environment_Builder_Interface;
use League\Common_Mark\Extension\Extension_Interface;
final class Strikethrough_Extension implements Extension_Interface
{
    public function register(Environment_Builder_Interface $environment): void
    {
        $environment->add_delimiter_processor(new Strikethrough_Delimiter_Processor());
        $environment->add_renderer(Strikethrough::class, new Strikethrough_Renderer());
    }
}