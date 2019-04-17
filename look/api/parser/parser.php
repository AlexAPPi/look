<?php

namespace Look\API\Parser;

use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;

use Look\API\Type\TypeManager;
use Look\API\Type\Interfaces\IType;
use Look\API\Type\Interfaces\IScalar;

use Look\API\Controller as APIController;

use Look\API\Type\Token\IToken;

use Look\API\Parser\Struct\ExtractableScalarObject as ExtractableScalarObjectStruct;
use Look\API\Parser\Struct\ExtractableScalarArray as ExtractableScalarArrayStruct;
use Look\API\Parser\Struct\ArgumentClass as ArgumentClassStruct;
use Look\API\Parser\Struct\APIClass as APIClassStruct;
use Look\API\Parser\Struct\Argument as ArgumentStruct;
use Look\API\Parser\Struct\Method as MethodStruct;
use Look\API\Parser\Struct\Value as ValueStruct;
use Look\API\Parser\Struct\Type as TypeStruct;

use Look\Type\Interfaces\IValue;
use Look\Type\Interfaces\IArray;
use Look\API\Parser\Exceptions\ParserException;

/**
 * Собирает структуру
 */
class Parser
{
    public static function extractScalarObject(ReflectionClass $class) : ?ExtractableScalarObjectStruct
    {
        $className = $class->getName();
        
        // То что искали, объект является оберткой скалярного типа
        if(is_subclass_of($className, IScalar::class)) {
            
            // Функция возврата системного типа
            $getScalarTypeFn = "$className::__getSystemEvalType";
            
            $struct = new ExtractableScalarObjectStruct();
            $struct->namespace  = $class->getNamespaceName();
            $struct->class      = $class->getShortName();
            $struct->scalarType = $getScalarTypeFn();

            if($comment = $class->getDocComment()) {
                $struct->comment = new DocBlock($comment);
            }

            return $struct;
        }
        
        return null;
    }

    public static function extractScalarArray(ReflectionClass $class) : ?ExtractableScalarArrayStruct
    {
        $className = $class->getName();
        $itemTypeConst = $className.'::ItemType';
        if(!defined($itemTypeConst)) {
            throw ParserException("Нарушен принцип работы ::ItemType в $className");
        }
        $defaultType = constant($itemTypeConst);
        $type        = Converter::getFixType($defaultType);
        
        // То что искали, массив являестя скалярным
        if(Converter::isScalarType($type)) {
            $struct = new ExtractableScalarArrayStruct();
            $struct->namespace  = $class->getNamespaceName();
            $struct->arrayClass = $class->getShortName();
            $struct->scalarType = $type;
            
            if($comment = $class->getDocComment()) {
                $struct->comment = new DocBlock($comment);
            }
            
            return $struct;
        }
        
        // Проверяем, мб элементы массива являются оберткой скалярного типа        
        if(is_subclass_of($type, IValue::class)) {
            
            $itemStruct = static::extractScalarObject(
                new ReflectionClass($type)
            );
            
            if($itemStruct) {
            
                $struct = new ExtractableScalarArrayStruct();
                $struct->namespace  = $class->getNamespaceName();
                $struct->arrayClass = $class->getShortName();
                $struct->scalarType = $itemStruct->scalarType;
                
                if($comment = $class->getDocComment()) {
                    $struct->comment = new DocBlock($comment);
                }
                
                return $struct;
            }
        }
        
        return null;
    }

    public static function parseNotScalarType(ReflectionParameter $argument)
    {
        if(!$argument->hasType()) {
            throw new ParserException('Аргумент не имеет типа');
        }
        
        $argName = $argument->getName();
        $class   = (string)$argument->getType();
        
        if(!class_exists($class)) {
            throw new ParserException("Класс [$class] объявленный типом для аргумента [$argName] не существует");
        }
        
        // TODO Список спец типов
        
        // Конструктор токена не может быть изменен
        // Особенности вызова смотрите в \Look\API\Caller
        if(is_subclass_of($class, IToken::class)) {
            return $class;
        }
        
        $reflectionClass = new ReflectionClass($class);
        
        if(!$reflectionClass
        || $reflectionClass->isAbstract()
        || $reflectionClass->isTrait()
        || $reflectionClass->isInterface()
        || $reflectionClass->isAnonymous()) {
            throw new ParserException("Тип [$class] объявленный для аргумента [$argName] должен быть классом");
        }
                
        // Т.к классы типа IArray, IValue и т.п используются для кучкования данных
        // Выполним обратную разборку данных
        $reflectionConstructor = $reflectionClass->getConstructor();
        
        if(!$reflectionConstructor) {
            throw new ParserException("Не удалось получить функцию констурктора класса [$class]");
        }
        
        $isArray = is_subclass_of($class, IArray::class);
        
        // Если данный объект является массивом со скалярным типом
        // Извлекаем скалярный тип и подменяем 
        if($isArray) {
            
            $struct = self::extractScalarArray($reflectionClass);
            
            if($struct) {
                return $struct;
            }
        }
        
        $isValue = is_subclass_of($class, IValue::class);
        
        // Если данный объект является оберткой для скалярного типа
        // Извлекаем скалярный тип и подменяем
        if($isValue && !$isArray) {
            
            $struct = self::extractScalarObject($reflectionClass);
            
            if($struct) {
                return $struct;
            }
        }
        
        $struct = new ArgumentClassStruct();
        $struct->namespace   = $reflectionClass->getNamespaceName();
        $struct->name        = $reflectionClass->getShortName();
        $struct->constructor = static::parseMethod($reflectionConstructor);
        
        if($comment = $reflectionClass->getDocComment()) {
            $struct->comment = new DocBlock($comment);
        }
        
        return $struct;
    }
    
