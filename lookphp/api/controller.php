<?php

namespace LookPhp\API;

use Throwable;
use Exception;

use LookPhp\Exceptions\SystemException;
use LookPhp\Exceptions\SystemLoginException;
use LookPhp\Exceptions\InvalidArgumentException;

use LookPhp\API\Exceptions\APIException;
use LookPhp\Token\Exceptions\TokenException;

use LookPhp\API\Caller;
use LookPhp\Url\Builder as Url;

use LookPhp\Type\Traits\Singleton;

/**
 * Класс можно вызвать только из api.php файла
 * 
 * [0.1.2] 11.04.2019 Реализована возможность передачи ProtectedData через HTTP заголовок
 * [0.1.1] 12.03.2018 Реализована возможность передачи AccessToken через HTTP заголовок
 * [0.1.0] 03.01.2018 Первая реализация
 */
final class Controller
{
    use Singleton;

    /** Название аргумента, по каторому передается токен */
    const accessTokenArgName    = 'access_token';
    
    /** Новое название аргумента, по каторому передается токен */
    const accessTokenArgNameNew = 'accessToken';

    /** Название заголовка, через который передается токен */
    const accessTokenHeaderName = 'HTTP_X_LookPhp_ACCESS_TOKEN'; //'X-LookPhp-Access-Token';
    
    /** Название аргумента, по которому передается зашифрованное сообщение */
    const protectedDataArgName = 'protected_data';

    /** Новое название аргумента, по которому передается зашифрованное сообщение */
    const protectedDataArgNameNew = 'protectedData';

    /** Название заголока, через который передается зашифрованное сообщение */
    const protectedDataHeaderName = 'HTTP_X_LookPhp_PROTECTED_DATA'; //'X-LookPhp-Protected-Data';

    /** Информация не найдена */
    const codeNotFound      = 404;
    
    /** Класс не найден */
    const codeClassNotFound = 1;
    
    /** Функция не найдена */
    const codeFuncNotFound  = 2;
    
    /**
     * @var array список наименований функций, которые нельзя инспользовать
     * init - функция инициализирующая работу API класса
     */
    private static $protectedFuncAPI = ['init', 'destroy', 'call', '__destroy', '__sleep', '__wakeup', '__get', '__set', '__toString'];
    
    /**
     * @var array список наименований классов, которые нельзя инспользовать
     */
    private static $protectedClassAPI = [];
    
    /**
     * @var array список наименований функций у классов, которые нельзя инспользовать
     */
    private static $protectedClassFuncAPI = [];
    
    /**
     * Устанавливает запрет на обращение к классу
     * @param string $name -> Название класса
     * @return void
     */
    public static function setClassProtect(string $name) : void
    {
        static::$protectedClassAPI[] = strtolower($name);
    }
    
    /**
     * Устанавливает запрет на вызов функции
     * @param string $name -> Название функции
     * @return void
     */
    public static function setFuncProtect(string $name) : void
    {
        static::$protectedFuncAPI[] = strtolower($name);
    }
    
    /**
     * Устанавливает запрет на вызов функции в классе
     * @param string $class -> Функция
     * @param string $func -> Класс
     * @return void
     */
    public static function setClassFuncProtect(string $class, string $func) : void
    {
        if(!isset(static::$protectedClassFuncAPI[$class]))
        {
            static::$protectedClassFuncAPI[$class] = [];
        }
        
        static::$protectedClassFuncAPI[$class][] = strtolower($func);
    }
    
    /**
     * Снимает запрет на вызов функции в классе
     * @param string $name -> Название класса
     * @return void
     */
    public static function unsetClassProtect(string $name) : void
    {
        $c = count(static::$protectedClassAPI);
        for($i = 0; $i < $c; $i++) {
            if(static::$protectedClassAPI[$i] == $name) {
                unset(static::$protectedClassAPI[$i]);
                return;
            }
        }
    }
    
    /**
     * Снимает запрет на вызов функции
     * @param string $name -> Название функции
     * @return void
     */
    public static function unsetFuncProtect(string $name) : void
    {
        $c = count(static::$protectedFuncAPI);
        for($i = 0; $i < $c; $i++) {
            if(static::$protectedFuncAPI[$i] == $name) {
                unset(static::$protectedFuncAPI[$i]);
                return;
            }
        }
    }
    
