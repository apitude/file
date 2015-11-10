<?php
namespace Apitude\File\Services;

class LocalFileService extends AbstractFileService
{
    protected function getFileAdapter()
    {
        return $this->container['flysystems']['local__DIR__'];
    }
}