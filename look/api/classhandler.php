<?php

namespace Look\API;

use Look\API\Caller;
use Look\Type\Converter;

use Look\Token\IToken;
use Look\Token\DB\ITokenDataBase;
use Look\Token\DB\MySQLiTokenDataBase;
use Look\Token\Exceptions\BadTokenException;
use Look\Token\Exceptions\AccessTokenException;
use Look\Token\Exceptions\ExpiredTokenException;

use Look\Type\Enum;
use Look\Type\Exceptions\EnumException;

use Look\API\Exceptions\APICallerException;
use Look\API\Exceptions\ObjectStructException;

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
     * Добавляет новый тип для распознавания типа
     * @param string   $type      -> Тип
     * @param string   $class     -> Класс
     * @param callable $handler   -> Обработчик типа (Должен принимать 1 значение mixed $value)
     * @param string   $exception -> Исключение которое будет формироваться при неправильной передаче аргумента данного типа
     * @return void
     * @throws MyInvalidException
     */
    public static function add(string $class, callable $handler, string $exception = null) : void
    {
        static::$handlers[$class] = $handler;
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
    public static function addException(string $type, string $exception, string $class = null) : void
    {
        AutoArgumentException::addTypeException($type, $exception);
        if($class) {
            Converter::addTypeForClass($type, $class);
        }
    }
    
    /**
     * Проверяет токен и права
     * 
     * @param string             $name  -> Название
     * @param string             $class -> Класс проверки токена
     * @param string|SimpleToken $token -> Токен
     * @return IToken|null NULL, если это не токен
     * 
     * @throws AccessTokenException  -> Формируется, если токен не владеет подписью для необходимых разрешений
     * @throws ExpiredTokenException -> Формируется, если токен истек
     * @throws BadTokenException     -> Формируется, если токен не найден или задан не верно
     */
    public static function checkArgOfToken(string $name, string $class, $token) : ?IToken
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
     * Проверяет токен и права
     * 
     * @param string $name  -> Название
     * @param string $class -> Класс Enum
     * @param string $value -> Значение
     * @return Enum|null Null, если это не токен
     * 
     * @throws EnumException -> Неверный формат Enum
     */
    public static function checkArgOfEnum(string $name, string $class, $value) : ?Enum
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

                throw new EnumException($name);
            }
        }
        // Определеям значение,
        // если оно существует возвращаем его
        $hasValueFn  = "$class::hasValue";
        $hasValueVal = $hasValueFn($value);
        if($hasValueVal !== null) {
            return $hasValueVal;
        }
        
        throw new EnumException($name);
    }
    
    public static function detect($param, $value, $type, $convert) {
        
        
                          
        if(is_object($value)) {

            // Проверяем объект на соответствие
            if($value instanceof $type) { return $value; }
            else { throw AutoArgumentException::of($name, $type); }
        }
        else {
            return static::createObject($param, $value, $type, $convert);
        }

        
        // Проверка токена
        $tmpToken = static::checkArgOfToken($type, $value);
        if($tmpToken) {
            $tmpFix = $tmpToken;
            //break;
        }

        // Проверка Enum
        $tmpEnum = static::checkArgOfEnum($name, $type, $value);
        if($tmpEnum) {
            $tmpFix = $tmpEnum;
            //break;
        }
    }
}