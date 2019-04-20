<?php

namespace Look\API\Parser;

/** use File System Lib */
use Look\API\Parser\TypeScript\TSNamespaceFS as TSNamespace;

use Look\API\Parser\Parser;
use Look\API\Parser\TypeScript\TSEnum;
use Look\API\Parser\TypeScript\TSEnumValue;

use Look\API\Parser\TypeScript\TSType;
use Look\API\Parser\TypeScript\TSValue;
use Look\API\Parser\TypeScript\TSClass;
use Look\API\Parser\TypeScript\TSMethod;
use Look\API\Parser\TypeScript\TSArgument;
use Look\API\Parser\TypeScript\TSInterface;
use Look\API\Parser\TypeScript\TSConstructor;
use Look\API\Parser\TypeScript\TSArgumentList;
use Look\API\Parser\TypeScript\TSInterfaceProperty;
use Look\API\Parser\TypeScript\TSConstructorArgument;
use Look\API\Parser\TypeScript\TSConstructorArgumentList;

use Look\API\Parser\Struct\ExtractableScalarObject as ExtractableScalarObjectStruct;
use Look\API\Parser\Struct\ExtractableScalarArray as ExtractableScalarArrayStruct;
use Look\API\Parser\Struct\ExtractableEnum as ExtractableEnumStruct;
use Look\API\Parser\Struct\ArgumentClass as ArgumentClassStruct;
use Look\API\Parser\Struct\APIClass as APIClassStruct;
use Look\API\Parser\Struct\Argument as ArgumentStruct;
use Look\API\Parser\Struct\Method as MethodStruct;
use Look\API\Parser\Struct\Value as ValueStruct;
use Look\API\Parser\Struct\Type as TypeStruct;

/**
 * Собирает классы и функции в библиотеку TS
 */
class BuildAPITSLib
{
    /** @var TSNamespace[] */
    protected static $namespaces   = [];
    
    /** @var TSNamespace[] */
    protected static $apiNamespaces = [];
    
    public static function extractTSEnum(ExtractableEnumStruct $class) : void
    {
        if(!isset(static::$namespaces[$class->namespace])) {
            static::$namespaces[$class->namespace] = new TSNamespace($class->namespace);
        }
        
        if(static::$namespaces[$class->namespace]->has($class->name)) {
            return;
        }
        
        $values = [];
        foreach($class->values as $value) {
            $values[] = new TSEnumValue($value->name, new TSValue($value->value), $value->comment);
        }
        
        $tsEnum = new TSEnum($class->name, $class->comment, ...$values);
        static::$namespaces[$class->namespace]->addEnum($tsEnum);
    }
    
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
                // Аргумент принимает Enum
                if($argument->type->class instanceof ExtractableEnumStruct) {
                    static::extractTSEnum($argument->type->class);
                }
                
                // Аргумент принимает объект
                else if($argument->type->class instanceof ArgumentClassStruct) {
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
        foreach($methods as $method) {
            
            if($method instanceof MethodStruct) {
                
                $arguments = new TSArgumentList($method->comment);
                
                if($method->arguments) {
                    
                    foreach($method->arguments as $name => $argument) {
                        
                        if($argument instanceof ArgumentStruct) {
                            
                            // Аргумент принимает Enum
                            if($argument->type->class instanceof ExtractableEnumStruct) {
                                static::extractTSEnum($argument->type->class);
                            }
                            
                            // Аргумент принимает объект
                            else if($argument->type->class instanceof ArgumentClassStruct) {
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
        foreach($struct as $class) {
            
            if($class instanceof APIClassStruct) {
                
                if(!isset(static::$apiNamespaces[$class->namespace])) {
                    static::$apiNamespaces[$class->namespace] = new TSNamespace($class->namespace);
                }
                
                if(static::$apiNamespaces[$class->namespace]->has($class->name)) {
                    continue;
                }
                
                if($class->methods) {
                    
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
        
        $namespaces = array_merge(static::$namespaces, static::$apiNamespaces);
        foreach($namespaces as $namespace) {
            $namespace->toTS();
        }
        
        //var_dump($struct);
        //var_dump(static::$interfaces);
        //var_dump($classes);
    }
}