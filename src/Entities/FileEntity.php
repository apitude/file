<?php
namespace Apitude\File\Entities;

use Apitude\Core\Entities\AbstractEntity;
use Apitude\Core\EntityStubs\StampEntityInterface;
use Apitude\Core\EntityStubs\StampEntityTrait;
use Apitude\Core\Annotations\API;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class File
 * @package Apitude\File\Entities
 * @ORM\Entity()
 * @ORM\Table(name="files", indexes={@ORM\Index(name="k_url", columns={"url"})})
 *
 * @API\Entity\Expose()
 */
class FileEntity extends AbstractEntity implements StampEntityInterface
{
    use StampEntityTrait;

    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @API\Property\Expose()
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=128)
     * @API\Property\Expose()
     */
    private $filesystem;

    /**
     * @var string
     * @ORM\Column(name="record_type", type="string", length=128)
     * @API\Property\Expose()
     */
    private $recordType;

    /**
     * @var string
     * @ORM\Column(name="file_name", type="string", length=128)
     * @API\Property\Expose()
     */
    private $fileName;

    /**
     * @var string
     * @ORM\Column(name="content_type", type="string", length=128)
     * @API\Property\Expose()
     */
    private $contentType;

    /**
     * @var string
     * @ORM\Column(type="string", length=1000)
     * @API\Property\Expose()
     */
    private $path;

    /**
     * @var int
     * @ORM\Column(type="integer", length=11)
     * @API\Property\Expose()
     */
    private $size;


    /**
     * @var string
     * @ORM\Column(type="string", length=1256)
     * @API\Property\Expose
     */
    private $url;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getRecordType()
    {
        return $this->recordType;
    }

    /**
     * @param $recordType
     * @return FileEntity
     */
    public function setRecordType($recordType)
    {
        $this->recordType = $recordType;
        return $this;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     * @return FileEntity
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @param string $contentType
     * @return FileEntity
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return FileEntity
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param int $size
     * @return FileEntity
     */
    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @return string
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * @param string $filesystem
     * @return FileEntity
     */
    public function setFilesystem($filesystem)
    {
        $this->filesystem = $filesystem;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return FileEntity
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }
}