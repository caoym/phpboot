<?php
/***************************************************************************
 *
 * Copyright (c) 2013 . All Rights Reserved
 *
 **************************************************************************/
/**
 * $Id: TreeTest.php 57067 2014-12-15 05:39:13Z caoyangmin $
 * @author caoyangmin(caoyangmin@gmail.com)
 * @brief
 */

require_once __DIR__.'/../../../lib/caoym/Autoload.php';

class TreeTest extends PHPUnit_Framework_TestCase {
	
	public function testAll(){
		$t = new \caoym\util\Tree();
		
		//$this->assertFalse($t->insert(array(),'the /'));
		//$this->assertEquals($t->find(array()),null);
		
		$this->assertTrue($t->insert(array('/'),'the /'));
		$this->assertEquals($t->find(array('/')), 'the /');
		$this->assertFalse($t->insert(array('/'),'the /'));
		$this->assertTrue($t->insert(array('/'),'the /2',true));
		$this->assertEquals($t->find(array('/')), 'the /2');
		$this->assertTrue($t->erase(array('/')));
		$this->assertEquals($t->find(array('/')),null);
		$this->assertFalse($t->erase(array('/')));
		
		$this->assertTrue($t->insert(array('a','b','c','d'),'the d'));
		$this->assertEquals($t->find(array('a','b','c','d')), 'the d');
		$this->assertFalse($t->insert(array('a','b','c','d'),'the d'));
		$this->assertTrue($t->insert(array('a','b','c','d'),'the d2',true));
		$this->assertEquals($t->find(array('a','b','c','d')), 'the d2');
		$this->assertEquals($t->find(array('a')), null);
		$this->assertEquals($t->find(array('a','b')), null);
		$this->assertEquals($t->find(array('a','b','c')),null);
		
		$this->assertTrue($t->insert(array('a','b'),'the b'));
		$this->assertEquals($t->find(array('a','b')), 'the b');
		$this->assertFalse($t->insert(array('a','b'),'the b'));
		$this->assertTrue($t->insert(array('a','b'),'the b2',true));
		$this->assertEquals($t->find(array('a','b')), 'the b2');
		
		$this->assertTrue($t->insert(array('a','b','c'),'the c'));
		$this->assertEquals($t->find(array('a','b','c')), 'the c');
		$this->assertFalse($t->insert(array('a','b','c'),'the c'));
		$this->assertTrue($t->insert(array('a','b','c'),'the c2',true));
		$this->assertEquals($t->find(array('a','b','c')), 'the c2');
		
		$this->assertTrue($t->insert(array('a'),'the a'));
		$this->assertEquals($t->find(array('a')), 'the a');
		$this->assertFalse($t->insert(array('a'),'the a'));
		$this->assertTrue($t->insert(array('a'),'the a2',true));
		$this->assertEquals($t->find(array('a')), 'the a2');
		
		$this->assertTrue($t->erase(array('a','b','c')));
		$this->assertEquals($t->find(array('a')), 'the a2');
		$this->assertEquals($t->find(array('a','b')), 'the b2');
		$this->assertEquals($t->find(array('a','b','c')),null);
		$this->assertEquals($t->find(array('a','b','c','d')),null);
		
		$this->assertTrue($t->erase(array('a','b')));
		$this->assertEquals($t->find(array('a')), 'the a2');
		$this->assertEquals($t->find(array('a','b')), null);
		$this->assertEquals($t->find(array('a','b','c')),null);
		$this->assertEquals($t->find(array('a','b','c','d')),null);
		
		$this->assertTrue($t->erase(array('a')));
		$this->assertEquals($t->find(array('a')), null);
		$this->assertEquals($t->find(array('a','b')), null);
		$this->assertEquals($t->find(array('a','b','c')),null);
		$this->assertEquals($t->find(array('a','b','c','d')),null);
		
		$this->assertTrue($t->insert(array('*'),'*'));
		$this->assertTrue($t->insert(array('*','*'),'*/*'));
		$this->assertTrue($t->insert(array('*','b'),'*/b'));
		$this->assertEquals($t->find(array('a')), '*');
		$this->assertEquals($t->find(array('a','a')), '*/*');
		$this->assertEquals($t->find(array('a','b')), '*/b');
	}
}
