<?php
namespace Base\V1\Model;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class BaseEntity extends BaseReadEntity
{
    /**
     * @param bool $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * @param int $version
     */
    private function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @param int $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * @param int $createdAt
     */
    private function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @param int $createdBy
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
    }

    /**
     * @param int $updatedAt
     */
    private function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @param int $updatedBy
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;
    }

    /**
     * @param int $deletedAt
     */
    private function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;
    }

    /**
     * @param int $deletedBy
     */
    public function setDeletedBy($deletedBy)
    {
        $this->deletedBy = $deletedBy;
    }
    
    public function __construct()
    {
        $this->isActive = true;
        $this->version = 0;
        $this->owner = 0;
        $this->createdAt = 0;
        $this->createdBy = 0;
    }
    
    /**
     * @ORM\PrePersist
     */
    final public function prePersist() {
        $this->version = 0;
        $this->createdAt = time();
    }
    
    /**
     * @ORM\PreUpdate
     */
    final public function preUpdate() {
        $this->version++;
        $this->updatedAt = time();
    }
    
    /**
     * @ORM\PreRemove
     */
    final public function preRemove() {
        $this->deletedAt = time();
    }
}