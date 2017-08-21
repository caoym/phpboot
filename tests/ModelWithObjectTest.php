<?php

namespace PhpBoot\Tests;

use PhpBoot\DB\DB;
use PhpBoot\Tests\Mocks\DBMock;

/**
 * @table test_table
 */
class ModelWithObjectForTest
{
    public $id;
    public $name;
    public $type;
}

class ModelWithObjectTest extends TestCase
{
    public function testUpdate()
    {
        $obj = new ModelWithObjectForTest();
        $obj->id = 1;
        $obj->name = 'abc';
        $obj->type = '123';

        $mock = new DBMock($this);
        $mock->setExpected('UPDATE `test_table` SET `name`=?,`type`=? WHERE (`id` = ?)', 'abc', '123', 1);
        $db = new DB($this->app, $mock);
        \PhpBoot\model($db, $obj)->update();
    }

    public function testUpdateWithColumn()
    {
        $obj = new ModelWithObjectForTest();
        $obj->id = 1;
        $obj->name = 'abc';
        $obj->type = '123';

        $mock = new DBMock($this);
        $mock->setExpected('UPDATE `test_table` SET `name`=? WHERE (`id` = ?)', 'abc', 1);
        $db = new DB($this->app, $mock);
        \PhpBoot\model($db, $obj)->update(['name']);
    }

    public function testCreate()
    {
        $obj = new ModelWithObjectForTest();
        $obj->id = 1;
        $obj->name = 'abc';
        $obj->type = '123';

        $mock = new DBMock($this);
        $mock->setExpected('INSERT INTO `test_table`(`id`,`name`,`type`) VALUES(?,?,?)',1, 'abc', '123');
        $db = new DB($this->app, $mock);
        \PhpBoot\model($db, $obj)->create();
    }

    public function testDelete()
    {
        $obj = new ModelWithObjectForTest();
        $obj->id = 1;
        $obj->name = 'abc';
        $obj->type = '123';

        $mock = new DBMock($this);
        $mock->setExpected('DELETE FROM `test_table` WHERE (`id` = ?)',1);
        $db = new DB($this->app, $mock);
        \PhpBoot\model($db, $obj)->delete();
    }

}