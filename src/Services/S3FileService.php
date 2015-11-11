<?php
namespace Apitude\File\Services;

class S3FileService extends AbstractFileService
{
    const FILESYSTEM_TYPE = 's3';

    protected function getFilesystemType()
    {
        return self::FILESYSTEM_TYPE;
    }
}