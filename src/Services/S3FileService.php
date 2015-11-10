<?php
namespace Apitude\File\Services;

class S3FileService extends AbstractFileService
{
    protected function getFileAdapter()
    {
        return $this->container['flysystems']['s3'];
    }
}