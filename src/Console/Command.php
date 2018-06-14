<?php
/**
 * Created by PhpStorm.
 * User: caoyangmin
 * Date: 2018/6/14
 * Time: 下午6:03
 */

namespace PhpBoot\Console;


use PhpBoot\Entity\ArrayContainer;
use PhpBoot\Entity\EntityContainer;
use PhpBoot\Metas\ParamMeta;
use PhpBoot\Utils\ArrayAdaptor;
use PhpBoot\Validator\Validator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

class Command extends \Symfony\Component\Console\Command\Command
{

    /**
     * @var ParamMeta[]
     */
    private $paramMetas;

    /**
     * @var string
     */
    private $actionName;

    public function __construct($actionName, $name)
    {
        parent::__construct($name?:$actionName);
        $this->actionName = $actionName;
    }
    /**
     * @param ParamMeta[] $paramMetas
     */
    public function setParamMetas($paramMetas)
    {
        $this->paramMetas = $paramMetas;
    }

    public function postCreate(ConsoleContainer $container)
    {
        if($container->getModuleName()){
            $this->setName($container->getModuleName().'.'.$this->getName());
        }

        foreach ($this->paramMetas as $paramMeta){
            $mode = $paramMeta->isOptional?InputArgument::OPTIONAL:InputArgument::REQUIRED;
            if($paramMeta->container instanceof ArrayContainer || $paramMeta->container instanceof EntityContainer){
                $mode = $mode|InputArgument::IS_ARRAY;
            }
            $this->addArgument($paramMeta->name,
                $mode,
                $paramMeta->description,
                $paramMeta->default
                );
        }
    }

    public function getParamMeta($name)
    {
        foreach ($this->paramMetas as $meta){
            if($meta->name == $name){
                return $meta;
            }
        }
        return null;
    }

    public function invoke(ConsoleContainer $container, InputInterface $input, OutputInterface $output)
    {
        $params = [];
        $reference = [];
        $this->bindInput($input, $params,$reference);
        ob_start();
        $code = call_user_func_array([$container->getInstance(), $this->actionName], $params);
        $out = ob_get_contents();
        ob_end_clean();
        $output->write($out);
        return $code;
    }

    public function bindInput(InputInterface $input, array &$params, array &$reference){
        $vld = new Validator();
        $req = ['argv'=>$input->getArguments()];
        $requestArray = new ArrayAdaptor($req);
        $inputs = [];
        foreach ($this->paramMetas as $k=>$meta){
            if($meta->isPassedByReference){
                // param PassedByReference is used to output
                continue;
            }
            $source = \JmesPath\search($meta->source, $requestArray);
            if ($source !== null){
                $source = ArrayAdaptor::strip($source);
                if($source instanceof ParameterBag){
                    $source = $source->all();
                }
                if($meta->container){
                    $inputs[$meta->name] = $meta->container->make($source);
                }else{
                    $inputs[$meta->name] = $source;
                }
                if($meta->validation){
                    $vld->rule($meta->validation, $meta->name);
                }
            }else{
                $meta->isOptional or \PhpBoot\abort(new \InvalidArgumentException("the parameter \"{$meta->source}\" is missing"));
                $inputs[$meta->name] = $meta->default;
            }
        }
        $vld = $vld->withData($inputs);
        $vld->validate() or \PhpBoot\abort(
            new \InvalidArgumentException(
                json_encode(
                    $vld->errors(),
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                )
            )
        );

        $pos = 0;
        foreach ($this->paramMetas as $meta){
            if($meta->isPassedByReference){
                $params[$pos] = null;
            }else{
                $params[$pos] = $inputs[$meta->name];
            }
            $pos++;
        }
    }
}