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
     * Writes an UploadedFile to the filesystem and returns a FileEntity
     *
     * @param UploadedFile $file
     * @param string       $recordType
     * @param string       $subPath
     * @return FileEntity
     */
    public function writeUploadedFileAndCreateEntity(UploadedFile $file, $recordType = self::DEFAULT_RECORDTYPE, $subPath = DIRECTORY_SEPARATOR)
    {
        list($path, $filesystem, $settings, $url) = $this->getConfigValuesForRecordType($recordType);

        $fileName = uniqid() . '.' . $file->guessExtension();
        $fullPath = $path . $subPath . $fileName;
        $url      = $url . DIRECTORY_SEPARATOR . $path . $subPath . $fileName;

        $results = $this->getFilesystem($filesystem)->writeStream($fullPath, fopen($file->getPathname(), 'r'), $settings);

        // If the write was unsuccessful, throw an exception
        if ($results === false) {
            throw new FileException('Unable to create file.');
        }

        return $this->createFileEntity($fileName, $recordType, $file->getMimeType(), $filesystem, $path, $url, $file->getSize());
    }

    /**
     * Writes string to the filesystem and returns a FileEntity
     *
     * @param string $contents
     * @param string $fileName
     * @param string $recordType
     * @param string $subPath
     * @return FileEntity
     */
    public function writeFileAndCreateEntity($contents, $fileName, $recordType = self::DEFAULT_RECORDTYPE, $subPath = DIRECTORY_SEPARATOR)
    {
        list($path, $filesystem, $settings, $url) = $this->getConfigValuesForRecordType($recordType);

        $fullPath = $path . $subPath . $fileName;
        $url      = $url . DIRECTORY_SEPARATOR . $path . $subPath . $fileName;

        $results = $this->getFilesystem($filesystem)->write($fullPath, $contents, $settings);

        // If the write was unsuccessful, throw an exception
        if ($results === false) {
            throw new FileException('Unable to create file.');
        }

        $mimeType = $this->getFilesystem($filesystem)->getMimetype($fullPath);
        $size     = strlen($contents);

        return $this->createFileEntity($fileName, $recordType, $mimeType, $filesystem, $path, $url, $size);
    }

    /**
     * @param UploadedFile $uploadedFile
     * @param FileEntity   $fileEntity
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
     * Deletes a file from the filesystem and then deletes the FileEntity
     * @param FileEntity $fileEntity
     * @return FileEntity
     * @throws FileException
     */
    public function deleteFile(FileEntity $fileEntity)
    {
        list($path, $filesystem) = $this->getConfigValuesForRecordType($fileEntity->getRecordType());

        /** @var Filesystem $fileSystem */
        $filesystem = $this->getFilesystem($filesystem);

        if ($filesystem->has($path . DIRECTORY_SEPARATOR . $fileEntity->getFileName())) {
            $results = $filesystem->delete($path . DIRECTORY_SEPARATOR . $fileEntity->getFileName());

            if (!$results) {
                throw new FileException('Unable to delete file');
            }
        }

        /** @var EntityManager $em */
        $em = $this->container['orm.em'];

        $em->remove($fileEntity);

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

    /**
     * Creates and returns a FileEntity
     *
     * @param $fileName
     * @param $recordType
     * @param $contentType
     * @param $filesystem
     * @param $path
     * @param $url
     * @param $size
     * @return FileEntity
     */
    protected function createFileEntity($fileName, $recordType, $contentType, $filesystem, $path, $url, $size)
    {
        /** @var EntityManager $em */
        $em = $this->container['orm.em'];

        $entity = new FileEntity();

        $entity->setFileName($fileName)
            ->setRecordType($recordType)
            ->setContentType($contentType)
            ->setFilesystem($filesystem)
            ->setPath($path)
            ->setUrl($url)
            ->setSize($size);

        $em->persist($entity);
        $em->flush();

        return $entity;
    }
}
