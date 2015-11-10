<?php
namespace Apitude\File\Services;

class S3FileService extends AbstractFileService
{
    protected function getFilesystem()
    {
        return $this->container['flysystems']['s3'];
    }
}