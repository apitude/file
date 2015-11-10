<?php
namespace Apitude\File\Services;

use Apitude\Core\Provider\ContainerAwareInterface;
use Apitude\Core\Provider\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use League\Flysystem\Filesystem;

abstract class AbstractFileService implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * Writes to the filesystem
     *
     * @param UploadedFile $file
     * @param string       $fileName
     * @return array|false
     */
    public function write(UploadedFile $file, $fileName = null)
    {
        if ($fileName === null) {
            $fileName = $file->getClientOriginalName();
        }

        $fs = fopen($file->getPathname(), 'r');

        return $this->getFilesystem()->writeStream($fileName, $fs, $this->settings);
    }

    /**
     * Reads from the filesystem
     *
     * @param File $file
     * @return array|false
     */
    public function read(File $file)
    {
        return $this->getFilesystem()->readStream($file->getPath());
    }

    /**
     * Deletes from the filesystem
     *
     * @param File $file
     * @return array|false
     */
    public function delete(File $file)
    {
        return $this->getFilesystem()->delete($file->getPath());
    }

    /**
     * @return Filesystem
     */
    abstract protected function getFilesystem();
}
