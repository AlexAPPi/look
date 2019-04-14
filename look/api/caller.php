<?php

namespace Look\API;

use ReflectionMethod;
use ReflectionFunction;
use ReflectionParameter;

use Look\Exceptions\InvalidArgumentException;

use Look\Type\Converter;
use Look\Type\Exceptions\UndefinedException;
use Look\Type\Exceptions\AutoArgumentException;

use Look\Token\IToken;
use Look\Token\DB\ITokenDataBase;
use Look\Token\DB\FSTokenDataBase;

use Look\Token\Exceptions\BadTokenException;
use Look\Token\Exceptions\AccessTokenException;
use Look\Token\Exceptions\ExpiredTokenException;

use Look\Type\Exceptions\ArrayException;
use Look\Type\Exceptions\BooleanException;
use Look\Type\Exceptions\BooleanArrayException;

use Look\API\Exceptions\APICallerException;
use Look\API\Exceptions\ObjectStructException;

use Look\Type\Interfaces\IValue;

/**
 * Реализует интерфейс API обращение к функциям с помощью данных
 * Конвертирует данные и создает аргументы для вызова
 */
class Caller
{
    use \Look\Type\Traits\Singleton;
    use \Look\Type\Traits\Settingable;
    
    /** @var ITokenDataBase объект базы данных */
    private $tokenDB;

