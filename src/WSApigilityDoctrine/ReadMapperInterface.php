<?php

namespace WSApigilityDoctrine;

interface ReadMapperInterface
{
    /**
     * @param string $id 
     * @return Entity
     */
    public function fetch($id);

    /**
     * @return Collection
     */
    public function fetchAll($params = array());
}