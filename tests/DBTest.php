<?php

namespace PhpBoot\Tests;

use PhpBoot\Application;
use PhpBoot\DB\DB;
use PhpBoot\DB\Raw;
use PhpBoot\DB\rules\basic\ScopedQuery;
use PhpBoot\Tests\Mocks\DBMock;


class DBTest extends TestCase
{
    /**
     * Tests SqlBuilder->select()
     */
    public function testSelect0()
    {
        $db = new DBMock($this);
        // SELECT c=1
        $db->setExpected('SELECT c=1');
        (new DB($this->app, $db))->select(DB::raw('c=1'))->get();
        
        $db->setExpected('SELECT *');
        (new DB($this->app,$db))->select()->get();
    }
    public function testSelect1()
    {
        $db = new DBMock($this);
        // SELECT col FROM tab
        $db->setExpected('SELECT `col` FROM `tab`');
        (new DB($this->app,$db))->select('col')->from('tab')->get();
    }
    public function testSelect2()
    {
        $db = new DBMock($this);
        // SELECT col FROM tab WHERE a=1 AND b=now() AND c='c' AND d IN (1,'2', now())
        $db->setExpected(
            'SELECT `col` FROM `tab` WHERE (a = ? AND b = now() AND c = ? AND d IN (?,?,now()) AND e BETWEEN ? AND now())',
            1, 'c', 1, '2','e1');
        //      where()
        (new DB($this->app, $db))->select('col')->from('tab')->where('a = ? AND b = ? AND c = ? AND d IN (?) AND e BETWEEN ? AND now()',
            1, DB::raw('now()'), 'c', [1,'2', DB::raw('now()')],'e1')->get();

        $db->setExpected(
            'SELECT `col` FROM `tab` WHERE (`a` = ? AND `b` = now() AND `c` = ? AND `d` IN (?,?,now()) AND `e` BETWEEN ? AND now())',
            1, 'c', 1, '2','e1');
        //      whereArgs()
        (new DB($this->app, $db))->select('col')->from('tab')->where([
            'a'=>['='=>1],
            'b'=>DB::raw('now()'), 
            'c'=>'c', 
            'd'=>['IN'=>[
                1,
                '2', 
                DB::raw('now()')
            ]],
            'e'=>['BETWEEN'=>[
                'e1',
                DB::raw('now()')
            ]]
        ])->get();
    }
    public function testSelect5()
    {
        $db = new DBMock($this);
        // SELECT col FROM tab LEFT JOIN tab1 ON tab.id=tab1.id
        $db->setExpected('SELECT `col` FROM `tab` LEFT JOIN `tab1` ON tab.id=tab1.id');
        (new DB($this->app, $db))->select('col')->from('tab')->leftJoin('tab1')->on('tab.id=tab1.id')->get();
    }
    public function testSelect6()
    {
        $db = new DBMock($this);
        // SELECT col FROM tab RIGHT JOIN tab1 ON tab.id=tab1.id
        $db->setExpected('SELECT `col` FROM `tab` RIGHT JOIN `tab1` ON tab.id=tab1.id');
        (new DB($this->app, $db))->select('col')->from('tab')->rightJoin('tab1')->on('tab.id=tab1.id')->get();
    }
    public function testSelect7()
    {
        $db = new DBMock($this);
        // SELECT col FROM tab INNER JOIN tab1 ON tab.id=tab1.id
        $db->setExpected('SELECT `col` FROM `tab` INNER JOIN `tab1` ON tab.id=tab1.id');
        (new DB($this->app, $db))->select('col')->from('tab')->innerJoin('tab1')->on('tab.id=tab1.id')->get();
    }
    public function testSelect8()
    {
        $db = new DBMock($this);
        // SELECT col FROM tab JOIN tab1 ON tab.id=tab1.id
        $db->setExpected('SELECT `col` FROM `tab` JOIN `tab1` ON tab.id=tab1.id');
        (new DB($this->app, $db))->select('col')->from('tab')->join('tab1')->on('tab.id=tab1.id')->get();
    }
    public function testSelect9()
    {
        $db = new DBMock($this);
        // SELECT col FROM tab JOIN tab1 ON tab.id=tab1.id WHERE col=1
        $db->setExpected('SELECT `col` FROM `tab` JOIN `tab1` ON tab.id=tab1.id WHERE (col=1)');
        (new DB($this->app, $db))->select('col')->from('tab')->join('tab1')->on('tab.id=tab1.id')->where('col=1')->get();
    }
    public function testSelect10()
    {
        $db = new DBMock($this);
        // SELECT * FROM tab GROUP BY col
        $db->setExpected('SELECT * FROM `tab` GROUP BY `col`');
        (new DB($this->app, $db))->select('*')->from('tab')->groupBy('col')->get();
    }
    public function testSelect11()
    {
        $db = new DBMock($this);
        // SELECT SUM(col) FROM tab GROUP BY col1 HAVING SUM(col)>0
        $db->setExpected('SELECT SUM(col) FROM `tab` GROUP BY `col1` HAVING (SUM(col)>?)', 0);
        (new DB($this->app, $db))->select(DB::raw('SUM(col)'))->from('tab')->groupBy('col1')->having('SUM(col)>?',0)->get();
    }
    public function testSelect12()
    {
        $db = new DBMock($this);
        // SELECT SUM(col) FROM tab WHERE col=1 GROUP BY col1 HAVING SUM(col)>0
        $db->setExpected('SELECT SUM(col) FROM `tab` WHERE (col=?) GROUP BY `col1` HAVING (SUM(col)>?)', 1,0);
        (new DB($this->app, $db))->select(DB::raw('SUM(col)'))->from('tab')->where('col=?',1)->groupBy('col1')->having('SUM(col)>?',0)->get();
        
    }
    public function testSelect13()
    {
        $db = new DBMock($this);
        // SELECT * FROM tab ORDER BY col
        $db->setExpected('SELECT * FROM `tab` ORDER BY `col`');
        (new DB($this->app, $db))->select('*')->from('tab')->orderBy('col')->get();
        (new DB($this->app, $db))->select('*')->from('tab')->orderBy(['col'])->get();
    }
    public function testSelect14()
    {
        $db = new DBMock($this);
        // SELECT * FROM tab ORDER BY col ASC
        $db->setExpected('SELECT * FROM `tab` ORDER BY `col` ASC');
        (new DB($this->app, $db))->select('*')->from('tab')->orderBy('col', DB::ORDER_BY_ASC)->get();
        (new DB($this->app, $db))->select('*')->from('tab')->orderBy(['col'=>DB::ORDER_BY_ASC])->get();
    }
    public function testSelect15()
    {
        $db = new DBMock($this);
        // SELECT * FROM tab ORDER BY col ASC, col1 DESC, col2
        $db->setExpected('SELECT * FROM `tab` ORDER BY `col` ASC,`col1` DESC,`col2`');
        (new DB($this->app, $db))->select('*')
            ->from('tab')
            ->orderBy('col', DB::ORDER_BY_ASC)
            ->orderBy('col1', DB::ORDER_BY_DESC)
            ->orderBy('col2')
            ->get();
        (new DB($this->app, $db))->select('*')
        ->from('tab')
        ->orderBy(['col'=>DB::ORDER_BY_ASC,
            'col1'=> DB::ORDER_BY_DESC,
            'col2'
        ]
        );
    }
    public function testSelect16()
    {
        $db = new DBMock($this);
        // SELECT * FROM tab LIMIT 0,1
        $db->setExpected('SELECT * FROM `tab` LIMIT 0,1');
        (new DB($this->app, $db))->select('*')->from('tab')->limit(0,1)->get();
    }
    public function testSelect17()
    {
        $db = new DBMock($this);
        // SELECT * FROM tab FOR UPDATE
        $db->setExpected('SELECT * FROM `tab` FOR UPDATE');
        (new DB($this->app, $db))->select('*')->from('tab')->forUpdate()->get();
    }
    public function testSelect18()
    {
        $db = new DBMock($this);
        // SELECT * FROM tab FOR UPDATE of col
        $db->setExpected('SELECT * FROM `tab` FOR UPDATE OF `col`');
        (new DB($this->app, $db))->select('*')->from('tab')->forUpdate()->of('col')->get();
    }
    public function testForInsert0()
    {
        $db = new DBMock($this);
        //INSERT INTO tab VALUES(1,2,3)
        $db->setExpected('INSERT INTO `tab` VALUES(?,?,?)', 1,2,3);
        (new DB($this->app, $db))->insertInto('tab')->values([1,2,3])->exec();
    }
    public function testForInsert1()
    {
        $db = new DBMock($this);
        //INSERT INTO tab VALUES(1,2,now())
        $db->setExpected('INSERT INTO `tab` VALUES(?,?,now())', 1,2);
        (new DB($this->app, $db))->insertInto('tab')->values([1, 2, DB::raw('now()')])->exec();
    }
    public function testForInsert2()
    {
        $db = new DBMock($this);
        //INSERT INTO tab(a,b,c)VALUES(1,2,now())
        $db->setExpected('INSERT INTO `tab`(`a`,`b`,`c`) VALUES(?,?,now())', 1,2);
        (new DB($this->app, $db))->insertInto('tab')->values(['a'=>1, 'b'=>2, 'c'=>DB::raw('now()')])->exec();
    }
    public function testForInsert3()
    {
        $db = new DBMock($this);
        //INSERT INTO tab(a,b,c)VALUES(1,2,now())
        $db->setExpected('INSERT INTO `tab`(`a`,`b`,`c`) VALUES(?,?,now()) ON DUPLICATE KEY UPDATE `a`=a+1', 1,2);

        (new DB($this->app, $db))->insertInto('tab')
            ->values(['a'=>1, 'b'=>2, 'c'=>DB::raw('now()')])
            ->onDuplicateKeyUpdate(['a'=>DB::raw('a+1')])
            ->exec();

        (new DB($this->app, $db))->insertInto('tab')
            ->values(['a'=>1, 'b'=>2, 'c'=>DB::raw('now()')])
            ->onDuplicateKeyUpdate('`a`=a+1')
            ->exec();
    }