    /**
     * @throws APICallerException
     */
    private function __construct()
    {
        $tokenDB = $this->getSetting('tokenDB', FSTokenDataBase::class);
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
    public static function &getTokenDB() : ITokenDataBase
    {
        return static::getInstance()->tokenDB;
    }

    /**
     * Добавляет новый тип для распознавания типа
     * @param string $type      -> Тип
     * @param string $class     -> Класс
     * @param string $exception -> Исключение которое будет формироваться при неправильной передаче аргумента данного типа
     * @return void
     * @throws MyInvalidException
     */
    public static function addType(string $type, string $class, string $exception = null) : void
    {
        Converter::addTypeForClass($type, $class);
        if($exception) {
            AutoArgumentException::addTypeException($type, $exception);
        }
    }

    /**
     * Регистрирует исключение которое будет вызвано при непраильной передаче данного типа
     * @param string $type      -> Тип
     * @param string $exception -> Исключение которое будет формироваться при неправильной передаче аргумента данного типа
     * @return void
     * @throws MyInvalidException
     */
    public static function addTypeException(string $type, string $exception, string $class = null) : void
    {
        AutoArgumentException::addTypeException($type, $exception);
        if($class) {
            Converter::addTypeForClass($type, $class);
        }
    }

    /**
     * Проверяет токен и права
     * 
     * @param string             $class -> Класс проверки токена
     * @param string|SimpleToken $token -> Токен
     * @return IToken|null FALSE, если это не токен
     * 
     * @throws AccessTokenException  -> Формируется, если токен не владеет подписью для необходимых разрешений
     * @throws ExpiredTokenException -> Формируется, если токен истек
     * @throws BadTokenException     -> Формируется, если токен не найден или задан не верно
     */
    private static function checkArgOfToken(string $class, $token) : ?IToken
    {
        // Проверяем наследие базового типа токена
        if(!is_subclass_of($class, IToken::class)) {
            return null;
        }
        
        // Извлекаем токен из базы данных
        if(is_string($token)) {
            $token = static::getInstance()->tokenDB->get($token, $class);
        }

        // Токен получен и 
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
     * Возвращает тип преобразования
     * 
     * @param ReflectionParameter $param -> Объект параметра
     * @param string              $сlass -> Класс типа
     * @return string
     */
    private static function getArgFixType(ReflectionParameter $param, &$сlass = null)
    {
        if(!$param->hasType()) {
            return Converter::TMixed;
        }
        
        $type    = (string)$param->getType();
        $newType = Converter::getTypeForClass($type);
        
        // Если нашлось соответсвие типу и классу
        if($newType !== null) {
            $сlass = $type;
            $type  = $newType;
        }
        
        // Тип относится к базовым представлениям (int, string, float, double, ...)
        if(Converter::isScalarType($type)) {
            
            // Конвертируем в массив значений
            // Т.к передача осуществляется списком
            if($param->isVariadic()) {
                return Converter::getArrayTypeFor($type);
            }
            
            return $type;
        }

        return $type;
    }
    
    /**
     * Создает новый объект указанного типа
     * 
     * @param ReflectionParameter $param    -> Объект параметра
     * @param mixed               $value    -> Значение
     * @param string              $type     -> Тип объекта
     * @param bool                $convert  -> Преобразовать тип под подходящий
     * @return mixed
     * @throws ParametrException При ошибке создания объекта 
     */
    private static function createObject(ReflectionParameter $param, $value, $type, bool $convert = true)
    {
        try {
            // Если указан параметр в виде Variadic цепочки
            if($param->isVariadic()) {
                
                $result = [];
                foreach($value as $itemKey => $itemValue) {
                    $result[$itemKey] = new $type(... static::getFixArgsForClassFunc($type, '__construct', $itemValue, $convert));
                }
                ksort($result);
                return $result;
            }

            // Обертываем в массив для возможности подстановки
            if(!is_array($value)) {
                $value = [$value];
            }

            // Проверяем аргументы на соответствие
            return new $type(... static::getFixArgsForClassFunc($type, '__construct', $value, $convert));
        }
        // Ловим дочерние ошибки и комбинируем их
        catch (InvalidArgumentException $ex) {

            // Скрываем название типа подменяя его на object,
            // если это не скалярный тип
            $desc = Converter::getClassForType($type);
            if($desc === null) {
                $desc = Converter::TObject;
            }
            
            throw new ObjectStructException($param->name, $desc, $ex->getCode(), $ex);
        }
    }
    
    /**
     * Использует интерфейс const ItemType для определения типа
     * @param ReflectionParameter $param
     * @param mixed $value
     * @param mixed $defaultType
     * @param mixed $type
     * @return int -> 0 - тип определен, 1 - тип определен и его нужно преобразовывать, 2 - можно вернуть текущее значение
     */
    private static function getArgFixValueHelper(ReflectionParameter $param, &$defaultType, &$type) : int
    {
        $tmpClass = $param->getDeclaringClass();
        
        if($tmpClass) {
            
            $class = $tmpClass->getName();
            
            // Проверка реализации интерфейса TypedArray::ItemType
            if(is_subclass_of($class, IValue::class)) {
                if($param->getDeclaringFunction()->getName() == '__construct') {
                    $itemType = $class.'::ItemType';
                    if(defined($itemType)) {
                        $defaultType = constant($itemType);
                        $type        = Converter::getFixType($defaultType);
                        return 1;
                    }
                }
            }
        }

        // Преобразовываем значение в нужный тип не требуется
        if($param->hasType()) {
            return 0;
        }
        
        return 2;
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
        $type        = null;
        $defaultType = null;
        
        // ItemType interface
        if(static::getArgFixValueHelper($param, $defaultType, $type) === 2) {
            return $value;
        }
        
        // Название агрумента и его передаваемое значение 
        $name         = $param->name;
        $tmpType      = null;
        $tmpFix       = null;
        $classOfType  = null;
        
        if($defaultType === null) {
            $typeObj     = $param->getType();
            $defaultType = (string)$typeObj;
            $fixArgType  = static::getArgFixType($param, $classOfType);
            $type        = Converter::getFixType($fixArgType);
        }
                
        switch($type) {
    
            case '':
            case Converter::TMixed:
            case Converter::TNULL:
                
                $tmpFix = $value;
                
            break;
                    
            case Converter::TBool:

                $tmpFix = Converter::anyToBool($value);
                if($tmpFix === null) {
                    throw new BooleanException($name);
                }
                
            break;
            
            case Converter::TBoolArray:

                // Определяем, является ли значение массивом
                // Определяем спец тип массива и сравниваем его с указанным
                if(Converter::detectBaseType($value, $tmpFix, $tmpType, $convert) && $tmpType === Converter::TArray) {
                                        
                    // Функция detectArrNumeric восзвращает точный тип,
                    // поэтому, если нам нужно определить TIntegerArray, мы игнорируем приставку
                    if(Converter::detectArrNumeric($tmpFix, $tmpFix) === Converter::TBoolArray) {
                        break;
                    }
                }

                // Если передан 1 параметр bool перемещаем его в массив
                $tmpFix = Converter::anyToBool($value);
                if($tmpFix !== null) {
                    $tmpFix = [$tmpFix];
                    break;
                }
                
                throw new BooleanArrayException($name);
            
            case Converter::TArray:

                // Если не удалось определить тип, или передаваемый тип не совпадает с заданным
                // Возвращаем ошибку передачи параметра
                if(Converter::detectBaseType($value, $tmpFix, $tmpType, $convert)) {
                    
                    if(Converter::compareTypes($type, $tmpType)) {
                       break;
                    }
                    
                    // Заворачиваем в массив
                    $tmpFix = [$tmpFix];
                    break;
                }
                
                throw new ArrayException($name);
                
            case Converter::TInteger:
            case Converter::TDouble:
            case Converter::TString:
                
                // Если не удалось определить тип, или передаваемый тип не совпадает с заданным
                // Возвращаем ошибку передачи параметра
                if(Converter::detectBaseType($value, $tmpFix, $tmpType, $convert) &&
                   Converter::compareTypesFunc($type, $tmpType)
                ) break;
                
                throw AutoArgumentException::of($name, $type);

            case Converter::TNumeric:

                // Преобразовываем в int|double
                if(Converter::detectBaseType($value, $tmpFix, $tmpType, $convert) &&
                  ($tmpType === Converter::TDouble || $tmpType === Converter::TInteger)
                ) break;
                
                throw AutoArgumentException::of($name, $type);

            case Converter::TUnsignedNumeric:
                
                // Значение имеет тип не отрицательного числа
                if(Converter::detectBaseType($value, $tmpFix, $tmpType, $convert) && $tmpFix >= 0 &&
                   ($tmpType === Converter::TDouble || $tmpType === Converter::TInteger)
                ) break;
                
                throw AutoArgumentException::of($name, $type);
                
            case Converter::TUnsignedInteger:
            case Converter::TUnsignedDouble:

                // Значение имеет тип не отрицательного числа
                if(Converter::detectBaseType($value, $tmpFix, $tmpType, $convert) && $tmpFix >= 0 && (
                    ($tmpType === Converter::TDouble && $type === Converter::TUnsignedDouble) ||
                    ($tmpType === Converter::TInteger && $type === Converter::TUnsignedInteger)
                )) break;

                throw AutoArgumentException::of($name, $type);

            case Converter::TDoubleArray:
            case Converter::TIntegerArray:
            case Converter::TNumericArray:
            case Converter::TUnsignedDoubleArray:
            case Converter::TUnsignedIntegerArray:
            case Converter::TUnsignedNumericArray:
 
                // Определяем, является ли значение массивом
                // Определяем спец тип массива и сравниваем его с указанным
                if(Converter::detectBaseType($value, $tmpFix, $tmpType, $convert)) {
                    
                    if($tmpType == Converter::TArray) {
                        
                        // Функция detectArrNumeric возвращает точный тип,
                        // поэтому, если нам нужно определить TIntegerArray, мы игнорируем приставку Unsigned
                        $detectArrType = Converter::detectArrNumeric($tmpFix, $tmpFix2);
                        if($detectArrType && Converter::compareSubTypes($detectArrType, $type)) {
                            $tmpFix = $tmpFix2;
                            break;   
                        }
                    }
                    
                    // Извлекаем значение типа элемента массива
                    // Если типы совпадают или проходят по подгонке
                    $tmpTypeItem = Converter::extractArrayTypeItem($type);
                    if($tmpTypeItem !== false) {
                        if(Converter::compareSubTypes($tmpType, $tmpTypeItem)) {
                            $tmpFix = [$tmpFix];
                            break;
                        }
                    }
                }
                                
                throw AutoArgumentException::of($name, $type);

            // TODO:
            // Callable не поддерживается (v1.1)

            case Converter::TCallable:
                
                throw AutoArgumentException::of($name, $type);
                
            default:

                // Создает объект из массива
                // Защита организована только на уровне передачи параметров
                // Для улучшения защиты указывайте типы аргументов функции
                if(class_exists($type)) {
                    
                    $tmpFix   = null;
                    $tmpToken = static::checkArgOfToken($type, $value);
                    
                    if($tmpToken) { $tmpFix = $tmpToken; }
                    else if(is_object($value)) {

                        // Проверяем объект на соответствие
                        if($value instanceof $type) { $tmpFix = $value; }
                        else { throw AutoArgumentException::of($name, $type); }
                    }
                    else {
                        $tmpFix = static::createObject($param, $value, $type, $convert);
                    }

                    break;
                }

                throw AutoArgumentException::of($name, $type);
        }

        // Специальный класс предназначений для
        // типизации аргументов
        if($classOfType !== null) {
            if(class_exists($classOfType)) {
                $tmpFix = new $classOfType(...$tmpFix);
            } else {
                throw AutoArgumentException::of($param->name, $defaultType);
            }
        }
        
        $currectType = gettype($tmpFix);

        // Тип пустой
        // Тип объект
        // Тип совпал с подобранным
        // Аргумент Variadic и тип массива подходит к данному типу
        if(empty($defaultType) ||
           Converter::compareTypesFunc($defaultType, $currectType) ||
           (!$typeObj->isBuiltin() && $currectType == Converter::TObject) ||
           ($param->isVariadic() && Converter::compareTypesFunc($defaultType, Converter::extractArrayTypeItem($type)))
        ) {
            return $tmpFix;
        }
        
        throw AutoArgumentException::of($param->name, $defaultType);
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
        $isAssocA = Converter::arrIsAssoc($args);
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