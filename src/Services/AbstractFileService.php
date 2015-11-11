<?php
namespace Apitude\File\Services;

use Apitude\Core\Provider\ContainerAwareInterface;
use Apitude\Core\Provider\ContainerAwareTrait;
use Apitude\File\Entities\FileEntity;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
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
     * Writes to the filesystem and creates a FileEntity
     *
     * @param UploadedFile $file
     * @param string $recordType
     * @return FileEntity|false
     */
    public function writeAndCreateEntity(UploadedFile $file, $recordType = 'file')
    {
        $fileName = uniqid();

        $results = $this->write($file, $recordType, $fileName);

        // If the write was unsuccessful, throw an exception
        if ($results === false) {
            throw new FileException('Unable to create file.');
        }

        $path = $recordType . '/' . $fileName;

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

        return $entity;
    }

    /**
     * Writes to the filesystem
     *
     * @param UploadedFile $file
     * @param string       $recordType
     * @param string       $fileName
     * @return array|false
     */
    public function write(UploadedFile $file, $recordType = 'file', $fileName = null)
    {
        $fs = fopen($file->getPathname(), 'r');

        if ($fileName === null) {
            $fileName = uniqid();
        }

        $path = $recordType . '/' . $fileName;

        return $this->getFilesystem()->writeStream($path, $fs, $this->settings);
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