    public function testUpdate0()
    {
        $db = new DBMock($this);
        //UPDATE `tab` SET a=1,b='2',c=now()
        $db->setExpected('UPDATE `tab` SET `a`=?,`b`=?,`c`=now()', 1,'2');
        (new DB($this->app, $db))->update('tab')->set(['a'=>1,'b'=>'2','c'=>DB::raw('now()')])->exec();
    }
    public function testUpdate1()
    {
        $db = new DBMock($this);
        //UPDATE `tab` SET a=1 WHERE b='2'
        $db->setExpected('UPDATE `tab` SET `a`=? WHERE (b=?)', 1,'2');
        (new DB($this->app, $db))->update('tab')->set(['a'=>1])->where('b=?',2)->exec();;
    }
    public function testUpdate2()
    {
        $db = new DBMock($this);
        //UPDATE `tab` SET a=1 WHERE b='2'
        $db->setExpected('UPDATE `tab` SET `a`=? WHERE (b=?)', 1,'2');
        (new DB($this->app, $db))->update('tab')->set(['a'=>1])->where('b=?',2)->exec();;
    }
    public function testUpdate3()
    {
        $db = new DBMock($this);
        //UPDATE `tab` SET a=1 WHERE b='2' ORDER BY c
        $db->setExpected('UPDATE `tab` SET `a`=? WHERE (b=?) ORDER BY `c`', 1,'2');
        (new DB($this->app, $db))->update('tab')->set(['a'=>1])->where('b=?',2)->orderBy('c')->exec();;
    }
    public function testUpdate4()
    {
        $db = new DBMock($this);
        //UPDATE `tab` SET a=1 WHERE b='2' ORDER BY c limit 1
        $db->setExpected('UPDATE `tab` SET `a`=? WHERE (b=?) ORDER BY `c` LIMIT 1', 1,'2');
        (new DB($this->app, $db))->update('tab')->set(['a'=>1])->where('b=?',2)->orderBy('c')->limit(1)->exec();
    }
    public function testDelete0(){
        $db = new DBMock($this);
        // DELETE FROM tab
        $db->setExpected('DELETE FROM `tab`');
        (new DB($this->app, $db))->deleteFrom('tab')->exec();
    }
    public function testDelete1(){
        $db = new DBMock($this);
        // DELETE FROM tab WHERE a=1
        $db->setExpected('DELETE FROM `tab` WHERE (a=?)',1);
        (new DB($this->app, $db))->deleteFrom('tab')->where('a=?',1)->exec();
    }
    public function testDelete2(){
        $db = new DBMock($this);
        // DELETE FROM tab WHERE a=1 ORDER BY b
        $db->setExpected('DELETE FROM `tab` WHERE (a=?) ORDER BY `b`',1);
        (new DB($this->app, $db))->deleteFrom('tab')->where('a=?',1)->orderBy('b')->exec();
    }
    public function testDelete3(){
        $db = new DBMock($this);
        // DELETE FROM tab WHERE a=1 ORDER BY b LIMIT 1
        $db->setExpected('DELETE FROM `tab` WHERE (a=?) ORDER BY `b` LIMIT 1',1);
        (new DB($this->app, $db))->deleteFrom('tab')->where('a=?',1)->orderBy('b')->limit(1)->exec();
    }
	 
