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
namespace League\Common_Mark\Event;

use League\Common_Mark\Output\Rendered_Content_Interface;
final class Document_Rendered_Event extends Abstract_Event
{
    private Rendered_Content_Interface $output;
    public function __construct(Rendered_Content_Interface $output)
    {
        $this->output = $output;
    }
    /**
     * @psalm-mutation-free
     */
    public function get_output(): Rendered_Content_Interface
    {
        return $this->output;
    }
    /**
     * @psalm-external-mutation-free
     */
    public function replace_output(Rendered_Content_Interface $output): void
    {
        $this->output = $output;
    }
}