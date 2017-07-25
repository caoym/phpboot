<?php

namespace PhpBoot\DB\impls;
use PhpBoot\DB\NestedStringCut;
use PhpBoot\DB\Raw;
use PhpBoot\DB\rules\basic\BasicRule;
use PhpBoot\DB\Context;

class ExecResult{
    public function __construct($success, $pdo, $st){
        $this->pdo = $pdo;
        $this->st = $st;
        $this->success = $success;
        $this->rows = $this->st->rowCount();
    }
    public function lastInsertId($name=null){
        return $this->pdo->lastInsertId($name);
    }
    /**
     * @var bool
     * true on success or false on failure.
     */
    public $success;
    /**
     * @var int
     * the number of rows.
     */
    public $rows;
    /**
     *
     * @var \PDO
     */
    public $pdo;

    /**
     * @var \PDOStatement
     */
    public $st;
}

class SelectImpl
{
    static  public function select($context, $columns){
        $context->appendSql("SELECT $columns");
    }
}

class FromImpl
{
    static public function from($context, $tables,$as=null){
        if($tables instanceof BasicRule){
            $context->appendSql("FROM (".$tables->context->sql.')');
            $context->params = array_merge($context->params,$tables->context->params);
        }else {
            $context->appendSql("FROM `$tables`");
        }
        if($as){
            $context->appendSql("as `$as`");
        }
    }
}

class DeleteImpl
{
    static public function deleteFrom($context, $from)
    {
        $context->appendSql("DELETE FROM `$from`");
    }
}

class JoinImpl
{
    static public function join($context, $type, $table) {
        if($type){
            $context->appendSql("$type JOIN $table");
        }else{
            $context->appendSql("JOIN $table");
        }
    }
}

class JoinOnImpl
{
    static public function on($context, $condition) {
        $context->appendSql("ON $condition");
    }
}

class ForUpdateImpl
{
    static public function forUpdate($context){
        $context->appendSql("FOR UPDATE");
    }
}

class ForUpdateOfImpl
{
    static public function of($context, $column){
        $context->appendSql("OF $column");
    }
}

class InsertImpl
{
    static public function insertInto($context, $table) {
        $context->appendSql("INSERT INTO `$table`");
    }
}
class ReplaceImpl
{
    static public function replaceInto($context, $table) {
        $context->appendSql("REPLACE INTO `$table`");
    }
}
class ValuesImpl
{
    static public function values($context, $values){
        $params = [];
        $stubs = [];
        foreach ($values as $v){
            if(is_a($v, Raw::class)){//直接拼接sql，不需要转义
                $stubs[]=$v->get();
            }else{
                $stubs[]='?';
                $params[] = $v;
            }
        }
        $stubs = implode(',', $stubs);

        if(array_keys($values) === range(0, count($values) - 1)){
            //VALUES(val0, val1, val2)
            $context->appendSql("VALUES($stubs)");

        }else{
            //(col0, col1, col2) VALUES(val0, val1, val2)
            $columns = implode(',', array_keys($values));
            $context->appendSql("($columns) VALUES($stubs)",false);
        }
        $context->appendParams($params);
    }
    private $sql = null;
}

class UpdateImpl
{
    static public function update($context, $table){
        $context->appendSql("UPDATE `$table`");
    }
}

class UpdateSetImpl
{
    public function set($context, $column, $value){
        $prefix = '';
        if($this->first){
            $this->first = false;
            $prefix = 'SET ';
        }else{
            $prefix = ',';
        }
        if(is_a($value, Raw::class)){
            $context->appendSql("$prefix$column=$value",$prefix == 'SET ');
        }else{
            $context->appendSql("$prefix$column=?",$prefix == 'SET ');
            $context->appendParams([$value]);
        }
    }

