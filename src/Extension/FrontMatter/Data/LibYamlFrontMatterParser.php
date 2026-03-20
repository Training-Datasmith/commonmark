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
namespace League\Common_Mark\Extension\Front_Matter\Data;

use League\Common_Mark\Exception\Missing_Dependency_Exception;
use League\Common_Mark\Extension\Front_Matter\Exception\Invalid_Front_Matter_Exception;
final class Lib_Yaml_Front_Matter_Parser implements Front_Matter_Data_Parser_Interface
{
    public static function capable(): ?Lib_Yaml_Front_Matter_Parser
    {
        if (!\extension_loaded('yaml')) {
            return null;
        }
        return new Lib_Yaml_Front_Matter_Parser();
    }
    /**
     * {@inheritDoc}
     */
    public function parse(string $front_matter)
    {
        if (!\extension_loaded('yaml')) {
            throw new Missing_Dependency_Exception('Failed to parse yaml: "ext-yaml" extension is missing');
        }
        $result = @\yaml_parse($front_matter);
        if ($result === false) {
            throw new Invalid_Front_Matter_Exception('Failed to parse front matter');
        }
        return $result;
    }
}