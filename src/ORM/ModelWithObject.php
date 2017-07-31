<?php

namespace PhpBoot\ORM;

use Doctrine\Common\Cache\Cache;
use PhpBoot\DB\DB;

class ModelWithObject extends ModelWithClass
{
    /**
     * ModelWithObject constructor.
     * @param DB $db
     * @param object $entity
     * @param Cache $cache
     */
    public function __construct(DB $db, $entity, Cache $cache)
    {
        is_object($entity) or \PhpBoot\abort(new \InvalidArgumentException('object required'));
        parent::__construct($db, get_class($entity), $cache);
        $this->object = $entity;
    }


    public function create()
    {
        $data = [];
        foreach ($this->getColumns() as $column){
            if(isset($this->object->$column)){
                if(is_array($this->object->$column) || is_object($this->object->$column)){
                    $data[$column] = json_encode($this->object->$column);
                }else{
                    $data[$column] = $this->object->$column;
                }

            }
        }
        $id = $this->db
            ->insertInto($this->entity->getTable())
            ->values($data)
            ->exec()->lastInsertId();
        $this->object->{$this->entity->getPK()} = $id;
    }

    public function update()
    {
        $data = [];
        $pk = $this->entity->getPK();
        foreach ($this->getColumns() as $column){
            if($pk != $column && isset($this->object->$column)){
                if(is_array($this->object->$column) || is_object($this->object->$column)){
                    $data[$column] = json_encode($this->object->$column);
                }else{
                    $data[$column] = $this->object->$column;
                }
            }
        }

        $this->db
            ->update($this->entity->getTable())
            ->setArgs($data)
            ->where("`{$pk}` = ?", $this->object->$pk)
            ->exec();
    }

    /**
     * @var object
     */
    private $object;
}