    public function setExpr($context, $expr, $args){
        $prefix = '';
        if($this->first){
            $this->first = false;
            $prefix = 'SET ';
        }else{
            $prefix = ',';
        }

        $context->appendSql("$prefix$expr",$prefix == 'SET ');
        $context->appendParams($args);

    }
    public function setArgs($context, $values){
        $set = [];
        $params = [];
        foreach ($values as $k=>$v){
            if(is_a($v, Raw::class)){//直接拼接sql，不需要转义
                $set[]= "$k=".$v->get();
            }else{
                $set[]= "$k=?";
                $params[]=$v;
            }
        }
        if($this->first){
            $this->first = false;
            $context->appendSql('SET '.implode(',', $set));
            $context->appendParams($params);
        }else{
            $context->appendSql(','.implode(',', $set),false);
            $context->appendParams($params);
        }
    }
    private $first=true;
}
class OrderByImpl
{
    public function orderByArgs($context, $orders){
        if(empty($orders)){
            return $this;
        }
        $params = array();
        foreach ($orders as $k=>$v){
            if(is_integer($k)){
                preg_match('/^[a-zA-Z0-9_.]+$/', $v) or \PhpBoot\abort(
                    new \InvalidArgumentException("invalid params for orderBy(".json_encode($orders).")"));

                $params[] = $v;
            }else{
                $v = strtoupper($v);
                preg_match('/^[a-zA-Z0-9_.]+$/', $k) &&
                ($v =='DESC' || $v =='ASC') or \PhpBoot\abort( new \InvalidArgumentException("invalid params for orderBy(".json_encode($orders).")"));

                $params[] = "$k $v";
            }
        }
        if($this->first){
            $this->first = false;
            $context->appendSql('ORDER BY '.implode(',', $params));
        }else{
            $context->appendSql(','.implode(',', $params),false);
        }
        return $this;
    }
    public function orderBy($context, $column, $order=null){
        if($this->first){
            $this->first = false;
            $context->appendSql("ORDER BY $column");
        }else{
            $context->appendSql(",$column", false);
        }
        if($order){
            $context->appendSql($order);
        }
        return $this;
    }
    private $first=true;
}

class LimitImpl
{
    static public function limit($context, $size){
        $intSize = intval($size);
        strval($intSize) == $size or \PhpBoot\abort(
            new \InvalidArgumentException("invalid params for limit($size)"));
        $context->appendSql("LIMIT $size");
    }
    static public function limitWithOffset($context,$start, $size){
        $intStart = intval($start);
        $intSize = intval($size);
        strval($intStart) == $start && strval($intSize) == $size or \PhpBoot\abort(
            new \InvalidArgumentException("invalid params for limit($start, $size)"));
        $context->appendSql("LIMIT $start,$size");
    }
}

class WhereImpl{

    static private function  findQ($str,$offset = 0,$no=0){
        $found = strpos($str, '?', $offset);
        if($no == 0 || $found === false){
            return $found;
        }
        return self::findQ($str, $found+1, $no-1);
    }
    static public function having($context, $expr, $args){
        self::condition($context, 'HAVING', $expr, $args);
    }
    static public function where($context, $expr, $args){
        self::condition($context, 'WHERE', $expr, $args);
    }

