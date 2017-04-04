<?php

namespace AppBundle\Utils\Listener;

use AppBundle\Entity\Module\PollProposal;
use AppBundle\Utils\File\ImageResize;
use Vich\UploaderBundle\Event\Event;

/**
 * Created by PhpStorm.
 * User: Patman
 * Date: 24/03/2017
 * Time: 10:31
 */
class ImageResizerListener
{
    public function onPostUpload(Event $event)
    {
        $object = $event->getObject();

        // do your stuff with $object and/or $mapping...
        $imageResizer = new ImageResize();

        if ($object instanceof PollProposal) {
            /**
             * @var $object PollProposal
             */
            $fileName = $object->getPictureFile()->getPathname();
            return $imageResizer->smart_resize_image($fileName, null, 300, 0, true,'file', true, false,90);
        }
        elseif ($object instanceof \AppBundle\Entity\Event\Event) {
            /**
             * @var $object \AppBundle\Entity\Event\Event
             */
            $fileName = $object->getPictureFile()->getPathname();
            return $imageResizer->smart_resize_image($fileName, null, 1000, 0, true, 'file', true, false,90);
        }
        return null;
    }
}