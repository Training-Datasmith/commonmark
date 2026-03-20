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
namespace League\Common_Mark\Extension\Front_Matter\Listener;

use League\Common_Mark\Event\Document_Rendered_Event;
use League\Common_Mark\Extension\Front_Matter\Output\Rendered_Content_With_Front_Matter;
final class Front_Matter_Post_Render_Listener
{
    public function __invoke(Document_Rendered_Event $event): void
    {
        if ($event->get_output()->get_document()->data->get('front_matter', null) === null) {
            return;
        }
        $front_matter = $event->get_output()->get_document()->data->get('front_matter');
        $event->replace_output(new Rendered_Content_With_Front_Matter($event->get_output()->get_document(), $event->get_output()->get_content(), $front_matter));
    }
}