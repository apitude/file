<?php
namespace Apitude\File\Services;

use Apitude\Core\Provider\ContainerAwareInterface;
use Apitude\Core\Provider\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use League\Flysystem\Adapter\AbstractAdapter;

abstract class AbstractFileService implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * Writes to the filesystem
     *
     * @param UploadedFile $file
     * @param string       $fileName
     */
    public function write(UploadedFile $file, $fileName = null)
    {
        if ($fileName === null) {
            $fileName = $file->getClientOriginalName();
        }

        $fs = fopen($file->getPathname(), 'r');

        $config = [];

        $this->getFileAdapter()->write($fileName, $fs, $config);
    }

    /**
     * Reads from the filesystem
     *
     * @param File $file
     * @return array|false
     */
    public function read(File $file)
    {
        return $this->getFileAdapter()->readStream($file->getPath());
    }

    /**
     * Deletes from the filesystem
     *
     * @param File $file
     * @return array|false
     */
    public function delete(File $file)
    {
        return $this->getFileAdapter()->delete($file->getPath());
    }

    /**
     * @return AbstractAdapter
     */
    abstract protected function getFileAdapter();
}
