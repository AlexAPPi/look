<?php

namespace Look\API;

use ReflectionParameter;

use Look\Exceptions\SystemLoginException;

use Look\API\Type\Token\IToken;
use Look\API\Type\Token\DataBase\ITokenDataBase;
use Look\API\Type\Token\DataBase\MySQLiTokenDataBase;
use Look\API\Type\Token\Exceptions\BadTokenException;
use Look\API\Type\Token\Exceptions\AccessTokenException;
use Look\API\Type\Token\Exceptions\ExpiredTokenException;

use Look\API\Caller;
use Look\API\Exceptions\APICallerException;
use Look\API\Exceptions\ObjectStructException;
use Look\API\Type\Exceptions\AutoArgumentException;
use Look\API\Type\Exceptions\InvalidArgumentException;

use Look\API\Type\Enum;
use Look\API\Type\Exceptions\EnumException;

/**
 * Обработчик нестандартных типов
 */
class ClassHandler
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
     * @throws SystemLoginException
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
     * Создает новый объект указанного типа
     * 
     * @param ReflectionParameter $param    -> Объект параметра
     * @param mixed               $value    -> Значение
     * @param string              $type     -> Тип объекта
     * @param bool                $convert  -> Преобразовать тип под подходящий
     * @return mixed
     * @throws ParametrException При ошибке создания объекта 
     */
    private static function standartHandler(ReflectionParameter $param, $value, string $type, bool $convert = true)
    {
        try {
            // Если указан параметр в виде Variadic цепочки
            if($param->isVariadic()) {
                
                $result = [];
                foreach($value as $itemKey => $itemValue) {
                    
                    $fixArgs = Caller::getFixArgsForClassFunc(
                        $type,
                        static::ClassConstructorMethod,
                        $itemValue,
                        $convert
                    );
                    
                    $result[$itemKey] = new $type(...$fixArgs);
                }
                ksort($result);
                return $result;
            }

            // Обертываем в массив для возможности подстановки
            if(!is_array($value)) {
                $value = [$value];
            }
            
            $fixArgs = Caller::getFixArgsForClassFunc(
                $type,
                static::ClassConstructorMethod,
                $value,
                $convert
            );
            
            // Проверяем аргументы на соответствие
            return new $type(...$fixArgs);
        }
        // Ловим дочерние ошибки и комбинируем их
        catch (InvalidArgumentException $ex) {
            
            $errMessage = constant("$ex::argumentErrMessage");
            throw new ObjectStructException($param->name, $errMessage, $ex->getCode(), $ex);
        }
    }
    
    /**
     * Проверяет токен и права
     * 
     * @param ReflectionParameter $param -> Параметр
     * @param string              $class -> Класс токена
     * @param string              $token -> Значение
     * @return IToken|null NULL, если это не токен
     * 
     * @throws AccessTokenException  -> Формируется, если токен не владеет подписью для необходимых разрешений
     * @throws ExpiredTokenException -> Формируется, если токен истек
     * @throws BadTokenException     -> Формируется, если токен не найден или задан не верно
     */
    public static function checkArgOfToken(ReflectionParameter $param, string $class, $token) : ?IToken
    {
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
     * @param string              $class -> Класс Enum
     * @param string              $value -> Значение
     * @return Enum|null Null, если это не Enum
     * 
     * @throws EnumException -> Неверный формат Enum
     */
    public static function checkArgOfEnum(ReflectionParameter $param, string $class, $value) : ?Enum
    {        
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
        $hasValueFn  = "$class::hasValue";
        $hasValueVal = $hasValueFn($value);
        if($hasValueVal !== null) {
            return $hasValueVal;
        }
        
        throw new EnumException($param->name);
    }
    
    /**
     *  
     * @param ReflectionParameter $param   -> Параметр
     * @param mixed               $value   -> Значение
     * @param bool                $convert -> Подгон типа
     * @return object|null Null, если объект не распознан
     * @throws InvalidArgumentException
     */
    public static function detect(ReflectionParameter $param, $value, bool $convert = true) : ?object
    {
        // Тип не определен
        if(!$param->hasType()) {
            return null;
        }
        
        $type = (string)$param->getType();
        
        if(isset(static::$handlers[$type])) {
            
            try {
                
                $handler = static::$handlers[$type];
                $object  = $handler($param, $type, $value);
                
                if($object && $object instanceof $type) {
                    return $object;
                }
                
                throw AutoArgumentException::of($param->name, $type);
                
            } catch (InvalidArgumentException $ex) {
                $errMessage = constant("$ex::argumentErrMessage");
                throw new ObjectStructException($param->name, $errMessage, $ex->getCode(), $ex);
            }
        }
        
        // Проверка токена
        $token = static::checkArgOfToken($param, $type, $value);
        if($token) {
            return $token;
        }

        // Проверка Enum
        $enum = static::checkArgOfEnum($param, $type, $value);
        if($enum) {
            return $enum;
        }
        
        if(is_object($value)) {
            
            // Проверяем объект на соответствие
            if($value instanceof $type) {
                return $value;
            }
            
            throw AutoArgumentException::of($param->name, $type);
        }
        
        return static::standartHandler($param, $value, $type, $convert);
    }
}