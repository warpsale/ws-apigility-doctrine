<?php

/**
 * @Entity(readOnly=true)
 * @Table(name="row")
 */
class Row
{
    /**
     * @Id @Column(type="smallint")
     * @var int
     */
    private $id;

    /**
     * @Column(type="string", length=32)
     * @var string
     */
    private $name;

    /**
     * @return the $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return the $name
     */
    public function getName()
    {
        return $this->name;
    }
}