<?php

namespace Look\API;

use TypeError;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionParameter;

use Look\API\Type\TypeManager;
use Look\API\Type\Interfaces\IType;
use Look\API\Type\Interfaces\IScalar;
use Look\API\Type\Interfaces\IScalarArray;

use Look\API\Type\Exceptions\ArrayException;
use Look\API\Type\Exceptions\BooleanException;
use Look\API\Type\Exceptions\UndefinedException;
use Look\API\Type\Exceptions\BooleanArrayException;
use Look\API\Type\Exceptions\AutoArgumentException;

use Look\Exceptions\SystemLogicException;

use Look\API\Type\Token\IToken;
use Look\API\Type\Token\DataBase\ITokenDataBase;
use Look\API\Type\Token\DataBase\MySQLiTokenDataBase;
use Look\API\Type\Token\Exceptions\BadTokenException;
use Look\API\Type\Token\Exceptions\AccessTokenException;
use Look\API\Type\Token\Exceptions\ExpiredTokenException;

use Look\API\Exceptions\APICallerException;
use Look\API\Exceptions\ObjectStructException;
use Look\API\Type\Exceptions\InvalidArgumentException;

use Look\API\Type\Enum;
use Look\API\Type\Exceptions\EnumException;

/**
 * Реализует интерфейс API обращение к функциям с помощью данных
 * Конвертирует данные и создает аргументы для вызова
 */
class Caller
{
    use \Look\Type\Traits\Singleton;
    use \Look\Type\Traits\Settingable;
    
    /** Метод который будет вызываться по умолчанию при создании экземпляра класса */
    const ClassConstructorMethod = '__construct'; 
    
    /** @var callable[] Хранит обработчиков типа */
    private static $handlers = [];
    
    /** @var ITokenDataBase объект базы данных */
    private $tokenDB;
    
    /**
     * @throws APICallerException
     */
    private function __construct()
    {
        $tokenDB = $this->getSetting('tokenDB', MySQLiTokenDataBase::class);
        if(!is_subclass_of($tokenDB, ITokenDataBase::class)) {
            throw new APICallerException('в настройках указана база данных, которая не является наследником: ' . ITokenDataBase::class);
        }
        $tokenDB .= '::getInstance';
        $this->tokenDB = $tokenDB();
    }
    
    /**
     * Возращает экземпляр базы данных
     * @return ITokenDataBase
     */
    public function &getTokenDB() : ITokenDataBase
    {
        return $this->tokenDB;
    }
    
    /**
     * Проверяет, существует ли такой тип
     * @param string $class -> Класс
     * @return bool
     */
    public static function has(string $class) : bool
    {
        return isset(static::$handlers[$class]);
    }
    
        
    /**
     * Добавляет новый тип для распознавания при вызове API,
     * если требуется нестандартный обработчик
     * 
     * @param string   $class     -> Класс
     * @param callable $handler   -> Обработчик типа (Должен принимать 1 значение mixed $value)
     * @param string   $exception -> Исключение которое будет формироваться при неправильной передаче аргумента данного типа
     * @param string   $shortName -> Короткое название типа
     * @return void
     * @throws SystemLogicException
     */
    public static function add(string $class, callable $handler, string $exception = null) : void
    {
        static::$handlers[$class] = $handler;
        if($exception) {
            static::addException($class, $exception);
        }
    }
    
    /**
     * Регистрирует исключение которое будет вызвано при непраильной передаче данного типа
     * @param string $class     -> Класс объекта
     * @param string $exception -> Исключение которое будет формироваться при неправильной передаче аргумента данного типа
     * @return void
     * @throws MyInvalidException
     */
    public static function addException(string $class, string $exception) : void
    {
        AutoArgumentException::addTypeException($class, $exception);
    }
        
    /**
     * Проверяет токен и права
     * 
     * @param ReflectionParameter $param -> Параметр
     * @param string              $token -> Значение
     * @return IToken|null NULL, если это не токен
     * 
     * @throws AccessTokenException  -> Формируется, если токен не владеет подписью для необходимых разрешений
     * @throws ExpiredTokenException -> Формируется, если токен истек
     * @throws BadTokenException     -> Формируется, если токен не найден или задан не верно
     */
    public static function checkArgOfToken(ReflectionParameter $param, $token) : ?IToken
    {
        if(!$param->hasType()) {
            return null;
        }
        
        $class = (string)$param->getType();
        
        // Проверяем наследие базового типа токена
        if(!is_subclass_of($class, IToken::class)) {
            return null;
        }
        
        // Извлекаем токен из базы данных
        if(is_string($token)) {
            $token = static::getInstance()->tokenDB->get($token, $class);
        }

        // Токен распознан
        if($token instanceof IToken) {

            // Токен истек
            if($token->isExpired()) {
                throw new ExpiredTokenException();
            }

            // Проверяем полномочия
            if(!$token->checkPermissions()) {
                throw new AccessTokenException();
            }
            
            return $token;
        }

        throw new BadTokenException();
    }
    
