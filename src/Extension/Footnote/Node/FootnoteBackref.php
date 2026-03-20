<?php

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 * (c) Rezo Zero / Ambroise Maupate
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace League\Common_Mark\Extension\Footnote\Node;

use League\Common_Mark\Node\Inline\Abstract_Inline;
use League\Common_Mark\Reference\Referenceable_Interface;
use League\Common_Mark\Reference\Reference_Interface;
/**
 * Link from the footnote on the bottom of the document back to the reference
 */
final class Footnote_Backref extends Abstract_Inline implements Referenceable_Interface
{
    /** @psalm-readonly */
    private Reference_Interface $reference;
    public function __construct(Reference_Interface $reference)
    {
        parent::__construct();
        $this->reference = $reference;
    }
    public function get_reference(): Reference_Interface
    {
        return $this->reference;
    }
}