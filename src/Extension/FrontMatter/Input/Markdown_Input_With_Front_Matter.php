<?php

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace League\Common_Mark\Extension\Front_Matter\Input;

use League\Common_Mark\Extension\Front_Matter\Front_Matter_Provider_Interface;
use League\Common_Mark\Input\Markdown_Input;
final class Markdown_Input_With_Front_Matter extends Markdown_Input implements Front_Matter_Provider_Interface
{
    /** @var mixed|null */
    private $front_matter;
    /**
     * @param string     $content     Markdown content without the raw front matter
     * @param int        $lineOffset  Line offset (based on number of front matter lines removed)
     * @param mixed|null $frontMatter Parsed front matter
     */
    public function __construct(string $content, int $line_offset = 0, $front_matter = null)
    {
        parent::__construct($content, $line_offset);
        $this->front_matter = $front_matter;
    }
    /**
     * {@inheritDoc}
     */
    public function get_front_matter()
    {
        return $this->front_matter;
    }
}