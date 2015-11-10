<?php
namespace Apitude\File\Services;

class LocalFileService extends AbstractFileService
{
    protected function getFilesystem()
    {
        return $this->container['flysystems']['local__DIR__'];
    }
}