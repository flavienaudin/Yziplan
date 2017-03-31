<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 14/02/2017
 * Time: 12:31
 */

namespace AppBundle\Utils\Naming;


use AppBundle\Entity\Event\Event;
use AppBundle\Entity\Module\PollProposal;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;

class PictureDirectoryNamer implements DirectoryNamerInterface
{
    /**
     * {@inheritdoc}
     */
    public function directoryName($object, PropertyMapping $mapping)
    {
        $subdir = 'unkownobject';
        if ($object instanceof Event) {
            if ($object->getCreatedAt() != null) {
                $subdir = date("Y/m/", $object->getCreatedAt()->getTimestamp()) . $object->getToken();
            } else {
                $subdir = date("Y/m/") . $object->getToken();
            }
        } elseif ($object instanceof PollProposal) {
            if ($object->getCreatedAt() != null) {
                $subdir = date("Y/m/", $object->getCreatedAt()->getTimestamp());
            } else {
                $subdir = date("Y/m/");
            }
        }
        return $subdir;
    }
}