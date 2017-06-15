<?php
/**
 * Created by PhpStorm.
 * User: caoyangmin
 * Date: 16/11/10
 * Time: 下午6:50
 */

namespace Once\Container;


use Illuminate\Support\Facades\App;
use Laravel\Lumen\Application;
use Once\Metas\PropertyMeta;
use Once\Utils\AnnotationsVisitor;
use Once\Utils\Validation;
use Once\Utils\Validator;
use Once\Utils\Verify;

class EntityContainer extends ClassAnnotations
{
    public function __construct($className)
    {
        $this->className = $className;
        //TODO 缓存
        $refl = new \ReflectionClass($className);

        $docFactory  = AnnotationsVisitor::createDocBlockFactory();
        if($refl->getDocComment()){
            $docblock = $docFactory->create($refl->getDocComment());
            $this->doc = $docblock->getSummary()."\n".$docblock->getDescription();
        }


        $properties = $refl->getProperties(\ReflectionProperty::IS_PUBLIC);
        $default = $refl->getDefaultProperties();
        $this->fileName = $refl->getFileName();
        foreach ($properties as $i){
            $isOption = array_has($default, $i->getName()) && $default[$i->getName()] !==null;
            if($i->getDocComment()) {
                $docblock = $docFactory->create($i->getDocComment());
                $doc = $docblock->getSummary()."\n".$docblock->getDescription();//TODO * 去掉已经解析出的@
            }else{
                $doc = '';
            }

            $this->properties[$i->getName()] = new PropertyMeta(
                $i->getName(),
                null,
                $isOption,
                $isOption?$default[$i->getName()]:null,
                null,
                $doc);
        }
    }

    public function getProperty($target){
        array_has($this->properties, $target) or Verify::fail("property $target not exist");
        return $this->properties[$target];
    }

    /**
     * @return PropertyMeta[]
     */
    public function getProperties(){
        return $this->properties;
    }
    public function getDoc(){
        return $this->doc;
    }

    /**
     * @param Application $app
     * @param array|object $params
     */
    public function make(Application $app, $params, $validate = true){
        if($params === null){
            return $params;
        }
        if(is_string($params)){
            $params = json_decode($params, true) or Verify::fail(
                new \InvalidArgumentException("make {$this->className} failed, ".json_last_error_msg()));
        }
        is_array($params)|| is_a($params, $this->className) or Verify::fail(
            new \InvalidArgumentException("make {$this->className} failed, array or object is require by param 2")
        );
        if(is_a($params, $this->className)){
            return $params;
        }
        $input = [];
        if($validate){
            $vld = new Validator($app);
            foreach ($this->getProperties() as $property){
                if(array_has($params, $property->name)){
                    $value = $params[$property->name];
                    //TODO 对象嵌套
                    $vld->addRule($property->name, $property->type, $property->validation);
                    $input[$property->name] = $value;
                }else{
                    $property->isOptional or  Verify::fail(new \InvalidArgumentException("property {$property->name} is required by {$this->className}"));
                    $input[$property->name] = $property->default;
                }
            }
            $input = $vld->validate($input);
        }else{
            $input = $params;
        }

        $output = $app->make($this->className);
        foreach ($input as $k=>$v){
            $output->{$k}=$v;
        }
        return $output;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }
    /**
     * @var PropertyMeta[]
     */
    private $properties=[];

    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $doc='';

    /**
     * @var string
     */
    private $fileName;
}