    static public function havingArgs($context, $args){
        self::conditionArgs($context, 'HAVING', $args);
    }
    static public function whereArgs($context, $args){
        self::conditionArgs($context, 'WHERE', $args);
    }
    /**
     * find like Mongodb query glossary
     * whereArray(
     *      [
     *          'id'=>['>'=>1],
     *          'name'=>'cym',
     *      ]
     * )
     * 支持的操作符有
     * =    'id'=>['=' => 1]
     * >    'id'=>['>' => 1]
     * <    'id'=>['<' => 1]
     * <>   'id'=>['<>' => 1]
     * >=   'id'=>['>=' => 1]
     * <=   'id'=>['<=' => 1]
     * BETWEEN  'id'=>['BETWEEN' => [1 ,2]]
     * LIKE     'id'=>['LIKE' => '1%']
     * IN   'id'=>['IN' => [1,2,3]]
     * NOT IN   'id'=>['NOT IN' => [1,2,3]]
     *
     * @param array $args
     */
    static public function conditionArgs($context, $prefix, $args=[]){
        if($args ===null){
            return ;
        }
        $exprs = array();
        $params = array();
        foreach ($args as $k => $v){
            if(is_array($v)){
                $ops = ['=', '>', '<', '<>', '>=', '<=', 'IN', 'NOT IN', 'BETWEEN', 'LIKE'];
                $op = array_keys($v)[0];
                $op = strtoupper($op);

                false !== array_search($op, $ops) or \PhpBoot\abort(
                    new \InvalidArgumentException("invalid param $op for whereArgs"));

                $var = array_values($v)[0];
                if($op == 'IN' || $op == 'NOT IN'){
                    $stubs = [];
                    foreach ($var as $i){
                        if(is_a($i, Raw::class)){
                            $stubs[]=strval($i);
                        }else{
                            $stubs[]='?';
                            $params[] = $i;
                        }
                    }
                    $stubs = implode(',', $stubs);
                    $exprs[] = "$k $op ($stubs)";
                }else if($op == 'BETWEEN'){
                    $cond = "$k BETWEEN";
                    if(is_a($var[0], Raw::class)){
                        $cond = "$cond ".strval($var[0]);
                    }else{
                        $cond = "$cond ?";
                        $params[] = $var[0];
                    }
                    if(is_a($var[1], Raw::class)){
                        $cond = "$cond AND ".strval($var[1]);
                    }else{
                        $cond = "$cond AND ?";
                        $params[] = $var[1];
                    }
                    $exprs[] = $cond;
                }else{
                    if(is_a($var, Raw::class)){
                        $exprs[] = "$k $op ".strval($var);
                    }else{
                        $exprs[] = "$k $op ?";
                        $params[] = $var;
                    }
                }
            }else{
                if(is_a($v, Raw::class)){
                    $exprs[] = "$k = ".strval($v);

                }else{
                    $exprs[] = "$k = ?";
                    $params[] = $v;
                }
            }
        }

        return self::condition($context, $prefix, implode(' AND ', $exprs), $params);
    }
    static public function condition($context, $prefix, $expr, $args){
        if(!empty($expr)){
            if($args){
                //因为PDO不支持绑定数组变量, 这里需要手动展开数组
                //也就是说把 where("id IN(?)", [1,2])  展开成 where("id IN(?,?)", 1,2)
                $cutted = null;
                $cut = null;
                $toReplace = array();

                $newArgs=array();
                //找到所有数组对应的?符位置
                foreach ($args as $k =>$arg){
                    if(is_array($arg) || is_a($arg, Raw::class)){
                        if(!$cutted){
                            $cut = new NestedStringCut($expr);
                            $cutted = $cut->getText();
                        }
                        //找到第$k个?符
                        $pos = self::findQ($cutted, 0, $k);
                        $pos = $cut->mapPos($pos);
                        $pos !== false or \PhpBoot\abort(
                            new \InvalidArgumentException("unmatched params and ? @ $expr"));

                        if(is_array($arg)){
                            $stubs = [];
                            foreach ($arg as $i){
                                if(is_a($i, Raw::class)){
                                    $stubs[] = strval($i);
                                }else{
                                    $stubs[] = '?';
                                    $newArgs[] = $i;
                                }
                            }
                            $stubs = implode(',', $stubs);
                        }else{
                            $stubs = strval($arg);
                        }
                        $toReplace[] = [$pos, $stubs];

                    }else{
                        $newArgs[]=$arg;
                    }
                }

                if(count($toReplace)){
                    $toReplace = array_reverse($toReplace);
                    foreach ($toReplace as $i){
                        list($pos, $v) = $i;
                        $expr = substr($expr, 0, $pos).$v. substr($expr, $pos+1);
                    }
                    $args = $newArgs;
                }
            }
            $context->appendSql($prefix.' '.$expr);
            if($args){
                $context->appendParams($args);
            }
        }
    }
}