    /**
     * Проверяет соответствие Enum
     * 
     * @param ReflectionParameter $param -> Параметр
     * @param string              $value -> Значение
     * @return Enum|null Null, если это не Enum
     * 
     * @throws EnumException -> Неверный формат Enum
     */
    public static function checkArgOfEnum(ReflectionParameter $param, $value) : ?Enum
    {
        if(!$param->hasType()) {
            return null;
        }
        
        $class = (string)$param->getType();
        
        // Проверяем наследие базового типа токена
        if(!is_subclass_of($class, Enum::class)) {
            return null;
        }
        
        // Передан объект
        if(is_object($value)) {
            $className = get_class($value);
            if($className !== false) {

                // Класс соответствует указанному
                if($className == $class) {
                    return $value;
                }

                throw new EnumException($param->name);
            }
        }
        
        // Определеям значение,
        // если оно существует возвращаем его
        $hasValueFn  = "$class::enumHasValue";
        $hasValueVal = $hasValueFn($value);
        if($hasValueVal !== null) {
            return $hasValueVal;
        }
        
        throw new EnumException($param->name);
    }
    
    /**
     * Обработчик типа по умолчанию
     * @param ReflectionParameter $param   -> Параметр
     * @param string              $type    -> Тип
     * @param array               $value   -> Структура
     * @param bool                $convert -> Не строгая типизация
     * @return mixed
     */
    public static function defaultHandler(ReflectionParameter $param, string $type, $value,  bool $convert = false)
    {        
        return Caller::getFixArgsForClassFunc(
            $type,
            static::ClassConstructorMethod,
            $value,
            $convert
        );
    }
    
