<?php
namespace Apitude\File\Controller;

use Apitude\Core\API\Controller\AbstractCrudController;
use Apitude\File\Services\LocalFileService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FileController extends AbstractCrudController implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    const ERROR_NO_FILE_UPLOADED      = 'No file uploaded';
    const ERROR_FILE_SIZE_EXCEEDED    = 'File size exceeded';
    const ERROR_FILE_TYPE_NOT_ALLOWED = 'File type not allowed';

    protected $apiRecordType = 'Apitude.File.Entities.FileEntity';

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
     * Validate the file in the request, write it to the filesystem, and create an entity
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function post(Request $request)
    {
        try {
            $uploadedFile = $this->validateFileRequest($request);

            $file = $this->getLocalFileService()->writeAndCreateEntity($uploadedFile);

            return new JsonResponse($this->getApiWriter()->writeObject($file), Response::HTTP_CREATED);

        } catch (FileException $e) {
            $this->logger->error($e->getMessage(), ['files' => $request->files->all()]);

            return new JsonResponse($e->getMessage(), $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

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
            throw new FileException(self::ERROR_NO_FILE_UPLOADED, Response::HTTP_BAD_REQUEST);
        }

        // check file restrictions
        /** @var UploadedFile $uploadedFile */
        $file = $request->files->getIterator()->current();

        if ($this->fileSizeRestriction && $this->fileSizeRestriction < $file->getSize()) {
            throw new FileException(self::ERROR_FILE_SIZE_EXCEEDED, Response::HTTP_BAD_REQUEST);
        }
        if (!empty($this->fileTypeRestrictions)
            && !in_array($file->getMimeType(), $this->fileTypeRestrictions)) {
            throw new FileException(self::ERROR_FILE_TYPE_NOT_ALLOWED, Response::HTTP_BAD_REQUEST);
        }

        return $file;
    }

    /**
     * @return LocalFileService
     */
    protected function getLocalFileService()
    {
        return $this->container[LocalFileService::class];
    }
}