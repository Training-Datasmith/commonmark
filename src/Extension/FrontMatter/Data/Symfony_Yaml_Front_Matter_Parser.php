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
use Symfony\Component\Yaml\Exception\Parse_Exception;
use Symfony\Component\Yaml\Yaml;
final class Symfony_Yaml_Front_Matter_Parser implements Front_Matter_Data_Parser_Interface
{
    /**
     * {@inheritDoc}
     */
    public function parse(string $front_matter)
    {
        if (!\class_exists(Yaml::class)) {
            throw new Missing_Dependency_Exception('Failed to parse yaml: "symfony/yaml" library is missing');
        }
        try {
            /** @psalm-suppress ReservedWord */
            return Yaml::parse($front_matter);
        } catch (Parse_Exception $ex) {
            throw Invalid_Front_Matter_Exception::wrap($ex);
        }
    }
}