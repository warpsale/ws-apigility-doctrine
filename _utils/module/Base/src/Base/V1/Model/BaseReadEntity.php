<?php
namespace Base\V1\Model;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\MappedSuperclass
 * @ORM\Table(indexes={@ORM\Index(columns={"is_active"})})
 */
abstract class BaseReadEntity
{
    /** 
     * @ORM\Column(name="is_active", type="boolean", options={"default":true}) 
     * @var bool
     */
    protected $isActive;
    
    /** 
     * @ORM\Column(type="smallint", options={"default":0}) 
     * @var int
     */
    protected $version;
    
    /** 
     * @ORM\Column(type="integer", options={"default":0}) 
     * @var int
     */
    protected $owner;
    
    /** 
     * @ORM\Column(name="created_at", type="integer", options={"default":0}) 
     * @var int
     */
    protected $createdAt;
    
    /** 
     * @ORM\Column(name="created_by", type="integer", options={"default":0}) 
     * @var int
     */
    protected $createdBy;
    
    /** 
     * @ORM\Column(name="updated_at", type="integer", nullable=true) 
     * @var int
     */
    protected $updatedAt;
    
    /** 
     * @ORM\Column(name="updated_by", type="integer", nullable=true) 
     * @var int
     */
    protected $updatedBy;
    
    /** 
     * @ORM\Column(name="deleted_at", type="integer", nullable=true) 
     * @var int
     */
    protected $deletedAt;
    
    /** 
     * @ORM\Column(name="deleted_by", type="integer", nullable=true) 
     * @var int
     */
    protected $deletedBy;

    public function __toString()
    {
        return method_exists($this, 'getName') ? $this->getName() : (string)$this->getId();
    }
    
    /**
     * @return the $id
     */
    public abstract function getId();

    /**
     * @return the $isActive
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * @return the $version
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return the $owner
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @return the $createdAt
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return the $createdBy
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @return the $updatedAt
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return the $updatedBy
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * @return the $deletedAt
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * @return the $deletedBy
     */
    public function getDeletedBy()
    {
        return $this->deletedBy;
    }
}