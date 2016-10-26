<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 25/10/2016
 * Time: 11:44
 */

namespace AppBundle\EventListener\User;


use AppBundle\Entity\User\AppUserInformation;
use AppBundle\Utils\File\FileUploader;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AvatarUploadListener
{
    /** @var FileUploader */
    private $uploader;

    public function __construct(FileUploader $uploader)
    {
        $this->uploader = $uploader;
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $this->uploadFile($entity);
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        $this->uploadFile($entity);
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        /** @var AppUserInformation $entity */
        $entity = $args->getEntity();
        if ($entity instanceof AppUserInformation) {
            $fileName = $entity->getAvatar();
            if (!empty($fileName)) {
                $entity->setAvatar(new File($this->uploader->getTargetDir() . DIRECTORY_SEPARATOR . $fileName));
            }
        }
    }

    private function uploadFile($entity)
    {
        // upload only works for Product entities
        if (!$entity instanceof AppUserInformation) {
            return;
        }

        $file = $entity->getAvatar();

        // only upload new files
        if (!$file instanceof UploadedFile) {
            return;
        }

        $fileName = $this->uploader->upload($file);
        $entity->setAvatar($fileName);
    }
}