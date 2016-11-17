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

    /**
     * @var string String to use for generating uniqueid for the filename
     */
    private $fileprefix;

    public function __construct($targetDir, $fileprefix)
    {
        $this->targetDir = $targetDir;
        $this->fileprefix = $fileprefix;
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

    /**
     * @param $file UploadedFile or URL
     * @return string|null
     */
    public function upload($file)
    {
        // only upload new files
        if ($file instanceof UploadedFile) {
            // Generate a unique name for the file before saving it
            $fileName = md5(uniqid($this->fileprefix)) . '.' . $file->guessExtension();
            // Move the file to the directory where brochures are stored
            $file->move($this->targetDir, $fileName);

            return $fileName;

        } else {
            if (!filter_var($file, FILTER_VALIDATE_URL) === false) {
                $fileName = md5(uniqid($this->fileprefix));
                file_put_contents($this->targetDir . $fileName, file_get_contents($file));
                $file = new File($this->targetDir . $fileName);
                $fileName = $fileName . '.' . $file->guessExtension();
                $file->move($this->targetDir, $fileName);

                return $fileName;

            } else {
                return null;
            }
        }
    }

    public function delete($filename)
    {
        $fileFullPath = $this->targetDir . DIRECTORY_SEPARATOR . $filename;
        $fs = new Filesystem();
        if ($fs->exists($fileFullPath)) {
            $fs->remove($fileFullPath);
            return true;
        }
        return false;
    }
}