    /**
     * Парсит тип агрумента
     * @param ReflectionParameter $argument
     * @return TypeStruct|null
     */
    public static function parseArgumentType(ReflectionParameter $argument) : ?TypeStruct
    {
        if($argument->hasType())
        {
            $typeStruct = new TypeStruct();
            
            $type     = $argument->getType();
            $typeName = (string)$type;
            
            // Скалярный тип
            if($type->isBuiltin() || TypeManager::isScalarType($typeName)) {
                $typeStruct->class    = $typeName;
                $typeStruct->isScalar = true;
                return $typeStruct;
            }
            
            $typeStruct->class    = static::parseNotScalarType($argument);
            $typeStruct->isScalar = false;
            return $typeStruct;
        }
        
        return null;
    }
    
    public static function parseArgumentDefaultValue(ReflectionParameter $argument) : ?ValueStruct
    {
        if($argument->isDefaultValueAvailable())
        {
            $valueStruct = new ValueStruct();
            $valueStruct->name = $argument->getName();
            
            // Значение передано в качестве константы
            if($argument->isDefaultValueConstant()) {
                
                $constName = $argument->getDefaultValueConstantName();
                
                // fatal error
                if(!defined($constName)) {
                    throw new ParserException("В качесте значения передана не существующая константа: $constName");
                }
                
                $valueStruct->value = constant($constName);
            }
            else {
                $valueStruct->value = $argument->getDefaultValue();
            }
            
            $detectedType  = null;
            $detectedValue = null;
            
            if(TypeManager::detectBaseType($valueStruct->value, $detectedValue, $detectedType)) {
                $valueStruct->value = $detectedValue;
                $valueStruct->type  = $detectedType;
            } else {
                $valueStruct->type = 'mixed';
            }
            
            return $valueStruct;
        }
        
        return null;
    }
        
    public static function parseArgument(ReflectionParameter $argument) : ?ArgumentStruct
    {
        $type    = static::parseArgumentType($argument);
        $default = static::parseArgumentDefaultValue($argument);
        
        $argumentStruct       = new ArgumentStruct();
        $argumentStruct->name = $argument->getName();
        
        $argumentStruct->variadic = $argument->isVariadic();
        $argumentStruct->position = $argument->getPosition();
        $argumentStruct->type     = $type;
        $argumentStruct->default  = $default;
        
        $argumentStruct->required = (!$argument->isDefaultValueAvailable() && !$argument->allowsNull());
        
        if($type) {
            // Выполняем подмену значений, т.к передан массив
            if($type->class instanceof ExtractableScalarArrayStruct) {
                $argumentStruct->variadic = true;
            }
        }
        
        return $argumentStruct;
    }
    
    public static function parseMethod(ReflectionMethod $method) : ?MethodStruct
    {
        $methodStruct       = new MethodStruct();
        $methodStruct->name = $method->getName();
        $methodStruct->arguments = [];
        
        $comment = $method->getDocComment();
        if($comment) {
            $methodStruct->comment = new DocBlock($comment);
        }
        
        $arguments = $method->getParameters();
        
        foreach($arguments as $arg) {
            $tmp = static::parseArgument($arg);
            if($tmp) {
                $methodStruct->arguments[$tmp->name] = $tmp;
            }
        }
        
        return $methodStruct;
    }
    
    public static function praseClass(ReflectionClass $reflectionClass) : ?APIClassStruct
    {
        // Преобразуем только классы
        if($reflectionClass->isAbstract()
        || $reflectionClass->isAnonymous()
        || $reflectionClass->isTrait()
        || $reflectionClass->isInterface()) {
            return null;
        }
        
        $classStruct            = new APIClassStruct();
        $classStruct->namespace = $reflectionClass->getNamespaceName();
        $classStruct->name      = $reflectionClass->getShortName();
        $classStruct->methods   = [];
        
        $comment = $reflectionClass->getDocComment();
        if($comment) {
            $classStruct->comment = new DocBlock($comment);
        }
        
        // Обрабатываем только статичные публичные методы
        $publicMethods = $reflectionClass->getMethods(
            ReflectionMethod::IS_STATIC |
            ReflectionMethod::IS_PUBLIC
        );
        
        // Пропускаем пустые классы (без методов)
        if(!$publicMethods
        || count($publicMethods) == 0) {
            return null;
        }
        
        foreach($publicMethods as $method) {
            $tmp = static::parseMethod($method);
            if($tmp) {
                $classStruct->methods[$tmp->name] = $tmp;
            }
        }
        
        // Пропускаем пустые классы (без методов)
        if(count($classStruct->methods) == 0) {
            return null;
        }
        
        return $classStruct;
    }
        
    public static function parseApiDir() : array
    {
        $files  = scandir(API_DIR);
        $result = [];
        
        foreach($files as $fileName)
        {
            // Только файлы
            if($fileName == '.'
            || $fileName == '..'
            || !is_file(API_DIR . DIRECTORY_SEPARATOR . $fileName)) {
                continue;
            }
            
            $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);

            if($fileExt != '.php')
            {
                // Удаляем расширение файла
                $offset    = 1 + strlen($fileExt);
                $className = substr($fileName, 0, - $offset);

                // Класс существует и он доступен по веб
                if(APIController::apiClassExists($className)
                && APIController::apiClassWebAccess($className)) {

                    $class = APP_NAME . '\\API\\' . $className;
                    $ref   = new ReflectionClass($class);
                    $parse = static::praseClass($ref);

                    if($parse) {
                        $result[] = $parse;
                    }
                }
            }
        }
        
        return $result;
    }
}