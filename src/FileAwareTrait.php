<?php
namespace Apitude\File;

use Apitude\Core\Application;
use Apitude\File\Services\FileService;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FileAwareTrait
 * @property Application container
 */
trait FileAwareTrait
{
    /**
     * File size limitation for upload
     * Zero = unlimited
     * @var int
     */
    protected $fileSizeRestriction = 0;

    /**
     * File type (mime type) limitations
     * @var array
     */
    protected $fileTypeRestrictions = [];

    /**
     * Validates the file in the request according to the size restrictions and type restrictions
     * Returns true if valid, returns a JsonResponse if invalid
     *
     * @param Request $request
     * @return UploadedFile
     * @throws FileException
     */
    public function validateFileRequest(Request $request)
    {
        if ($request->files->count() != 1) {
            throw new FileException('No file uploaded', Response::HTTP_BAD_REQUEST);
        }

        // check file restrictions
        /** @var UploadedFile $uploadedFile */
        $file = $request->files->getIterator()->current();

        // Set size and file type restrictions from config
        if (isset($this->container['config']['files'][$recordType]['maxsize'])) {
            $this->fileSizeRestriction = $this->container['config']['files'][$recordType]['maxsize'];
        }
        if (isset($this->container['config']['files'][$recordType]['types'])) {
            $this->$fileTypeRestrictions = $this->container['config']['files'][$recordType]['types'];
        }

        if ($this->fileSizeRestriction && $this->fileSizeRestriction < $file->getSize()) {
            throw new FileException('File size exceeded', Response::HTTP_BAD_REQUEST);
        }

        if (!empty($this->fileTypeRestrictions)
            && !in_array($file->getMimeType(), $this->fileTypeRestrictions)) {
            throw new FileException('File type not allowed', Response::HTTP_BAD_REQUEST);
        }

        return $file;
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->container[FileService::class];
    }
}