    public function testForReplace0()
    {
        $db = new DBMock($this);
        //REPLACE INTO tab VALUES(1,2,3)
        $db->setExpected('REPLACE INTO `tab` VALUES(?,?,?)', 1,2,3);
        (new DB($this->app, $db))->replaceInto('tab')->values([1,2,3])->exec();
    }
    public function testForReplace1()
    {
        $db = new DBMock($this);
        //REPLACE INTO tab VALUES(1,2,now())
        $db->setExpected('REPLACE INTO `tab` VALUES(?,?,now())', 1,2);
        (new DB($this->app, $db))->replaceInto('tab')->values([1, 2, DB::raw('now()')])->exec();
    }

    public function testSelectWhere()
    {
        $db = new DBMock($this);
        $db->setExpected('SELECT * FROM `tab` WHERE (`a` = ?) AND (`b` = ?) OR (`c` = ?)', 1,2,3);
        (new DB($this->app, $db))
            ->select()
            ->from('tab')
            ->where(['a'=>1])
            ->where(['b'=>2])
            ->orWhere(['c'=>3])
            ->get();
    }

    public function testSelectSubWhere()
    {
        $db = new DBMock($this);
        $db->setExpected('SELECT * FROM `tab` WHERE ( (`a` = ?) ) AND ( (`b` = ?) AND (`c` = ?) OR (`d` = ?) )', 1,2,3,4);
        (new DB($this->app, $db))
            ->select()
            ->from('tab')
            ->where(function(ScopedQuery $query){
                $query->where(['a'=>1]);
            })
            ->where(function(ScopedQuery $query){
                $query->where(['b'=>2])
                    ->where(['c'=>3])
                    ->orWhere(['d'=>4]);
            })
            ->get();
    }

