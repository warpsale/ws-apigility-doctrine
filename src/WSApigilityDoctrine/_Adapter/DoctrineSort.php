<?php
namespace WSApigilityDoctrine\_Adapter;

class DoctrineSort 
{
    private $qb;
    private $sort;
    
    public function __construct($qb, $sort)
    {
        $this->qb = $qb;
        $this->sort = $sort;
    }
    
    public function getUpdatedQueryBuilder() 
    {
        $items = explode(',', $this->sort);
        
        for ($i=0; $i<count($items); $i++) {
            $order = strpos($items[$i], '-') === FALSE ? 'ASC' : 'DESC';
            $field = (strpos($items[$i], '.') === FALSE ? 'e.' : '') . str_replace(['-', '+'], ['', ''], trim($items[$i]));
            if ($i == 0) {
                $this->qb->orderBy($field, $order);
            } else {
                $this->qb->addOrderBy($field, $order);
            }
        }
        
        return $this->qb;
    }
}