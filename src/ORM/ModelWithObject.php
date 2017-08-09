<?php

namespace PhpBoot\ORM;

use Doctrine\Common\Cache\Cache;
use PhpBoot\DB\DB;

class ModelWithObject
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
        $entityName = get_class($entity);
        $this->db = $db;
        $builder = $db->getApp()->get(ModelContainerBuilder::class);
        $this->entity = $builder->build($entityName);
        $this->object = $entity;
    }
    /**
     * @return void
     */
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
        $id = $this->db->insertInto($this->entity->getTable())
            ->values($data)
            ->exec()->lastInsertId();
        $this->object->{$this->entity->getPK()} = $id;
    }

    /**
     * @param array $columns columns to update. if columns is empty array, update all of the columns
     * @return int rows updated
     */
    public function update(array $columns=[])
    {
        $data = [];
        $pk = $this->entity->getPK();
        foreach ($this->getColumns() as $column){
            if(count($columns) && !in_array($column, $columns)){
                continue;
            }
            if($pk != $column && isset($this->object->$column)){
                if(is_array($this->object->$column) || is_object($this->object->$column)){
                    $data[$column] = json_encode($this->object->$column);
                }else{
                    $data[$column] = $this->object->$column;
                }
            }
        }

        return $this->db->update($this->entity->getTable())
            ->set($data)
            ->where("`{$pk}` = ?", $this->object->$pk)
            ->exec()->rows;
    }

    /**
     * @return int rows deleted
     */
    public function delete()
    {
        $pk = $this->entity->getPK();
        return $this->db->deleteFrom($this->entity->getTable())
            ->where([$pk => $this->object->$pk])
            ->exec()->rows;
    }

    protected function getColumns()
    {
        $columns = [];
        foreach ($this->entity->getProperties() as $p){
            $columns[] = $p->name;
        }
        return $columns;
    }

    /**
     * @var object
     */
    protected $object;

    /**
     * @var ModelContainer
     */
    protected $entity;
    /**
     * @var DB
     */
    protected $db;
}