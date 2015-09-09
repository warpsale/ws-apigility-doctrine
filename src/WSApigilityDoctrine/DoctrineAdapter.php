<?php
namespace WSApigilityDoctrine;

use Zend\Paginator\Adapter\AdapterInterface;
use WSApigilityDoctrine\_Adapter\DoctrinePaginator;
use WSApigilityDoctrine\_Adapter\DoctrineQueryRql;
use WSApigilityDoctrine\_Adapter\DoctrineSort;

class DoctrineAdapter implements AdapterInterface
{
    protected $array = null;
    protected $count = null;

    public function __construct($qb, $params, $results)
    {
        // QUERY
        $rql = isset($params['query']) ? $params['query'] : NULL;
        if ($rql) { 
            $dq = new DoctrineQueryRql($qb, $rql);
            $qb = $dq->getUpdatedQueryBuilder();
        }
        
        // SORT
        $sort = isset($params['sort']) ? $params['sort'] : NULL;
        if ($sort) { 
            $ds = new DoctrineSort($qb, $sort);
            $qb = $ds->getUpdatedQueryBuilder();
        }
        
        // LIMIT AND OFFSET
        $results = isset($params['results']) && intval($params['results']) > 0 ? $params['results'] : $results;
        $page = isset($params['page']) && intval($params['page']) > 0 ? $params['page'] : 1;
        if ($results && $page) {
            $dp = new DoctrinePaginator($qb, $results, $page);
            $qb = $dp->getUpdatedQueryBuilder();
        }

        $data = $qb->getQuery()
                    ->getArrayResult();

        $this->count = count($data) > 0 ? $data[0]['TCount'] : 0; 
        array_walk($data, function(&$row){ unset($row['TCount']); });
        $this->array = $data;
    }
    
    /**
     * Returns an array of items for a page.
     *
     * @param  int $offset Page offset
     * @param  int $itemCountPerPage Number of items per page
     * @return array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        return $this->array;
    }
    
    /**
     * Returns the total number of rows in the array.
     *
     * @return int
     */
    public function count()
    { 
        return $this->count;
    }
}
