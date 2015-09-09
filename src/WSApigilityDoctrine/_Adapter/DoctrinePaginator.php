<?php
namespace WSApigilityDoctrine\_Adapter;

class DoctrinePaginator
{
    private $qb;
    private $results;
    private $page;
    
    public function __construct($qb, $results, $page)
    {
        $this->qb = $qb;
        $this->results = $results;
        $this->page = $page;
    }
    
    public function getUpdatedQueryBuilder() 
    {
        $offset = ($this->page-1) * $this->results;
        
        $this->qb->addSelect('TCOUNT() AS TCount')
            ->setFirstResult($offset)
            ->setMaxResults($this->results);

        return $this->qb;
    }
}