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
namespace League\Common_Mark\Extension\Common_Mark\Node\Inline;

use League\Common_Mark\Node\Inline\Abstract_String_Container;
use League\Common_Mark\Node\Raw_Markup_Container_Interface;
final class Html_Inline extends Abstract_String_Container implements Raw_Markup_Container_Interface
{
}