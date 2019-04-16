<?php

namespace Look\API;

use ReflectionMethod;
use ReflectionFunction;
use ReflectionParameter;

use Look\API\Type\TypeManager;
use Look\API\Type\Interfaces\IType;

use Look\API\Type\Exceptions\ArrayException;
use Look\API\Type\Exceptions\BooleanException;
use Look\API\Type\Exceptions\UndefinedException;
use Look\API\Type\Exceptions\BooleanArrayException;
use Look\API\Type\Exceptions\AutoArgumentException;

/**
 * Реализует интерфейс API обращение к функциям с помощью данных
 * Конвертирует данные и создает аргументы для вызова
 */
class Caller
{
    use \Look\Type\Traits\Singleton;
    use \Look\Type\Traits\Settingable;
    
    /**
     * Преобразует тип параметра к сестемному
     * @param ReflectionParameter $param -> Объект параметра
     * @return string
     */
    public static function argTypeToSystem(ReflectionParameter $param) : string
    {
        if($param->hasType()) {
            
            $type = (string)$param->getType();
            
            switch($type) {
                case 'int'   :    return IType::TInteger;
                case 'float' :    return IType::TDouble;
                case 'bool'  :    return IType::TBool;
                case 'array':     return IType::TArray;
                case 'string' :   return IType::TString;
                case 'object' :   return IType::TObject;
                case 'iterable' : return IType::TIterable;
                default : break;
            }
            
            if(class_exists($type)) {
                return IType::TObject;
            }
        }
        
        return IType::TMixed;
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
    private static function getArgFixValue(ReflectionParameter $param, $value, bool $convert = true)
    {
        $paramName = $param->name;
        $paramType = static::argTypeToSystem($param);
        $originalParamType = $param->hasType() ? (string)$param->getType() : null;
                
        $convertedType  = null;
        $convertedValue = null;
                
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
                
                // TODO WRAP_ARRAY
                
                // Если передан 1 параметр с типом bool
                // обертываем его в массив
                $convertedValue = TypeManager::anyToBool($value);
                if($convertedValue !== null) {
                    $convertedType  = IType::TBoolArray;
                    $convertedValue = [$convertedValue];
                    break;
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
                    
                    // TODO WRAP_ARRAY
                    
                    // Заворачиваем в массив
                    $convertedType  = IType::TArray;
                    $convertedValue = [$convertedValue];
                    break;
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
                    else {
                                        
                        // TODO WRAP_ARRAY

                        // Извлекаем значение типа элемента массива
                        // Если типы совпадают или проходят по подгонке
                        $convertItemType = TypeManager::extractArrayTypeItem($paramType);
                        if($convertItemType !== null) {
                            if(TypeManager::instanteOf($convertedType, $convertItemType)) {
                                $convertedType  = $paramType;
                                $convertedValue = [$convertedValue];
                                break;
                            }
                        }
                    }
                }
                
                throw AutoArgumentException::of($paramName, $paramType);

            // TODO:
            // Callable не поддерживается (v1.1)

            case IType::TCallable:
                
                throw AutoArgumentException::of($paramName, $paramType);
                
            default:
                
                // Создает объект из массива
                // Проверка организована только на уровне передачи параметров
                // Для улучшения защиты указывайте типы аргументов функции
                if($originalParamType && class_exists($originalParamType)) {
                                        
                    $convertedType  = $paramType;
                    $convertedValue = ClassHandler::detect($param, $value, $convert);
                    
                    if($convertedValue) {
                        break;
                    }
                }
                
                throw AutoArgumentException::of($paramName, $paramType);
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
    private static function getArgValue(ReflectionParameter $param, $value, bool $convert = true)
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
    private static function getFixArgsForParameters($funcParams, array $args, bool $convert = true)
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
                        if($index == 0 || $index > $key)
                            $newArgs[] = $arg;
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
    public static function getFixArgsForClosure($func, array $args, bool $convert = true)
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
    public static function getFixArgsForClassFunc(string $class, string $func, array $args, bool $convert = true)
    {
        // Получаем список переменных функции метода
        $funcInfo   = new ReflectionMethod($class, $func);
        $funcParams = $funcInfo->getParameters();
        return static::getFixArgsForParameters($funcParams, $args, $convert);
    }
}