class GroupByImpl{
    static public function groupBy($context, $column){
        $context->appendSql("GROUP BY $column");
    }
}

class ExecImpl
{
    /**
     *
     * @param Context $context
     * @param $exceOnError boolean whether throw exceptions
     * @return ExecResult
     */
    static public function exec($context) {
        $st = $context->connection->prepare($context->sql);
        $success = $st->execute($context->params);
        return new ExecResult($success, $context->connection, $st);
    }
    /**
     *
     * @param Context $context
     * @param string|false $asDict return  as dict or array
     * @return false|array
     */
    static public function get($context, $dictAs=false){

        $st = $context->connection->prepare($context->sql);
        if($st->execute($context->params)){
            $res = $st->fetchAll(\PDO::FETCH_ASSOC);
            if ($dictAs){
                $dict= [];
                foreach ($res as $i){
                    $dict[$i[$dictAs]]=$i;
                }
                return $context->handleResult($dict);
            }
            return $context->handleResult($res);
        }else{
            return false;
        }
    }

    /**
     * @param Context $context
     * @return int|false
     */
    static public function count($context){

        $found = [];
        if(!preg_match('/\bselect\b/i', $context->sql, $found, PREG_OFFSET_CAPTURE) ||
            count($found)==0){
            \PhpBoot\abort(new \PDOException("can not use count(*) without select"));
        }
        list($chars, $columnBegin) = $found[0];
        $columnBegin = $columnBegin + strlen('select')+1;

        $columnEnd = 0;
        $found = [];
        if(!preg_match('/\bfrom\b/i', $context->sql, $found, PREG_OFFSET_CAPTURE) ||
            count($found)==0){
            $columnEnd = strlen($context->sql);
        }else{
            list($chars, $columnEnd) = $found[0];
        }
        $sql = substr($context->sql, 0, $columnBegin);
        $sql .= ' COUNT(*) as `count` ';
        $sql .= substr($context->sql, $columnEnd);

        $st = $context->connection->prepare($sql);
        if($st->execute($context->params)){
            $res = $st->fetchAll(\PDO::FETCH_ASSOC);
            return $res[0]['count'];
        }else{
            return false;
        }

    }
}
class OnDuplicateKeyUpdateImpl
{
    public function set($context, $column, $value){
        $prefix = '';
        if($this->first){
            $this->first = false;
            $prefix = 'ON DUPLICATE KEY UPDATE ';
        }else{
            $prefix = ',';
        }
        if(is_a($value, Raw::class)){
            $context->appendSql("$prefix$column=$value",$prefix == 'ON DUPLICATE KEY UPDATE ');
        }else{
            $context->appendSql("$prefix$column=?",$prefix == 'ON DUPLICATE KEY UPDATE ');
            $context->appendParams([$value]);
        }
    }

    public function setExpr($context, $expr, $args){
        $prefix = '';
        if($this->first){
            $this->first = false;
            $prefix = 'ON DUPLICATE KEY UPDATE ';
        }else{
            $prefix = ',';
        }

        $context->appendSql("$prefix$expr",$prefix == 'ON DUPLICATE KEY UPDATE ');
        $context->appendParams($args);

    }
    public function setArgs($context, $values){
        $set = [];
        $params = [];
        foreach ($values as $k=>$v){
            if(is_a($v, Raw::class)){//直接拼接sql，不需要转义
                $set[]= "$k=".$v->get();
            }else{
                $set[]= "$k=?";
                $params[]=$v;
            }
        }
        if($this->first){
            $this->first = false;
            $context->appendSql('ON DUPLICATE KEY UPDATE '.implode(',', $set));
            $context->appendParams($params);
        }else{
            $context->appendSql(','.implode(',', $set),false);
            $context->appendParams($params);
        }
    }
    private $first=true;
}
