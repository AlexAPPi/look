<?php

namespace LookPhp\API\Parser;

use LookPhp\Type\Converter;
use LookPhp\API\Controller as APIController;

use LookPhp\API\Parser\Parser;
use LookPhp\API\Parser\Exceptions\ParserException;

use LookPhp\API\Parser\TypeScript\TSType;
use LookPhp\API\Parser\TypeScript\TSValue;
use LookPhp\API\Parser\TypeScript\TSClass;
use LookPhp\API\Parser\TypeScript\TSMethod;
use LookPhp\API\Parser\TypeScript\TSArgument;
use LookPhp\API\Parser\TypeScript\TSNamespace;
use LookPhp\API\Parser\TypeScript\TSInterface;
use LookPhp\API\Parser\TypeScript\TSConstructor;
use LookPhp\API\Parser\TypeScript\TSArgumentList;
use LookPhp\API\Parser\TypeScript\TSInterfaceProperty;
use LookPhp\API\Parser\TypeScript\TSConstructorArgument;
use LookPhp\API\Parser\TypeScript\TSConstructorArgumentList;

use LookPhp\API\Parser\Struct\ExtractableScalarObject as ExtractableScalarObjectStruct;
use LookPhp\API\Parser\Struct\ExtractableScalarArray as ExtractableScalarArrayStruct;
use LookPhp\API\Parser\Struct\ArgumentClass as ArgumentClassStruct;
use LookPhp\API\Parser\Struct\APIClass as APIClassStruct;
use LookPhp\API\Parser\Struct\Argument as ArgumentStruct;
use LookPhp\API\Parser\Struct\Method as MethodStruct;
use LookPhp\API\Parser\Struct\Value as ValueStruct;
use LookPhp\API\Parser\Struct\Type as TypeStruct;

/**
 * Собирает классы и функции в библиотеку TS
 */
class BuildAPITSLib
{
    /** @var TSNamespace[] */
    protected static $namespaces   = [];
    
    /** @var TSNamespace[] */
    protected static $apiNamespaces = [];
    
    public static function extractTSInterface(ArgumentClassStruct $class) : void
    {
        if(!isset(static::$namespaces[$class->namespace])) {
            static::$namespaces[$class->namespace] = new TSNamespace($class->namespace);
        }
        
        if(static::$namespaces[$class->namespace]->has($class->name)) {
            return;
        }
        
        $tsInterface = new TSInterface($class->name, $class->comment);
        
        foreach($class->constructor->arguments as $name => $argument)
        {
            if($argument instanceof ArgumentStruct)
            {
                // Аргумент принимает объект
                if($argument->type->class instanceof ArgumentClassStruct)
                {
                    static::extractTSInterface($argument->type->class);
                }
                
                $tsType     = new TSType($argument->type);
                $tsValue    = new TSValue($argument->default);
                $tsProperty = new TSInterfaceProperty(
                    false,
                    $name,
                    $tsType,
                    $tsValue,
                    $class->constructor->comment
                );
                $tsInterface->addProperty($tsProperty);
            }
        }
        
        static::$namespaces[$class->namespace]->addInterface($tsInterface);
    }
    
    public static function extractAPIMethods(array $methods) : ?array
    {
        $result = [];
        
        foreach($methods as $method)
        {
            if($method instanceof MethodStruct)
            {
                $arguments = new TSArgumentList($method->comment);
                if($method->arguments)
                {
                    foreach($method->arguments as $name => $argument)
                    {
                        if($argument instanceof ArgumentStruct) {
                            
                            if($argument->type->class instanceof ArgumentClassStruct)
                            {
                                static::extractTSInterface($argument->type->class);
                            }
                        }
                        
                        $type    = new TSType($argument->type);
                        $default = new TSValue($argument->default);
                        
                        $arguments[] = new TSArgument(
                            $argument->name,
                            $type,
                            $default,
                            $argument->required,
                            $argument->variadic,
                            $argument->position
                        );
                    }
                }
                
                $result[] = new TSMethod(
                    $method->name,
                    $arguments,
                    TSMethod::PublicAccess,
                    TSMethod::StaticMethod,
                    $method->comment
                );
            }
        }
        
        return $result;
    }
        
    public static function build() : void
    {
        $struct = Parser::parseApiDir();

        foreach($struct as $class)
        {
            if($class instanceof APIClassStruct)
            {
                if(!isset(static::$apiNamespaces[$class->namespace])) {
                    static::$apiNamespaces[$class->namespace] = new TSNamespace($class->namespace);
                }
                
                if(static::$apiNamespaces[$class->namespace]->has($class->name)) {
                    continue;
                }
                
                if($class->methods)
                {
                    $extract = static::extractAPIMethods(
                        $class->methods
                    );
                    
                    if($extract && count($extract) > 0) {
                        $apiClass = new TSClass(false, $class->name, $class->comment);
                        foreach($extract as $method) {
                            $apiClass->addMethod($method);
                        }
                        static::$apiNamespaces[$class->namespace]->addClass($apiClass);
                    }
                }
            }
        }
        
        $i               = 0;
        $namespaces      = array_merge(static::$namespaces, static::$apiNamespaces);
        $namespacesCount = count($namespaces);
        foreach($namespaces as $namespace) {
            $i++;
            echo $namespace->toTS();
            if($i < $namespacesCount) {
                echo "\n";
            }
        }
        
        //var_dump($struct);
        //var_dump(static::$interfaces);
        //var_dump($classes);
    }
}