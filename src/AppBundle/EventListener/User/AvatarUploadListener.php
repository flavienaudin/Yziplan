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

    private function uploadFile($entity)
    {
        // upload only works for Product entities
        if (!$entity instanceof AppUserInformation) {
            return;
        }

        $file = $entity->getAvatar();

        $fileName = $this->uploader->upload($file);
        if($fileName != null){
            $entity->setAvatar($fileName);
        }else{
            return;
        }
    }
}