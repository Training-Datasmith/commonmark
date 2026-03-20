<?php

declare (strict_types=1);
/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * Original code based on the CommonMark JS reference parser (http://bitly.com/commonmark-js)
 *  - (c) John MacFarlane
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace League\Common_Mark\Extension\Smart_Punct;

use League\Common_Mark\Node\Inline\Abstract_String_Container;
final class Quote extends Abstract_String_Container
{
    public const DOUBLE_QUOTE = '"';
    public const DOUBLE_QUOTE_OPENER = '“';
    public const DOUBLE_QUOTE_CLOSER = '”';
    public const SINGLE_QUOTE = "'";
    public const SINGLE_QUOTE_OPENER = '‘';
    public const SINGLE_QUOTE_CLOSER = '’';
}