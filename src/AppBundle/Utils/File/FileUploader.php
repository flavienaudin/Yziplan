<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 24/10/2016
 * Time: 22:51
 */

namespace AppBundle\Utils\File;


use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    /**
     * @var string Path to directory where to move file
     */
    private $targetDir;

    public function __construct($targetDir)
    {
        $this->targetDir = $targetDir;
    }

    /**
     * @return string
     */
    public function getTargetDir()
    {
        return $this->targetDir;
    }

    /**
     * Give the web-relative path of the target dir
     * @return string
     */
    public function getWebRelativeTargetDir()
    {
        return substr($this->targetDir, strpos($this->targetDir, 'web') + 4);

    }

    public function upload(UploadedFile $file)
    {
        // Generate a unique name for the file before saving it
        $fileName = md5(uniqid('avatar_')) . '.' . $file->guessExtension();
        // Move the file t  o the directory where brochures are stored
        $file->move($this->targetDir, $fileName);

        return $fileName;
    }

    public function delete(File $file)
    {
        $fs = new Filesystem();
        $fs->remove($file->getRealPath());
    }
}