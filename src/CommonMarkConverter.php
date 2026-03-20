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
namespace League\Common_Mark;

use League\Common_Mark\Environment\Environment;
use League\Common_Mark\Extension\Common_Mark\Common_Mark_Core_Extension;
/**
 * Converts CommonMark-compatible Markdown to HTML.
 */
final class Common_Mark_Converter extends Markdown_Converter
{
    /**
     * Create a new Markdown converter pre-configured for CommonMark
     *
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        $environment = new Environment($config);
        $environment->add_extension(new Common_Mark_Core_Extension());
        parent::__construct($environment);
    }
    public function get_environment(): Environment
    {
        \assert($this->environment instanceof Environment);
        return $this->environment;
    }
}