<?php
namespace Apitude\File\Services;

class LocalFileService extends AbstractFileService
{
    const FILESYSTEM_TYPE = 'local__DIR__';

    protected function getFilesystemType()
    {
        return self::FILESYSTEM_TYPE;
    }
}