<?php
namespace Apitude\File\Services;

use Apitude\Core\Provider\ContainerAwareInterface;
use Apitude\Core\Provider\ContainerAwareTrait;
use Apitude\File\Entities\FileEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\EntityManager;
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
     * @param string       $recordType
     * @return bool
     */
    public function write(UploadedFile $file, $recordType = '')
    {
        $fs = fopen($file->getPathname(), 'r');

        $fileName = uniqid();
        $path     = $recordType . '/' . $fileName;

        $results = $this->getFilesystem()->writeStream($path, $fs, $this->settings);

        // If the write was successful, add it as an entity
        if ($results === true) {
            /** @var EntityManager $em */
            $em = $this->container['orm.em'];

            $entity = new FileEntity();

            $entity->setFileName($fileName)
                ->setRecordType($recordType)
                ->setContentType($file->getMimeType())
                ->setFilesystem($this->getFilesystemType())
                ->setPath($path)
                ->setFileName($fileName)
                ->setSize($file->getSize());

            $em->persist($entity);
            $em->flush();
        }

        return $results;
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
    protected function getFilesystem()
    {
        return $this->container['flysystems'][$this->getFilesystemType()];
    }

    /**
     * Implement this by simply returning a string containing the name of the filesystem, e.g. s3
     * @return string
     */
    abstract protected function getFilesystemType();
}
