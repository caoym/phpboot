<?php
/**
 * $Id: DB.php 131 2015-10-10 02:25:57Z yangmin.cao $
 * @author caoym(caoyangmin@gmail.com)
 */
namespace phprs\ezsql;
/**
 * 
 * @author caoym
 *
 */
class DB extends \PDO
{
    public function __construct(
        $dsn, 
        $username, 
        $passwd, 
        $options = [\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES'utf8';",\PDO::MYSQL_ATTR_FOUND_ROWS => true]){
        parent::__construct($dsn, $username, $passwd, $options);
        $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }
}
