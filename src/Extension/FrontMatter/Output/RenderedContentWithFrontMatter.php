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
namespace League\Common_Mark\Extension\Front_Matter\Output;

use League\Common_Mark\Extension\Front_Matter\Front_Matter_Provider_Interface;
use League\Common_Mark\Node\Block\Document;
use League\Common_Mark\Output\Rendered_Content;
/**
 * @psalm-immutable
 */
final class Rendered_Content_With_Front_Matter extends Rendered_Content implements Front_Matter_Provider_Interface
{
    /**
     * @var mixed
     *
     * @psalm-readonly
     */
    private $front_matter;
    /**
     * @param Document   $document    The parsed Document object
     * @param string     $content     The final HTML
     * @param mixed|null $frontMatter Any parsed front matter
     */
    public function __construct(Document $document, string $content, $front_matter)
    {
        parent::__construct($document, $content);
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