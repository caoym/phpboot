<?php

namespace PhpBoot\ORM;

use PhpBoot\DB\DB;

class ModelWithObject extends ModelWithClass
{
    /**
     * ModelWithObject constructor.
     * @param DB $db
     * @param object $entity
     */
    public function __construct(DB $db, $entity)
    {
        is_object($entity) or \PhpBoot\abort(new \InvalidArgumentException('object required'));
        parent::__construct($db, get_class($entity));
        $this->object = $entity;
    }

    /**
     * @return mixed
     */
    public function create()
    {
        $data = [];
        foreach ($this->getColumns() as $column){
            if(isset($this->object->$column)){
                $data[$column] = $this->object->$column;
            }
        }
        return $this->db
            ->insertInto($this->entity->getTable())
            ->values($data)
            ->exec()->lastInsertId();
    }

    public function update()
    {
        $data = [];
        $pk = $this->entity->getPK();
        foreach ($this->getColumns() as $column){
            if($pk != $column && isset($this->object->$column)){
                $data[$column] = $this->object->$column;
            }
        }

        return $this->db
            ->update($this->entity->getTable())
            ->setArgs($data)
            ->where("`{$pk}` = ?", $this->object->$pk)
            ->exec()
            ->rows;
    }

    /**
     * @var object
     */
    private $object;
}