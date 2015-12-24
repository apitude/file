<?php
namespace Apitude\File\Controller;

use Apitude\Core\API\Controller\AbstractCrudController;
use Apitude\File\FileAwareTrait;
use Apitude\File\Services\LocalFileService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FileController extends AbstractCrudController implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    use FileAwareTrait;

    protected $apiRecordType = 'Apitude.File.Entities.FileEntity';

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

            $file = $this->getFileService()->writeUploadedFileAndCreateEntity($uploadedFile);

            return new JsonResponse($this->getApiWriter()->writeObject($file), Response::HTTP_CREATED);

        } catch (FileException $e) {
            $this->logger->error($e->getMessage(), ['files' => $request->files->all()]);

            return new JsonResponse($e->getMessage(), $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
