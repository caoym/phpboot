<?php
namespace PhpBoot\Tests\Mocks;

class DBMock{
    public function __construct($test){
        $this->test = $test;
    }
    public function __destruct()
    {
        $this->test->assertTrue($this->prepared);
        $this->test->assertTrue($this->executed);
    }

    public function setAttribute($key, $value){

    }
    public function prepare($sql){
        $this->prepared = true;
        print ".............\n";
        print $this->expectedSql." , ";
        print_r($this->expectedParams);
        print $sql."\n";

        $this->test->assertEquals($this->expectedSql, $sql);
        return $this;
    }
    public function rowCount(){

    }
    public function execute($params){
        $this->executed = true;
        print_r($params);
        $this->test->assertEquals($this->expectedParams, $params);
    }
    public function fetchAll($arg){

    }
    public function lastInsertId()
    {

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

    private $executed = false;
    private $prepared = false;
}