    /**
     * Преобразует параметр в нужный тип
     * 
     * !!! В данной функции реализована подставка параметров, типа Object
     * 
     * Описывайте в конструктарах класса точные типы аргументов
     * 
     * @param ReflectionParameter $param    -> Объект параметра
     * @param mixed               $value    -> Значение
     * @param bool                $convert  -> Преобразовать тип под подходящий
     * @throws ParametrException
     */
    private static function getArgFixValue(ReflectionParameter $param, $value, bool $convert = false)
    {
        $originalParamType = $param->hasType() ? (string)$param->getType() : null;
        $paramName         = $param->name;
        $paramType         = TypeManager::argTypeToITypeStandart($param);
        $paramBuiltin      = $param->hasType() && $param->getType()->isBuiltin();

        $convertedType  = null;
        $convertedValue = null;
                
        echo "$paramName | $paramType | $paramBuiltin | $originalParamType" . PHP_EOL;
        
        switch($paramType) {

            case IType::TMixed:
                
                // Если тип не задан,
                // передаем значение как есть
                $convertedType  = IType::TMixed;
                $convertedValue = $value;
                
            break;
            
            case IType::TBool:
                
                $convertedType  = IType::TBool;
                $convertedValue = TypeManager::anyToBool($value);
                if($convertedValue === null) {
                    throw new BooleanException($paramName);
                }
                
            break;
            
            case IType::TBoolArray:

                // Определяем, является ли значение массивом
                // Определяем спец тип массива и сравниваем его с указанным
                if(TypeManager::detectBaseType($value, $convertedValue, $convertedType, $convert)
                && $convertedType === IType::TArray) {
                    
                    $convertedType = TypeManager::detectArrType(
                        $convertedValue,
                        $convertedValue
                    );
                    
                    // Тип массива совпал
                    if($convertedType == IType::TBoolArray) {
                        break;
                    }
                }
                
                throw new BooleanArrayException($paramName);
            
            case IType::TArray:

                // Если не удалось определить тип, или передаваемый тип не совпадает с заданным
                // Возвращаем ошибку передачи параметра
                if(TypeManager::detectBaseType($value, $convertedValue, $convertedType, $convert)) {
                    
                    // Типы совпадают
                    if($convertedType == IType::TArray) {
                       break;
                    }
                }
                
                throw new ArrayException($paramName);
                
            case IType::TInteger:
            case IType::TDouble:
            case IType::TString:
                
                // Если не удалось определить тип, или передаваемый тип не совпадает с заданным
                // Возвращаем ошибку передачи параметра
                if(TypeManager::detectBaseType($value, $convertedValue, $convertedType, $convert)
                && $paramType == $convertedType) break;
                
                throw AutoArgumentException::of($paramName, $paramType);
                
            case IType::TNumeric:
                
                // Преобразовываем в integer|double
                if(TypeManager::detectBaseType($value, $convertedValue, $convertedType, $convert)
                && ($convertedType == IType::TDouble || $convertedType == IType::TInteger)
                ) break;
                
                throw AutoArgumentException::of($paramName, $paramType);
                
            case IType::TUnsignedNumeric:
                
                // Значение имеет тип не отрицательного числа
                if(TypeManager::detectBaseType($value, $convertedValue, $convertedType, $convert)
                && $convertedValue >= 0
                && ($convertedType == IType::TDouble || $convertedType == IType::TInteger)
                ) break;
                
                throw AutoArgumentException::of($paramName, $paramType);
                
            case IType::TUnsignedInteger:
            case IType::TUnsignedDouble:
                
                // Значение имеет тип не отрицательного числа
                if(TypeManager::detectBaseType($value, $convertedValue, $convertedType, $convert)
                && $convertedValue >= 0 && (
                    ($convertedType === IType::TDouble && $paramType === IType::TUnsignedDouble) ||
                    ($convertedType === IType::TInteger && $paramType === IType::TUnsignedInteger)
                )) break;
                
                throw AutoArgumentException::of($paramName, $paramType);
                
            case IType::TDoubleArray:
            case IType::TIntegerArray:
            case IType::TNumericArray:
            case IType::TUnsignedDoubleArray:
            case IType::TUnsignedIntegerArray:
            case IType::TUnsignedNumericArray:
                                
                // Определяем, является ли значение массивом
                // Определяем спец тип массива и сравниваем его с указанным
                if(TypeManager::detectBaseType($value, $convertedValue, $convertedType, $convert)) {
                    
                    // Значение определилось как массив
                    if($convertedType == IType::TArray) {
                        
                        $convertArrValue = null;
                        $convertArrType  = TypeManager::detectArrType($convertedValue, $convertArrValue);
                        
                        // Точный тип массива является дочерним для базового
                        if($convertArrType !== null
                        && TypeManager::instanteOf($convertArrType, $paramType)) {
                            $convertedType  = $convertArrType;
                            $convertedValue = $convertArrValue;
                            break;
                        }
                    }
                }
                
                throw AutoArgumentException::of($paramName, $paramType);
            
            default:
                                
                // Создает объект из массива
                // Проверка организована только на уровне передачи параметров
                // Для улучшения защиты указывайте типы аргументов функции
                if(!$paramBuiltin
                && class_exists($originalParamType)) {
                    
                    ////////////////////////////////////////////////////////////
                    ////////////////////////////////////////////////////////////
                    ////////////////////////////////////////////////////////////
                    
                    // Проверка токена
                    $token = static::checkArgOfToken($param, $originalParamType, $value);
                    if($token) {
                        return $token;
                    }

                    // Проверка Enum
                    $enum = static::checkArgOfEnum($param, $originalParamType, $value);
                    if($enum) {
                        return $enum;
                    }

                    // Если передан объект, то сразу сравниваем тип
                    if(is_object($value)) {

                        // Проверяем объект на соответствие
                        if($value instanceof $originalParamType) {
                            return $value;
                        }

                        throw AutoArgumentException::of($param->name, $originalParamType);
                    }
                    
                    ////////////////////////////////////////////////////////////
                    ////////////////////////////////////////////////////////////
                    ////////////////////////////////////////////////////////////
                    
                    try {
                    
                        if(isset(static::$handlers[$originalParamType])) {
                            
                            $handler = static::$handlers[$originalParamType];
                            $object  = $handler(
                                $param,
                                $value,
                                $convert
                            );
                            
                            if($object !== null) {
                                $convertedType  = $param->isVariadic() ? IType::TArray : $originalParamType;
                                $convertedValue = $object;
                                break;
                            }
                            
                            throw AutoArgumentException::of($param->name, $paramType);
                        }
                        
                        // Если указан параметр в виде Variadic цепочки
                        if($param->isVariadic()) {
                            
                            $result = [];
                            foreach($value as $itemKey => $itemValue) {
                                $fixArgs = static::defaultHandler($param, $originalParamType, $itemValue, $convert);
                                $result[$itemKey] = new $originalParamType(...$fixArgs);
                            }
                            ksort($result);
                            
                            $convertedType  = IType::TArray;
                            $convertedValue = $result;
                            break;
                        }
                        
                        $fixArgs = static::defaultHandler($param, $originalParamType, $value, $convert);
                        $convertedType  = $originalParamType;
                        $convertedValue = new $originalParamType(...$fixArgs);
                        break;
                        
                    } catch (TypeError|InvalidArgumentException $ex) {
                        $fixType = DEBUG ? $originalParamType : $paramType;
                        throw new ObjectStructException($param->name, $fixType, $ex->getCode(), $ex);
                    }
                }
                
                throw AutoArgumentException::of($paramName, $paramType);
        }
        
        // Если параметр нужно передать списком,
        // его значение уже было подогнано ранее
        if(!$param->isVariadic() && !$paramBuiltin) {
            
            // Обработчик скалярного типа 
            if(is_subclass_of($originalParamType, IScalar::class)) {
                $convertedType  = $originalParamType;
                $convertedValue = new $originalParamType($convertedValue);
            }
            
            // Обертка скалярного массива
            else if(is_subclass_of($originalParamType, IScalarArray::class)) {
                $convertedType  = $originalParamType;
                $convertedValue = new $originalParamType(...$convertedValue);
            }
        }
        
        // Значение определено
        if($convertedType) {
            return $convertedValue;
        }
        
        throw AutoArgumentException::of($param->name, $paramType);
    }
    
