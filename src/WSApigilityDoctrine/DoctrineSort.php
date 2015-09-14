<?php
namespace WSApigilityDoctrine;

class DoctrineSort 
{
    public static function sort($qb, $params) 
    {
        $sort = isset($params['sort']) ? $params['sort'] : NULL;
        if ($sort) {
            $items = explode(',', $sort);
            for ($i=0; $i<count($items); $i++) {
                $order = strpos($items[$i], '-') === FALSE ? 'ASC' : 'DESC';
                $field = (strpos($items[$i], '.') === FALSE ? 'e.' : '') . str_replace(['-', '+'], ['', ''], trim($items[$i]));
                if ($i == 0) {
                    $qb->orderBy($field, $order);
                } else {
                    $qb->addOrderBy($field, $order);
                }
            }
        }
    }
}