<?php

/**
 * $Id: HttpRouterEntriesTest.php 58710 2015-01-14 16:08:49Z caoyangmin $
 * @author caoyangmin(caoyangmin@gmail.com)
 * @brief
 */
require_once __DIR__.'/../../lib/phprs/AutoLoad.php';

class HttpRouterEntriesTest extends PHPUnit_Framework_TestCase {
	
	public function testAll(){
		$t = new \phprs\util\HttpRouterEntries();
		
		//$this->assertFalse($t->insert(array(),'the /'));
		//$this->assertEquals($t->find(array()),null);
		
		$this->assertTrue($t->insert('/','/'));
		$this->assertEquals($t->find('/'), '/');
		$this->assertFalse($t->insert('/','/'));
		$this->assertFalse($t->insert('/','the /2'));
		$this->assertEquals($t->find('/'), '/');
		
		$this->assertTrue($t->insert('/a/b/c/d/?x=1&y=2','/a/b/c/d/?x=1&y=2'));
		$this->assertFalse($t->insert('/a/b/c/d/?y=2&x=1','/a/b/c/d/?x=1&y=2'));
		$this->assertTrue($t->insert('/a/b/c/d/?x=1&y=2&z=3','/a/b/c/d/?x=1&y=2&z=3'));
		$this->assertTrue($t->insert('/a/b','/a/b'));
		
		$this->assertEquals($t->find('a'), '/');
		$this->assertEquals($t->find('a/b'), '/a/b');
		$this->assertEquals($t->find('/a/b/?x=1&y=2'), '/a/b');
		$this->assertEquals($t->find('/a/b/'), '/a/b');
		$this->assertEquals($t->find('/a/b/c'), '/a/b');
		$this->assertEquals($t->find('/a/b/c/d'), '/a/b');
		$this->assertEquals($t->find('/a/b/c/d/?x=1&y=2'), '/a/b/c/d/?x=1&y=2');
		$this->assertEquals($t->find('/a/b/c/d/?y=2&x=1'), '/a/b/c/d/?x=1&y=2');
		$this->assertEquals($t->find('/a/b/c/d/?a=1&y=2&x=1'), '/a/b/c/d/?x=1&y=2');
		$this->assertEquals($t->find('/a/b/c/d/?x=1&y=2&z=3'), '/a/b/c/d/?x=1&y=2&z=3');
		$this->assertEquals($t->find('/a/b/c/d/?x=1'), '/a/b');
		$this->assertEquals($t->find('/a/b/c/d/e/?x=1&y=2'), '/a/b');
		
		$this->assertTrue($t->insert('/m/*','/m/*'));
		$this->assertTrue($t->insert('/m','/m'));
		$this->assertTrue($t->insert('/n?*','/n?*'));
		$this->assertTrue($t->insert('/n/*','/n/*'));
		
		$this->assertEquals($t->find('/m/a'), '/m/*');
		$this->assertEquals($t->find('/m/a/b'), '/m/*');
		
		$this->assertEquals($t->find('/n?a=1'),('/n?*'));
		$this->assertEquals($t->find('/n/a'),('/n/*'));
		
		
		$this->assertTrue($t->insert('/x/','/x/',true));
		$this->assertTrue($t->insert('/x/*','/x/*'));
		
		$this->assertEquals($t->find('/x/y'),'/x/*');
		$this->assertEquals($t->find('/x'), '/x/');
		$this->assertEquals($t->find('/x?a=1'), '/x/');
		
		
	}
}

?>