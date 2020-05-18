<?php


namespace kiraxe\AdminCrmBundle\Services\FileUploaderImages;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;


class FileUploaderImages
{
    private $targetDir;

    public function __construct($targetDir)
    {
        $this->targetDir = $targetDir;
    }

    public function upload(UploadedFile $file, $order)
    {
        $filesystem = new Filesystem();

        //$filesystem->remove([$this->getTargetDir().'order_'.$order->getId()]);

        if(!$filesystem->exists($this->getTargetDir().'order_'.$order->getId()))
        {
            try {
                    $filesystem->mkdir($this->getTargetDir() .'order_' . $order->getId());
            } catch (IOExceptionInterface $exception) {
                    echo "An error occurred while creating your directory at " . $exception->getPath();
            }
        }

        $fileName = md5(uniqid()) . '.' . $file->guessExtension();
        $file->move($this->getTargetDir().'/'. 'order_'.$order->getId(), $fileName);

        return $fileName;
    }

    public function getTargetDir()
    {
        return $this->targetDir;
    }
}