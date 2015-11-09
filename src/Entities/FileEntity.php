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
 * @ORM\Table(name="files", indexes={@ORM\Index(name="k_path", columns={"path"})})
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
    private $record_type;

    /**
     * @var string
     * @ORM\Column(type="string", length=128)
     * @API\Property\Expose()
     */
    private $file_name;

    /**
     * @var string
     * @ORM\Column(type="string", length=128)
     * @API\Property\Expose()
     */
    private $content_type;

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
        return $this->record_type;
    }

    /**
     * @param $record_type
     * @return AbstractFileEntity
     */
    public function setRecordType($record_type)
    {
        $this->record_type = $record_type;
        return $this;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->file_name;
    }

    /**
     * @param string $file_name
     * @return AbstractFileEntity
     */
    public function setFileName($file_name)
    {
        $this->file_name = $file_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->content_type;
    }

    /**
     * @param string $content_type
     * @return AbstractFileEntity
     */
    public function setContentType($content_type)
    {
        $this->content_type = $content_type;
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
     * @return AbstractFileEntity
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
     * @return AbstractFileEntity
     */
    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }
}