<?php
namespace PhpBoot\ORM;

use PhpBoot\DB\DB;

class ModelWithClass
{
    /**
     * Model constructor.
     * @param DB $db
     * @param string $entityName
     */
    public function __construct(DB $db, $entityName)
    {
        $this->db = $db;
        $builder = new ModelContainerBuilder();
        $this->entity = $builder->build($entityName);
    }

    /**
     * @param string $id
     * @return mixed|null
     */
    public function find($id)
    {
        $row = $this->db->select($this->getColumns())
            ->from($this->entity->getTable())
            ->where("`{$this->entity->getPK()}` = ?", $id)
            ->getFirst();
        if($row){
            return $this->entity->make($row, false);
        }else{
            return null;
        }
    }

    public function getColumns()
    {
        $columns = [];
        foreach ($this->entity->getProperties() as $p){
            $columns = $p->name;
        }
        return $columns;
    }
    /**
     * @var ModelContainer
     */
    protected $entity;
    /**
     * @var DB
     */
    protected $db;
}