    /**
     * Снимает запрет на вызов функции в классе
     * @param string $class -> Функция
     * @param string $func -> Класс
     * @return void
     */
    public static function unsetClassFuncProtect(string $class, string $func) : void
    {
        if(isset(static::$protectedClassFuncAPI[$class])) {

            $c = count(static::$protectedClassFuncAPI[$class]);
            for($i = 0; $i < $c; $i++) {
                if(static::$protectedClassFuncAPI[$class][$i] == $func) {
                    unset(static::$protectedClassFuncAPI[$class][$i]);
                    return;
                }
            }
        }
    }
    
    /**
     * Возвращает массив с информацией о входных параметрах к API
     * @param Url   $url    Url
     * @param array $params Параметры
     * @return array
     */
    public static function requestInfo(Url $url, array $params = []) : array
    {
        $requestParams   = [];
        $requestParams[] = [
            'key'   => 'method',
            'value' => $url->getLastSection()
        ];
        
        foreach ($params as $param => $value) {
            $requestParams[] = [
                'key'   => $param,
                'value' => $value
            ];
        }
        
        return $requestParams;
    }
    
    /**
     * Проверяет наименование, которое должно состоять только из букв и цыфр
     * @param string $name
     * @return bool
     */
    public static function validName(string $name) : bool
    {
        if(empty($name)) {
            return false;
        }
        
        $re      = '/([a-zA-Z0-9]*)/';
        $matches = array();
        preg_match($re, $name, $matches);
        
        return $name === $matches[1];
    }
    
