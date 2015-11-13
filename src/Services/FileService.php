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
    const DEFAULT_URL        = '/';

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
        list($path, $filesystem, $settings, $url) = $this->getConfigValuesForRecordType($recordType);

        $fileName = uniqid() . '.' . $file->guessExtension();
        $fullPath = $path . DIRECTORY_SEPARATOR . $fileName;
        $url      = $url . DIRECTORY_SEPARATOR .  $path . DIRECTORY_SEPARATOR . $fileName;

        $results = $this->getFilesystem($filesystem)->writeStream($fullPath, fopen($file->getPathname(), 'r'), $settings);

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
            ->setUrl($url)
            ->setSize($file->getSize());

        $em->persist($entity);
        $em->flush();

        return $entity;
    }

    /**
     * @param UploadedFile $uploadedFile
     * @param FileEntity $fileEntity
     * @return bool
     */
    public function putUploadedFile(UploadedFile $uploadedFile, FileEntity $fileEntity)
    {
        list($path, $filesystem, $settings) = $this->getConfigValuesForRecordType($fileEntity->getRecordType());

        $fullPath = $path . DIRECTORY_SEPARATOR . $fileEntity->getFileName();

        $this->getFilesystem($filesystem)->putStream($fullPath, fopen($uploadedFile->getPathname(), 'r'), $settings);

        $fileEntity->setContentType($uploadedFile->getMimeType())
            ->setSize($uploadedFile->getSize());

        /** @var EntityManager $em */
        $em = $this->container['orm.em'];

        $em->flush();

        return $fileEntity;
    }

    /**
     * @param string $type
     * @return Filesystem
     */
    protected function getFilesystem($type)
    {
        return $this->container['flysystems'][$type];
    }

    /**
     * Returns a list of values from the File Config, or defaults if not found
     *
     * @param string $recordType
     * @return array
     */
    protected function getConfigValuesForRecordType($recordType)
    {
        $config = $this->container['config']['files'];

        $filesystem = isset($config['record_types'][$recordType]['filesystem']) ?
            $config['record_types'][$recordType]['filesystem'] :
            self::DEFAULT_FILESYSTEM;

        return [
            isset($config['record_types'][$recordType]['path']) ?
                $config['record_types'][$recordType]['path'] :
                self::DEFAULT_PATH,
            $filesystem,
            isset($config['record_types'][$recordType]['settings']) ?
                $config['record_types'][$recordType]['settings'] :
                $this->settings,
            isset($config['filesystems'][$filesystem]) ?
                $config['filesystems'][$filesystem] :
                self::DEFAULT_URL,
        ];
    }
}
