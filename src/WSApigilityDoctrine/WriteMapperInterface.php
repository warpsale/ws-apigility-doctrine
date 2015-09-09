<?php

namespace WSApigilityDoctrine;

interface WriteMapperInterface
{
    /**
     * @param array|\Traversable|\stdClass $data 
     * @return Entity
     */
    public function create($data);
    
    /**
     * @param string $id
     * @return bool
    */
    public function delete($id);
    
    /**
     * @param string $id
     * @param array|\Traversable|\stdClass $data
     * @return Entity
     */
    public function update($id, $data);
}