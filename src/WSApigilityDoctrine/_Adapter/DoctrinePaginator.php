<?php
namespace WSApigilityDoctrine\_Adapter;

class DoctrinePaginator
{
    private $platform;
    private $qb;
    private $results;
    private $page;
    
    public function __construct($platform, $qb, $results, $page)
    {
        $this->platform = $platform;
        $this->qb = $qb;
        $this->results = $results;
        $this->page = $page;
    }
    
    public function getUpdatedQueryBuilder() 
    {
        $offset = ($this->page-1) * $this->results;
        
        if ($this->platform == 'postgresql') {
            $this->qb->addSelect('TCOUNT() AS TCount');
        }
        
        $this->qb->setFirstResult($offset)
            ->setMaxResults($this->results);

        return $this->qb;
    }
}