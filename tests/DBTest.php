<?php

namespace PhpBoot\Tests;

use PhpBoot\Application;
use PhpBoot\DB\DB;

class DBMock{
    public function __construct($test){
        $this->test = $test;
    }
    public function setAttribute($key, $value){
        
    }
    public function prepare($sql){
        
        print ".............\n";
        print $this->expectedSql."\n";
        print $sql."\n";
        
        $this->test->assertEquals($this->expectedSql, $sql);
        return $this;
    }
	public function rowCount(){
        
    }
    public function execute($params){
        $this->test->assertEquals($this->expectedParams, $params);
    }
    public function fetchAll($arg){
        
    }
    public function setExpected($sql, $_=null){
        $this->expectedSql = $sql;
        $this->expectedParams = array_slice(func_get_args(), 1);
    }
    private  $expectedSql;
    private  $expectedParams;
    /**
     * 
     * @var \PHPUnit_Framework_TestCase
     */
    private $test;
}

class DBTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->db = new DBMock($this);
    }
    
    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->db = null;
        parent::tearDown();
    }
    /**
     * Tests SqlBuilder->select()
     */
    public function testSelect0()
    {
        // SELECT c=1
        $this->db->setExpected('SELECT c=1');
        (new DB($this->app, $this->db))->select('c=1')->get();
        
        $this->db->setExpected('SELECT *');
        (new DB($this->app,$this->db))->select()->get();
    }
    public function testSelect1()
    {
        // SELECT col FROM tab
        $this->db->setExpected('SELECT `col` FROM `tab`');
        (new DB($this->app,$this->db))->select('col')->from('tab')->get();
    }
    public function testSelect2()
    {
        // SELECT col FROM tab WHERE a=1 AND b=now() AND c='c' AND d IN (1,'2', now())
        $this->db->setExpected(
            'SELECT `col` FROM `tab` WHERE a = ? AND b = now() AND c = ? AND d IN (?,?,now()) AND e BETWEEN ? AND now()',
            1, 'c', 1, '2','e1');
        //      where()
        (new DB($this->app, $this->db))->select('col')->from('tab')->where('a = ? AND b = ? AND c = ? AND d IN (?) AND e BETWEEN ? AND now()',
            1, DB::raw('now()'), 'c', [1,'2', DB::raw('now()')],'e1')->get();
        //      whereArgs()
        (new DB($this->app, $this->db))->select('col')->from('tab')->where([
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
        // SELECT col FROM tab LEFT JOIN tab1 ON tab.id=tab1.id
        $this->db->setExpected('SELECT `col` FROM `tab` LEFT JOIN tab1 ON tab.id=tab1.id');
        (new DB($this->app, $this->db))->select('col')->from('tab')->leftJoin('tab1')->on('tab.id=tab1.id')->get();
    }
    public function testSelect6()
    {
        // SELECT col FROM tab RIGHT JOIN tab1 ON tab.id=tab1.id
        $this->db->setExpected('SELECT `col` FROM `tab` RIGHT JOIN tab1 ON tab.id=tab1.id');
        (new DB($this->app, $this->db))->select('col')->from('tab')->rightJoin('tab1')->on('tab.id=tab1.id')->get();
    }
    public function testSelect7()
    {
        // SELECT col FROM tab INNER JOIN tab1 ON tab.id=tab1.id
        $this->db->setExpected('SELECT `col` FROM `tab` INNER JOIN tab1 ON tab.id=tab1.id');
        (new DB($this->app, $this->db))->select('col')->from('tab')->innerJoin('tab1')->on('tab.id=tab1.id')->get();
    }
    public function testSelect8()
    {
        // SELECT col FROM tab JOIN tab1 ON tab.id=tab1.id
        $this->db->setExpected('SELECT `col` FROM `tab` JOIN tab1 ON tab.id=tab1.id');
        (new DB($this->app, $this->db))->select('col')->from('tab')->join('tab1')->on('tab.id=tab1.id')->get();
    }
    public function testSelect9()
    {
        // SELECT col FROM tab JOIN tab1 ON tab.id=tab1.id WHERE col=1
        $this->db->setExpected('SELECT `col` FROM `tab` JOIN tab1 ON tab.id=tab1.id WHERE col=1');
        (new DB($this->app, $this->db))->select('col')->from('tab')->join('tab1')->on('tab.id=tab1.id')->where('col=1')->get();
    }
    public function testSelect10()
    {
        // SELECT * FROM tab GROUP BY col
        $this->db->setExpected('SELECT * FROM `tab` GROUP BY col');
        (new DB($this->app, $this->db))->select('*')->from('tab')->groupBy('col')->get();
    }
    public function testSelect11()
    {
        // SELECT SUM(col) FROM tab GROUP BY col1 HAVING SUM(col)>0
        $this->db->setExpected('SELECT SUM(col) FROM `tab` GROUP BY col1 HAVING SUM(col)>?', 0);
        (new DB($this->app, $this->db))->select('SUM(col)')->from('tab')->groupBy('col1')->having('SUM(col)>?',0)->get();
    }
    public function testSelect12()
    {     
        // SELECT SUM(col) FROM tab WHERE col=1 GROUP BY col1 HAVING SUM(col)>0
        $this->db->setExpected('SELECT SUM(col) FROM `tab` WHERE col=? GROUP BY col1 HAVING SUM(col)>?', 1,0);
        (new DB($this->app, $this->db))->select('SUM(col)')->from('tab')->where('col=?',1)->groupBy('col1')->having('SUM(col)>?',0)->get();
        
    }
    public function testSelect13()
    {
        // SELECT * FROM tab ORDER BY col
        $this->db->setExpected('SELECT * FROM `tab` ORDER BY col');
        (new DB($this->app, $this->db))->select('*')->from('tab')->orderBy('col')->get();
        (new DB($this->app, $this->db))->select('*')->from('tab')->orderByArgs(['col'])->get();
    }
    public function testSelect14()
    {
        // SELECT * FROM tab ORDER BY col ASC
        $this->db->setExpected('SELECT * FROM `tab` ORDER BY col ASC');
        (new DB($this->app, $this->db))->select('*')->from('tab')->orderBy('col', DB::ORDER_BY_ASC)->get();
        (new DB($this->app, $this->db))->select('*')->from('tab')->orderByArgs(['col'=>DB::ORDER_BY_ASC])->get();
    }
    public function testSelect15()
    {
        // SELECT * FROM tab ORDER BY col ASC, col1 DESC, col2
        $this->db->setExpected('SELECT * FROM `tab` ORDER BY col ASC,col1 DESC,col2');
        (new DB($this->app, $this->db))->select('*')
            ->from('tab')
            ->orderBy('col', DB::ORDER_BY_ASC)
            ->orderBy('col1', DB::ORDER_BY_DESC)
            ->orderBy('col2')
            ->get();
        (new DB($this->app, $this->db))->select('*')
        ->from('tab')
        ->orderByArgs(['col'=>DB::ORDER_BY_ASC,
            'col1'=> DB::ORDER_BY_DESC,
            'col2'
        ]
        );
    }
    public function testSelect16()
    {
        // SELECT * FROM tab LIMIT 0,1
        $this->db->setExpected('SELECT * FROM `tab` LIMIT 0,1');
        (new DB($this->app, $this->db))->select('*')->from('tab')->limit(0,1)->get();
    }
    public function testSelect17()
    {
        // SELECT * FROM tab FOR UPDATE
        $this->db->setExpected('SELECT * FROM `tab` FOR UPDATE');
        (new DB($this->app, $this->db))->select('*')->from('tab')->forUpdate()->get();
    }
    public function testSelect18()
    {
        // SELECT * FROM tab FOR UPDATE of col
        $this->db->setExpected('SELECT * FROM `tab` FOR UPDATE OF col');
        (new DB($this->app, $this->db))->select('*')->from('tab')->forUpdate()->of('col')->get();
    }
    public function testForInsert0()
    {
        //INSERT INTO tab VALUES(1,2,3)
        $this->db->setExpected('INSERT INTO `tab` VALUES(?,?,?)', 1,2,3);
        (new DB($this->app, $this->db))->insertInto('tab')->values([1,2,3])->exec();
    }
    public function testForInsert1()
    {
        //INSERT INTO tab VALUES(1,2,now())
        $this->db->setExpected('INSERT INTO `tab` VALUES(?,?,now())', 1,2);
        (new DB($this->app, $this->db))->insertInto('tab')->values([1, 2, DB::raw('now()')])->exec();
    }
    public function testForInsert2()
    {
        //INSERT INTO tab(a,b,c)VALUES(1,2,now())
        $this->db->setExpected('INSERT INTO `tab`(a,b,c) VALUES(?,?,now())', 1,2);
        (new DB($this->app, $this->db))->insertInto('tab')->values(['a'=>1, 'b'=>2, 'c'=>DB::raw('now()')])->exec();
    }
    public function testForInsert3()
    {
        //INSERT INTO tab(a,b,c)VALUES(1,2,now())
        $this->db->setExpected('INSERT INTO `tab`(a,b,c) VALUES(?,?,now()) ON DUPLICATE KEY UPDATE a=a+1', 1,2);
        (new DB($this->app, $this->db))->insertInto('tab')
            ->values(['a'=>1, 'b'=>2, 'c'=>DB::raw('now()')])
            ->onDuplicateKeyUpdate('a',DB::raw('a+1'))
            ->exec();

        (new DB($this->app, $this->db))->insertInto('tab')
            ->values(['a'=>1, 'b'=>2, 'c'=>DB::raw('now()')])
            ->onDuplicateKeyUpdateArgs(['a'=>DB::raw('a+1')])
            ->exec();

        (new DB($this->app, $this->db))->insertInto('tab')
            ->values(['a'=>1, 'b'=>2, 'c'=>DB::raw('now()')])
            ->onDuplicateKeyUpdateExpr('a=a+1')
            ->exec();
    }

    public function testUpdate0()
    {
        //UPDATE `tab` SET a=1,b='2',c=now()
        $this->db->setExpected('UPDATE `tab` SET a=?,b=?,c=now()', 1,'2');
        (new DB($this->app, $this->db))->update('tab')->setArgs(['a'=>1,'b'=>'2','c'=>DB::raw('now()')])->exec();
        (new DB($this->app, $this->db))->update('tab')->set('a',1)->set('b', '2')->set('c', DB::raw('now()'))->exec();
        (new DB($this->app, $this->db))->update('tab')->set('a',1)->setArgs(['b'=>'2','c'=>DB::raw('now()')])->exec();
        (new DB($this->app, $this->db))->update('tab')->setArgs(['a'=>1,'b'=>'2'])->set('c',DB::raw('now()'))->exec();;
    }
    public function testUpdate1()
    {
        //UPDATE `tab` SET a=1 WHERE b='2'
        $this->db->setExpected('UPDATE `tab` SET a=? WHERE b=?', 1,'2');
        (new DB($this->app, $this->db))->update('tab')->set('a', 1)->where('b=?',2)->exec();;
    }
    public function testUpdate2()
    {
        //UPDATE `tab` SET a=1 WHERE b='2'
        $this->db->setExpected('UPDATE `tab` SET a=? WHERE b=?', 1,'2');
        (new DB($this->app, $this->db))->update('tab')->set('a', 1)->where('b=?',2)->exec();;
    }
    public function testUpdate3()
    {
        //UPDATE `tab` SET a=1 WHERE b='2' ORDER BY c
        $this->db->setExpected('UPDATE `tab` SET a=? WHERE b=? ORDER BY c', 1,'2');
        (new DB($this->app, $this->db))->update('tab')->set('a', 1)->where('b=?',2)->orderBy('c')->exec();;
    }
    public function testUpdate4()
    {
        //UPDATE `tab` SET a=1 WHERE b='2' ORDER BY c limit 1
        $this->db->setExpected('UPDATE `tab` SET a=? WHERE b=? ORDER BY c LIMIT 1', 1,'2');
        (new DB($this->app, $this->db))->update('tab')->set('a', 1)->where('b=?',2)->orderBy('c')->limit(1)->exec();
    }
    public function testDelete0(){
        // DELETE FROM tab
        $this->db->setExpected('DELETE FROM `tab`');
        (new DB($this->app, $this->db))->deleteFrom('tab')->exec();
    }
    public function testDelete1(){
        // DELETE FROM tab WHERE a=1
        $this->db->setExpected('DELETE FROM `tab` WHERE a=?',1);
        (new DB($this->app, $this->db))->deleteFrom('tab')->where('a=?',1)->exec();
    }
    public function testDelete2(){
        // DELETE FROM tab WHERE a=1 ORDER BY b
        $this->db->setExpected('DELETE FROM `tab` WHERE a=? ORDER BY b',1);
        (new DB($this->app, $this->db))->deleteFrom('tab')->where('a=?',1)->orderBy('b')->exec();
    }
    public function testDelete3(){
        // DELETE FROM tab WHERE a=1 ORDER BY b LIMIT 1
        $this->db->setExpected('DELETE FROM `tab` WHERE a=? ORDER BY b LIMIT 1',1);
        (new DB($this->app, $this->db))->deleteFrom('tab')->where('a=?',1)->orderBy('b')->limit(1)->exec();
    }
	 
    public function testForReplace0()
    {
        //REPLACE INTO tab VALUES(1,2,3)
        $this->db->setExpected('REPLACE INTO `tab` VALUES(?,?,?)', 1,2,3);
        (new DB($this->app, $this->db))->replaceInto('tab')->values([1,2,3])->exec();
    }
    public function testForReplace1()
    {
        //REPLACE INTO tab VALUES(1,2,now())
        $this->db->setExpected('REPLACE INTO `tab` VALUES(?,?,now())', 1,2);
        (new DB($this->app, $this->db))->replaceInto('tab')->values([1, 2, DB::raw('now()')])->exec();
    }
    /**
     * 
     * @var DBMock
     */
    private $db;


}