    public function testUpdateWhere()
    {
        $db = new DBMock($this);
        $db->setExpected('UPDATE `tab` SET `a`=? WHERE (`b` = ?) AND (`c` = ?) OR (`d` = ?)', 1, 2, 3, 4);
        (new DB($this->app, $db))
            ->update('tab')
            ->set(['a'=>1])
            ->where(['b'=>2])
            ->where(['c'=>3])
            ->orWhere(['d'=>4])
            ->exec();
    }

    public function testUpdateSubWhere()
    {
        $db = new DBMock($this);
        $db->setExpected('UPDATE `tab` SET `a`=? WHERE ( (`b` = ?) ) AND ( (`c` = ?) AND (`d` = ?) OR (`e` = ?) )', 1,2,3,4,5);
        (new DB($this->app, $db))
            ->update('tab')
            ->set(['a'=>1])
            ->where(function(ScopedQuery $query){
                $query->where(['b'=>2]);
            })
            ->where(function(ScopedQuery $query){
                $query->where(['c'=>3])
                    ->where(['d'=>4])
                    ->orWhere(['e'=>5]);
            })
            ->exec();
    }



    public function testSelectHanving()
    {
        $db = new DBMock($this);
        $db->setExpected('SELECT * FROM `tab` GROUP BY `g` HAVING (`a` = ?) AND (`b` = ?) OR (`c` = ?)', 1,2,3);
        (new DB($this->app, $db))
            ->select()
            ->from('tab')
            ->groupBy('g')
            ->having(['a'=>1])
            ->having(['b'=>2])
            ->orHaving(['c'=>3])
            ->get();
    }

