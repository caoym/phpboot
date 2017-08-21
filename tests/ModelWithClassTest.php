<?php

namespace PhpBoot\Tests;
use PhpBoot\DB\DB;
use PhpBoot\Tests\Mocks\DBMock;

/**
 * @table test_table
 */
class ModelWithClassForTest
{
    public $id;
    public $name;
    public $type;
}

class ModelWithClassTest extends TestCase
{

    public function testUpdate()
    {
        $mock = new DBMock($this);
        $mock->setExpected('UPDATE `test_table` SET `name`=? WHERE (`id` = ?)', 'abc', 1);
        $db = new DB($this->app, $mock);
        \PhpBoot\models($db, ModelWithObjectForTest::class)->update(1, ['name'=>'abc']);
    }

    public function testUpdateWhere()
    {
        $mock = new DBMock($this);
        $mock->setExpected('UPDATE `test_table` SET `name`=? WHERE (`id` = ?)', 'abc', 1);
        $db = new DB($this->app, $mock);
        \PhpBoot\models($db, ModelWithObjectForTest::class)->updateWhere(
            ['name'=>'abc'],
            ['id'=>1]
        )->exec();
    }

    public function testFind()
    {
        $mock = new DBMock($this);
        $mock->setExpected('SELECT `id`,`name`,`type` FROM `test_table` WHERE (`id` = ?)', 1);
        $db = new DB($this->app, $mock);
        \PhpBoot\models($db, ModelWithObjectForTest::class)->find(1);
    }

    public function testFindWhere()
    {
        $mock = new DBMock($this);
        $mock->setExpected('SELECT `id`,`name`,`type` FROM `test_table` WHERE (`name` = ?)', 'abc');
        $db = new DB($this->app, $mock);
        \PhpBoot\models($db, ModelWithObjectForTest::class)->findWhere(['name'=>'abc'])->get();
    }

    public function testFindEmptyWhere()
    {
        $mock = new DBMock($this);
        $mock->setExpected('SELECT `id`,`name`,`type` FROM `test_table`');
        $db = new DB($this->app, $mock);
        \PhpBoot\models($db, ModelWithObjectForTest::class)->findWhere()->get();
    }

    public function testDelete()
    {
        $mock = new DBMock($this);
        $mock->setExpected('DELETE FROM `test_table` WHERE (`id` = ?) LIMIT 1', 1);
        $db = new DB($this->app, $mock);
        \PhpBoot\models($db, ModelWithObjectForTest::class)->delete(1);
    }

    public function testDeleteWhere()
    {
        $mock = new DBMock($this);
        $mock->setExpected('DELETE FROM `test_table` WHERE (`name` = ?)', 'abc');
        $db = new DB($this->app, $mock);
        \PhpBoot\models($db, ModelWithObjectForTest::class)->deleteWhere(['name'=>'abc'])->exec();
    }

}