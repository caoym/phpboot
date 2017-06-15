<?php
/**
 * Created by PhpStorm.
 * User: caoyangmin
 * Date: 16/11/15
 * Time: 下午9:23
 */

namespace Once\Container;

/**
 * Class ClassAnnotations
 * @package Once\Container
 * @author
 * TODO * 保存所有已处理的注释
 */
class ClassAnnotations
{

    /**
     * @return array[][]
     * sample: [['path',['/users/']], ['author',['caoyangmin']]
     */
    public function getClassAnnotations()
    {
        return $this->classAnnotations;
    }

    /**
     * @param string $propertyName
     * @return array[][]
     * sample:
     * [
     *      ['var', ['string']]
     * ]
     *
     */
    public function getPropertyAnnotations($propertyName)
    {
        return $this->propertyAnnotations;
    }

    /**
     * @param string $methodName
     * @return array[][]
     * sample:
     * [
     *   ['param', ['string','$arg0']]
     * ]
     *
     */
    public function getMethodAnnotations($methodName)
    {
        if(!array_has($this->methodAnnotations, $methodName)){
            return [];
        }
        return $this->methodAnnotations[$methodName];
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasClassAnnotation($name)
    {
        return !!count($this->getClassAnnotation($name));
    }

    /**
     * @param $name
     * @return array[]
     */
    public function getClassAnnotation($name)
    {
        return
            array_values(array_map(
                function ($i) {
                    return $i[1];
                },
                array_filter(
                    $this->classAnnotations,
                    function ($i) use ($name) {
                        return $i[0] == $name;
                    }
                )
            ));
    }

    /**
     * @param $name
     * @return array[]
     */
    public function getClassAnnotationFirst($name)
    {
        $ann = $this->getClassAnnotation($name);
        return count($ann) ? $ann[0] : null;
    }


    /**
     * @param $method
     * @param $name
     * @return bool
     */
    public function hasMethodAnnotation($method, $name)
    {
        return !!count($this->getMethodAnnotation($method, $name));
    }

    /**
     * @param $method
     * @param $name
     * @return array[]
     */
    public function getMethodAnnotation($method, $name)
    {
        if(!isset($this->methodAnnotations[$method])){
            return [];
        }
        return array_values(array_map(
            function ($i) {
                return $i[1];
            },
            array_filter(
                $this->methodAnnotations[$method],
                function ($i) use ($name) {
                    return $i[0] == $name;
                }
            )
        ));
    }

    /**
     * @param $method
     * @param $name
     * @return array[]
     */
    public function getMethodAnnotationFirst($method, $name)
    {
        $ann = $this->getMethodAnnotation($method, $name);
        return count($ann) ? $ann[0] : null;
    }

    /**
     * @param $property
     * @param $name
     * @return bool
     */
    public function hasPropertyAnnotation($property, $name)
    {
        return !!count($this->getPropertyAnnotation($property, $name));
    }

    /**
     * @param $property
     * @param $name
     * @return array[]
     */
    public function getPropertyAnnotation($property, $name)
    {
        if(!isset($this->propertyAnnotations[$property])){
            return [];
        }
        return array_values(array_map(
            function ($i) {
                return $i[1];
            },
            array_filter(
                $this->propertyAnnotations[$property],
                function ($i) use ($name) {
                    return $i[0] == $name;
                }
            )));
    }

    /**
     * @param $property
     * @param $name
     * @return array[]
     */
    public function getPropertyAnnotationFirst($property, $name)
    {
        $ann = $this->getPropertyAnnotation($property, $name);
        return count($ann) ? $ann[0] : null;
    }

    public function addClassAnnotation($name, array $value)
    {
        $this->classAnnotations[] = [$name, $value];
    }

    public function addPropertyAnnotation($property, $name, array $value)
    {
        $this->propertyAnnotations[$property][] = [$name, $value];
    }

    public function addMethodAnnotation($method, $name, array $value)
    {
        $this->methodAnnotations[$method][] = [$name, $value];
    }

    /**
     * @var array[][]
     * sample: [['path',['/users/']], ['author',['caoyangmin']]
     */
    private $classAnnotations = [];

    /**
     * @var array[][][]
     * sample:
     * [
     *      'method1'=>[
     *             ['param', ['string','$arg0']]
     *      ]
     * ]
     */
    private $methodAnnotations = [];

    /**
     * @var array[][][]
     * sample:
     * [
     *      'property1'=>[
     *             ['var', ['string']]
     *      ]
     * ]
     */
    private $propertyAnnotations = [];
}