    public function testSelectSubHanving()
    {
        $db = new DBMock($this);
        $db->setExpected('SELECT * FROM `tab` GROUP BY `g` HAVING (`a` = ?) AND ( (`b` = ?) AND (`c` = ?) OR (`d` = ?) )', 1,2,3,4);
        (new DB($this->app, $db))
            ->select()
            ->from('tab')
            ->groupBy('g')
            ->having(['a'=>1])
            ->having(function(ScopedQuery $query){
                $query->where(['b'=>2])
                    ->where(['c'=>3])
                    ->orWhere(['d'=>4]);
            })
            ->get();
    }

    public function testWrap()
    {
        self::assertEquals('`abc`', DB::wrap('abc'));
        self::assertEquals('abc', DB::wrap(DB::raw('abc')));
        self::assertEquals('abc.123', DB::wrap('abc.123'));
        self::assertEquals('`abc.123 456`', DB::wrap('abc.123 456'));
    }
    public function testSubQueryWithFrom()
    {
        $mock = new DBMock($this);
        $mock->setExpected('SELECT * FROM (SELECT * FROM `tab` WHERE (`a` = ?))', 1);
        $db = new DB($this->app, $mock);
        $db->select()
            ->from($db->select()->from('tab')->where(['a'=>1]))
            ->get();
    }

    public function testSubQueryWithWhereIn1()
    {
        $mock = new DBMock($this);
        $mock->setExpected('SELECT * FROM `tab` WHERE (`a` = ? AND `b` IN (SELECT * FROM `tab` WHERE (`a` = ?)) AND `c` = ?)', 1, 2, 3);
        $db = new DB($this->app, $mock);
        $db->select()
            ->from('tab')
            ->where([
                'a'=>1,
                'b'=>['IN'=>$db->select()->from('tab')->where(['a'=>2])],
                'c'=>3
            ])
            ->get();

    }

    public function testSubQueryWithWhereIn2()
    {
        $mock = new DBMock($this);
        $mock->setExpected('SELECT * FROM `tab` WHERE (b IN (SELECT * FROM `tab` WHERE (`a` = ?)))', 1);
        $db = new DB($this->app, $mock);
        $db->select()
            ->from('tab')
            ->where('b IN ?', $db->select()->from('tab')->where(['a'=>1]))
            ->get();

    }

    public function testSubQueryWithBetween()
    {
        $mock = new DBMock($this);
        $mock->setExpected('SELECT * FROM `tab` WHERE (`a` BETWEEN (SELECT * FROM `tab` WHERE (`b` = ?)) AND (SELECT * FROM `tab` WHERE (`c` = ?)))', 1, 2);
        $db = new DB($this->app, $mock);
        $db->select()
            ->from('tab')
            ->where(['a'=>['BETWEEN'=>[
                $db->select()->from('tab')->where(['b'=>1]),
                $db->select()->from('tab')->where(['c'=>2])]
            ]])
            ->get();

    }
    public function testInsertWithBatchValues()
    {
        $mock = new DBMock($this);
        $mock->setExpected("INSERT INTO `tab` VALUES(false,1,'2\\'\\\\',3.1,'',now())");
        $db = new DB($this->app, $mock);
        $db->insertInto('tab')
            ->batchValues([[
                false, 1, "2'\\", 3.1, null, DB::raw('now()'),
            ]])
            ->exec();
    }
    public function testInsertWithBatchValues2()
    {
        $mock = new DBMock($this);
        $mock->setExpected("INSERT INTO `tab`(`a`,`b`,`c`,`d`,`e`,`f`) VALUES(false,1,'2\\'\\\\',3.1,'',now())");
        $db = new DB($this->app, $mock);
        $db->insertInto('tab')
            ->batchValues([[
                'a'=>false, 'b'=>1, 'c'=>"2'\\", 'd'=>3.1, 'e'=>null, 'f'=>DB::raw('now()')
            ]])
            ->exec();
    }
    public function testInsertWithBatchValues3()
    {
        $mock = new DBMock($this);
        $mock->setExpected("INSERT INTO `tab` VALUES(false,1,'2\\'\\\\',3.1,'',now()), (true,2,'3\\'\\\\',4.1,'',now())");
        $db = new DB($this->app, $mock);
        $db->insertInto('tab')
            ->batchValues([
                [false, 1, "2'\\", 3.1, null, DB::raw('now()')],
                [true, 2, "3'\\", 4.1, null, DB::raw('now()')],
            ])
            ->exec();
    }

    /**
     * 
     * @var DBMock
     */
    private $db;


}

