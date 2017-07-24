<?php

namespace PhpBoot\ORM;


use PhpBoot\Entity\EntityContainer;

class ModelContainer extends EntityContainer
{

    /**
     * @param string $table
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return string
     */
    public function getPK()
    {
        return $this->pk;
    }

    /**
     * @param string $pk
     */
    public function setPK($pk)
    {
        $this->pk = $pk;
    }

    /**
     * @var string
     */
    private $table;

    /**
     * @var string
     */
    private $pk = 'id';
}