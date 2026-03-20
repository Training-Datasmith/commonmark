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
namespace League\Common_Mark;

use League\Common_Mark\Exception\Common_Mark_Exception;
use League\Common_Mark\Output\Rendered_Content_Interface;
use League\Config\Exception\Configuration_Exception_Interface;
/**
 * Interface for a service which converts content from one format (like Markdown) to another (like HTML).
 */
interface Converter_Interface
{
    /**
     * @throws CommonMarkException
     * @throws ConfigurationExceptionInterface
     */
    public function convert(string $input): Rendered_Content_Interface;
}