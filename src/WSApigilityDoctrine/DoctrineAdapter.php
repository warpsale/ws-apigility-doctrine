<?php
namespace WSApigilityDoctrine;

use Zend\Paginator\Adapter\AdapterInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use WSApigilityDoctrine\_Adapter\DoctrinePaginator;
use WSApigilityDoctrine\_Adapter\DoctrineQueryRql;
use WSApigilityDoctrine\_Adapter\DoctrineSort;

class DoctrineAdapter implements AdapterInterface
{
    protected $array = null;
    protected $count = null;

    public function __construct($qb, $fields, $params, $results)
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
        $platform = $qb->getEntityManager()->getConnection()->getDatabasePlatform()->getName();
        $results = isset($params['results']) && intval($params['results']) > 0 ? $params['results'] : $results;
        $page = isset($params['page']) && intval($params['page']) > 0 ? $params['page'] : 1;
        
        if ($platform && $results && $page) {
            $dp = new DoctrinePaginator($platform, $qb, $results, $page);
            $qb = $dp->getUpdatedQueryBuilder();
        }

        $query = $qb->getQuery();
        $data = $query->getArrayResult();
        
        $this->count = count($data) < 1 ? 0 : ($platform == 'postgresql' ? $data[0]['TCount'] : count(new Paginator($query, $fetchJoinCollection = true)));
        
        // FIELDS VISIBILITY FILTER
        for ($i=0; $i<count($data); $i++) {
            foreach ($data[$i] as $k => $v) {
                if (!in_array($k, $fields)) {
                    unset($data[$i][$k]);
                }
            }
        }
        
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
