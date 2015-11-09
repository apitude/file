<?php
namespace Apitude\File\Controller;

use Apitude\Core\API\Controller\AbstractCrudController;
use Apitude\File\Entities\FileEntity;
use League\Flysystem\Config;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use League\Flysystem\Adapter\AbstractAdapter;

abstract class AbstractFileController extends AbstractCrudController implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    const ERROR_NO_FILE_UPLOADED      = 'No file uploaded';
    const ERROR_FILE_SIZE_EXCEEDED    = 'File size exceeded';
    const ERROR_FILE_TYPE_NOT_ALLOWED = 'File type not allowed';

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

    public function post(Request $request)
    {
        try {
            if ($request->files->count() != 1) {
                return new JsonResponse(self::ERROR_NO_FILE_UPLOADED, Response::HTTP_BAD_REQUEST);
            }

            // check file restrictions
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $request->files->getIterator()->current();
            if ($this->fileSizeRestriction && $this->fileSizeRestriction < $uploadedFile->getSize()) {
                return new JsonResponse(self::ERROR_FILE_SIZE_EXCEEDED, Response::HTTP_BAD_REQUEST);
            }
            if (!empty($this->fileTypeRestrictions)
                && !in_array($uploadedFile->getMimeType(), $this->fileTypeRestrictions)) {
                return new JsonResponse(self::ERROR_FILE_TYPE_NOT_ALLOWED, Response::HTTP_BAD_REQUEST);
            }

            $file = $this->getFileService()->write($uploadedFile->getFilename(), $uploadedFile, new Config());
        } catch (FileException $e) {
            $this->logger->error($e->getMessage(), ['files' => $request->files->all()]);

            return new JsonResponse($e->getMessage(), $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $response = new JsonResponse($file, Response::HTTP_CREATED);

        return $response;
    }

    protected function isRequestForDownload(Request $request)
    {
        return $request->get('download') ? true : false;
    }

    public function get(Request $request, FileEntity $fileEntity)
    {
        if (!$fileEntity) {
            return new JsonResponse(Response::$statusTexts[Response::HTTP_NOT_FOUND], Response::HTTP_NOT_FOUND);
        }

        if ($this->isRequestForDownload($request)) {
            $disposition = 'attachment';
        } else {
            $disposition = 'inline';
        }
        $fileName = $fileEntity->getFileName();

        $headers = [
            'Content-Type'        => $fileEntity->getContentType(),
            'Content-Disposition' => "{$disposition}; filename={$fileName}",
            'Content-Length'      => intval($fileEntity->getSize()),
        ];

        $stream = $this->getFileService()->readStream($fileEntity->getPath());

        return new StreamedResponse($stream, 200, $headers);
    }

    /**
     * @param Request    $request
     * @param FileEntity $fileEntity
     *
     * @return bool|\Symfony\Component\HttpFoundation\Response
     */
    public function delete(Request $request, $fileEntity)
    {
        if (!$fileEntity) {
            return new JsonResponse(Response::$statusTexts[Response::HTTP_NOT_FOUND], Response::HTTP_NOT_FOUND);
        }

        return $this->getFileService()->delete($fileEntity->getPath());
    }

    /**
     * @return AbstractAdapter
     */
    private function getFileService()
    {
        return $this->container['file.service'];
    }
}