    /**
     * 
     * @param ReflectionParameter $param
     * @param type $value
     * @return type
     * @throws ParametrException
     * @throws UndefinedException
     */
    private static function getArgValue(ReflectionParameter $param, $value, bool $convert = false)
    {
        // Не обязательный параметр
        // Если он не указан, передаем значение по умолчанию
        if($param->isOptional() && $value === null) {
            
            // Variadic не может быть значением по умолчанию,
            // поэтому проверяем доступность значения
            if($param->isDefaultValueAvailable()) {
                
                // Константа
                if($param->isDefaultValueConstant()) {
                    return constant($param->getDefaultValueConstantName());
                }
                
                return $param->getDefaultValue();
            }
            
            throw new UndefinedException($param->name);
        }
        
        // Если параметр не может быть null,
        // говорим, что это обязательный параметр
        if(!$param->allowsNull() && $value === null) {
            throw new UndefinedException($param->name);
        }
        
        return static::getArgFixValue($param, $value, $convert);
    }

    /**
     * Функция возвращает список аргументов,
     * которые преобразованы под тип заданных при объявлении функции.
     * 
     * Оценивается тип аргумента функции и происходит подгон значения под нее
     * 
     * @param string  $funcParams -> Параметры
     * @param array   $args       -> Массив аргументов
     * @param bool    $convert    -> Преобразовать string в другой тип
     * @return mixed
     */
    private static function getFixArgsForParameters($funcParams, array $args, bool $convert = false)
    {
        // Определяем тип ключей
        $isAssocA = TypeManager::arrIsAssoc($args);
        $result   = [];
        $index    = 0;
        
        // Т.к синтаксис PHP не позволяет добавлять аргументы
        // после указания аргумента типа Variadic,
        // мы рассматриваем его отдельно
        foreach($funcParams as $param) {
            
            // Передача агрументов списком
            if($param->isVariadic()) {
                
                $newArgs = [];
                
                if($isAssocA) {
                    foreach($args as $key => $arg) {
                        if($key >= $index) {
                            $newArgs[] = $arg;
                        }
                    }
                }
                else { $newArgs = $args[$param->name] ?? null; }
                
                // Склеиваем в цепочку аргументы
                $tmpRes = static::getArgValue($param, $newArgs, $convert);
                $result = array_merge($result, is_array($tmpRes) ? $tmpRes : [$tmpRes]);
                
                break;
            }
            
            $value    = $isAssocA ? $args[$index] ?? null : $args[$param->name] ?? null;
            $result[] = static::getArgValue($param, $value, $convert);
            $index++;
        }

        return $result;
    }
    
    /**
     * Функция возвращает список аргументов,
     * которые преобразованы под тип заданных при объявлении функции.
     * 
     * Оценивается тип аргумента функции и происходит подгон значения под нее
     * 
     * @param string  $func    -> Название метода
     * @param array   $args    -> Массив аргументов
     * @param bool    $convert -> Преобразовать string в другой тип
     * @return mixed
     */
    public static function getFixArgsForClosure($func, array $args, bool $convert = false)
    {
        // Получаем список переменных функции метода
        $funcInfo   = new ReflectionFunction($func);
        $funcParams = $funcInfo->getParameters();
        return static::getFixArgsForParameters($funcParams, $args, $convert);
    }
    
    /**
     * Функция возвращает список аргументов,
     * которые преобразованы под тип заданных при объявлении функции.
     * 
     * Оценивается тип аргумента функции и происходит подгон значения под нее
     * 
     * @param string  $class   -> Название класса
     * @param string  $func    -> Название метода
     * @param array   $args    -> Массив аргументов
     * @param bool    $convert -> Преобразовать string в другой тип
     * @return mixed
     */
    public static function getFixArgsForClassFunc(string $class, string $func, array $args, bool $convert = false)
    {
        // Получаем список переменных функции метода
        $funcInfo   = new ReflectionMethod($class, $func);
        $funcParams = $funcInfo->getParameters();
        return static::getFixArgsForParameters($funcParams, $args, $convert);
    }
}