    /**
     * Проверяет существует ли такой класс API
     * @param string $name - Наименование класса
     * @return boolean
     */
    public static function apiClassExists(string $name, string &$path = null) : bool
    {  
        $path = null;
        
        if(static::validName($name)) {
            
            // /api/{name}.php
            $path = API_DIR . DIRECTORY_SEPARATOR . $name . '.php';
            
            if (file_exists($path)) {
                
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Проверяет существование функции у данного класса
     * @param string $className Наименование класса
     * @param string $funName Наименование функции
     * @return boolean
     */
    public static function apiFunctionExists(string $className, string $funName) : bool
    {
        if(static::validName($className) && static::validName($funName)) {
            
            return method_exists('\\' . APP_NAME . '\\API\\' . $className, $funName);
        }
        
        return false;
    }
    
    /**
     * Проверяет функцию на доступность при post или get запросе по API
     * @param string $className Наименование класса
     * @return boolean
     */
    public static function apiClassWebAccess(string $className) : bool
    {
        return !in_array(strtolower($className), static::$protectedClassAPI);
    }
    
    /**
     * Проверяет функцию класса на доступность при post или get запросе по API
     * @param string $className Наименование класса
     * @param string $funName Наименование функции
     * @return boolean
     */
    public static function apiClassFuncWebAccess(string $className, string $funName) : bool
    {
        if(empty($className) || empty($funName)) {
            return false;
        }
        
        if(!isset(static::$protectedClassFuncAPI[strtolower($className)])) {
            return true;
        }
        
        return !in_array(strtolower($funName), static::$protectedClassFuncAPI[strtolower($className)]);
    }
    
    /**
     * Проверяет функцию на доступность при post или get запросе по API
     * @param string $funName Наименование функции
     * @return boolean
     */
    public static function apiFunctionWebAccess(string $funName) : bool
    {
        return !in_array(strtolower($funName), static::$protectedFuncAPI);
    }

    /**
     * Возвращает дату последнего редактирования файла
     * @param string $path путь к файлу
     * @return int
     */
    public static function getLastModif(string $path) : int
    {
        return file_exists($path) ? filemtime($path) : -1;
    }

    /**
     * Возвращает ошибку пользователю
     *
     * @param Throwable $ex      Исключение
     * @param integer   $code    Код ошибки (по умолчанию берется из исключения)
     * @param string    $message Сообщение  (по умолчаниб берется из исключения)
     * @param boolean   $log     Записать в лог
     * @return void
     */
    public static function returnError(Throwable $ex, int $code = null, string $message = null, bool $log = true)
    {
        if($log) api_err_log($ex);

        $code    = $code ?? $ex->getCode();
        $message = $message ?? $ex->getMessage();

        api_send_json([
            'error' => [
                'error_code'     => $code,
                'error_msg'      => $message,
                'request_params' => api_request_params(),
                //'time'           => $time
            ]
        ]);
    }
    
    /**
     * Извлекает токен запроса
     * 
     * @return string|null
     */
    public static function extractTokenFromRequest(?array $unfix) : ?string
    {
        // Если токен передан через параметр запроса
        if(isset($unfix[static::accessTokenArgName])) {
            return $unfix[static::accessTokenArgName];
        }

        // Если токен передан через HTTP заголовок
        if(isset($_SERVER[static::accessTokenHeaderName])) {
            return $_SERVER[static::accessTokenHeaderName];
        }
        
        return null;
    }

    /**
     * Извлекает protected data из запроса
     * 
     * @return string|null
     */
    public static function extractProtectedDataFromRequest($unfix) : ?string
    {
        // Если данные переданы через параметр запроса
        if(isset($unfix[static::protectedDataArgName])) {
            return $unfix[static::protectedDataArgName];
        }

        // Если данные переданы через HTTP заголовок
        if(isset($_SERVER[static::protectedDataHeaderName])) {
            return $_SERVER[static::protectedDataHeaderName];
        }
        
        return null;
    }
    
    /**
     * Формирует API ответ
     * Отвечает за обработку запросов к API
     * 
     * @throws APIException
     */
    public static function handle(Url $url, array $params = [])
    {
        try
        {
            // 1 секция должна быть API
            // В запросе может быть только 2 секции
            if($url->getFirstSection() !== 'api' || $url->getSectionCount() != 2) {
                throw new APIException('API request not currect', static::codeNotFound);
            }
            
            $classAndFn = strtolower($url->getLastSection());
            $method     = explode('.', $classAndFn);
            
            $apiClass = strtolower($method[0]);
            $apiFunc  = strtolower($method[1]);
            
            // Защита url
            // Переход возможен только по /api/[class].[function]
            // Проверяем, чтобы название класса и функции было только из английских букв
            if (count($method) > 2 || !static::validName($apiClass) || !static::validName($apiFunc)) {
                throw new APIException('API request not currect', static::codeNotFound);
            }
            
            // Проверка доступности класса
            if (static::apiClassExists($apiClass) && static::apiClassWebAccess($apiClass)) {
                
                // Список функций, запрещенных для открытого доступа
                // Функция не может быть в списке системных или запрещенных
                if (static::apiFunctionExists($apiClass, $apiFunc)
                &&  static::apiFunctionWebAccess($apiFunc)
                &&  static::apiClassFuncWebAccess($apiClass, $apiFunc)
                ) {
                    // Без привязки
                    $unfix = array_merge([], $params);
                    
                    // Извлекаем данные
                    // Зашифрованные данные не могут быть переданы при передаче токена через параметр
                    if(!isset($unfix[static::accessTokenArgNameNew])
                    && !isset($unfix[static::accessTokenArgName])) {
                        $unfix[static::accessTokenArgNameNew]   = static::extractTokenFromRequest($unfix);
                        $unfix[static::protectedDataArgNameNew] = static::extractProtectedDataFromRequest($unfix);
                    }
                    
                    $apiClass = APP_NAME . '\\API\\' . $apiClass;
                    $method   = $apiClass . '::' . $apiFunc;
                    $args     = Caller::getFixArgsForClassFunc($apiClass, $apiFunc, $unfix);
                    $result   = $method(...$args);

                    if($result instanceof ApiResult) {

                        $cacheMaxAge  = $result->cacheMaxAge;
                        $clearHeaders = $result->clearHeaders;
                        $result       = $result->result;

                        // extract value
                        while($result instanceof APIResultable) {
                            $result = $result->toAPIResult();
                        }

                        api_send_json(['response' => $result], $cacheMaxAge, $clearHeaders);
                        return;
                    }

                    // extract value
                    while($result instanceof APIResultable) {
                        $result = $result->toAPIResult();
                    }

                    api_send_json(['response' => $result]);
                }
                else
                {
                    throw new APIException('API function not exists', static::codeFuncNotFound);
                }
            }
            else
            {
                throw new APIException('API class not exists', static::codeClassNotFound);
            }
        }
        catch (APIException|InvalidArgumentException|TokenException $ex)
        {
            static::returnError($ex, $ex->getCode(), $ex->getMessage());
        }
        catch (SystemException|SystemLoginException $ex)
        {
            static::returnError($ex, $ex->getCode(), 'system error');
        }
        catch (Exception|Throwable $ex)
        {
            static::returnError($ex, $ex->getCode(), 'server error');
        }
    }
}