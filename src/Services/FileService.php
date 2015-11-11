<?php
namespace Apitude\File\Services;

use Apitude\Core\Provider\ContainerAwareInterface;
use Apitude\Core\Provider\ContainerAwareTrait;
use Apitude\File\Entities\FileEntity;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\EntityManager;
use League\Flysystem\Filesystem;

class FileService implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    const DEFAULT_RECORDTYPE = 'file';
    const DEFAULT_FILESYSTEM = 'local__DIR__';
    const DEFAULT_PATH       = 'files';

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * Writes to the filesystem and creates a FileEntity
     *
     * @param UploadedFile $file
     * @param string $recordType
     * @return FileEntity
     * @throws FileException
     */
    public function writeUploadedFileAndCreateEntity(UploadedFile $file, $recordType = self::DEFAULT_RECORDTYPE)
    {
        $filesConfig = $this->container['config']['files'];

        $path = isset($filesConfig['record_types'][$recordType]['path']) ?
            $filesConfig[$recordType]['path'] :
            self::DEFAULT_PATH;

        $filesystem = isset($filesConfig['record_types'][$recordType]['filesystem']) ?
            $filesConfig['record_types'][$recordType]['filesystem'] :
            self::DEFAULT_FILESYSTEM;

        $fileName = uniqid() . '.' . $file->guessExtension();
        $fullPath = $path . DIRECTORY_SEPARATOR . $fileName;

        $results = $this->getFilesystem($filesystem)->writeStream(
            $fullPath,
            fopen($file->getPathname(), 'r'),
            $this->settings);

        // If the write was unsuccessful, throw an exception
        if ($results === false) {
            throw new FileException('Unable to create file.');
        }

        /** @var EntityManager $em */
        $em = $this->container['orm.em'];

        $entity = new FileEntity();

        $entity->setFileName($fileName)
            ->setRecordType($recordType)
            ->setContentType($file->getMimeType())
            ->setFilesystem($filesystem)
            ->setPath($path)
            ->setFileName($fileName)
            ->setSize($file->getSize());

        $em->persist($entity);
        $em->flush();

        return $entity;
    }

    /**
     * @param string $type
     * @return Filesystem
     */
    protected function getFilesystem($type)
    {
        return $this->container['flysystems'][$type];
    }
}
