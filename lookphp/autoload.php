<?php

/** @var string Директория сайта */
define('BASE_DIR', __DIR__);

defined('DEBUG') ||
/** @var string Режим отладки */
define('DEBUG', true);

defined('APP_NAME') ||
/** @var string Название приложения */
define('APP_NAME', 'app');

defined('API_USE_JSONP_CALLBACK') ||
/** @var string API использует jsonp */
define('API_USE_JSONP_CALLBACK', false);

defined('PUBLIC_DIR') ||
/** @var string Путь к публичной директории сайта */
define('PUBLIC_DIR', BASE_DIR . DIRECTORY_SEPARATOR . 'public');

defined('APP_DIR') ||
/** @var string Путь к директории приложения */
define('APP_DIR', BASE_DIR . DIRECTORY_SEPARATOR . APP_NAME);

/** @var string Путь к директории с API методами */
define('API_DIR', APP_DIR  . DIRECTORY_SEPARATOR . 'api');

/** @var string Путь к директории с шаблонами страниц */
define('VIEW_DIR', APP_DIR  . DIRECTORY_SEPARATOR . 'view');

/** @var string Путь к директории с шаблонами слоев */
define('LAYOUT_DIR',     APP_DIR  . DIRECTORY_SEPARATOR . 'layout');

/** @var string Сервер на платформе Window */
define('OS_WINDOW', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

/** @var string Сервер на платформе Linux */
define('OS_LINUX', !OS_WINDOW);

ini_set('default_charset','utf-8');
mb_internal_encoding('utf-8');

if(DEBUG === true) {
    ini_set("display_errors", 'on');
    ini_set('html_errors', 'on');
    error_reporting(E_ALL);
} else {
    ini_set("display_errors", 'off');
    ini_set('html_errors', 'off');
    error_reporting(0); 
}

/**
 * Инициализирует работу автозагрузчика классов
 */
require __DIR__ . '/LookPhp/autoloadmanager.php';

/**
 * Возвращает IP клиента
 * @return string
 */
function get_client_ip()
{
    try
    {
        return (new \LookPhp\Client\IP\Detector())->get();
    }
    catch(Throwable $ex)
    {
        err_log($ex);
        return 'detect error';
    }
}

/**
 * Ведет лог ошибок
 * 
 * @param string $log -> Строка лога
 * @return int
 */
function dev_log(string $log) : int
{
    $time = time();

    try
    {
        $date     = date('Y_m_d', $time);
        $fileName = __DIR__ . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'dev_' . $date . '.txt';
        $errorStr = sprintf('%s => %s%s%s[URL]: %s%s[USER-AGENT]: %s%s[USER-IP]: %s%s%s',
            date('d.m.Y H:i:s'), PHP_EOL, $log, PHP_EOL,
            $_SERVER['REQUEST_URI'], PHP_EOL,
            $_SERVER['HTTP_USER_AGENT'], PHP_EOL,
            get_client_ip(), PHP_EOL,
            PHP_EOL
        );

        file_put_contents($fileName, $errorStr, FILE_APPEND | LOCK_EX);
    }
    catch(Throwable $ex) {}

    return $time;
}

/**
 * Ведет лог ошибок
 * 
 * @param Throwable $ex -> Вызванное исключение
 * @return int
 */
function err_log(Throwable $ex) : int
{
    $time = time();

    try
    {
        $date     = date('Y_m_d', $time);
        $fileName = __DIR__ . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'error_' . $date . '.txt';
        $errorStr = sprintf('%s => %s[Error#%s|%s]: %s in %s on line %s%s[URL]: %s%s[USER-AGENT]: %s%s[USER-IP]: %s%s%s',
            date('d.m.Y H:i:s'), PHP_EOL, $ex->getCode(), get_class($ex),
            $ex->getMessage(), $ex->getFile(), $ex->getLine(), PHP_EOL,
            $_SERVER['REQUEST_URI'], PHP_EOL,
            $_SERVER['HTTP_USER_AGENT'], PHP_EOL,
            get_client_ip(), PHP_EOL,
            PHP_EOL
        );

        file_put_contents($fileName, $errorStr, FILE_APPEND | LOCK_EX);
    }
    catch(Throwable $ex) {}

    return $time;
}

/**
 * Возвращает часть кода, где произошла ошибка
 * 
 * @param string $file        -> Файл из которого необходимо извлечь фрагмент кода
 * @param int    $line_number -> Линия которую нужно извлечь
 * @param int    $radius      -> Радиус захвата кода
 * @return string
 */
function get_file_context_radius($file, $line_number, $radius = 5)
{
    $context = array();
    if ($file && is_readable($file)) {
        $i = 0;
        foreach (file($file) as $line) {
            $i++;
            if ($i >= $line_number - $radius && $i <= $line_number + $radius) {
                if ($i == $line_number) {
                    $context[] = ' >>'.$i."\t".$line;
                } else {
                    $context[] = '   '.$i."\t".$line;
                }
            }
            if ($i > $line_number + $radius) {
                break;
            }
        }
    }
    return "\n".implode("", $context);
}

/**
 * Отправляет json
 * 
 * @param array $array        -> Массив
 * @param int   $cacheMaxAge  -> Максимальное время кеша
 * @param bool  $clearHeaders -> Удалять заголовки отправленые ранее
 */
function send_json(array $array, int $cacheMaxAge = 1, bool $clearHeaders = true)
{
    if(function_exists('api_send_json')) {
        api_send_json($array, $cacheMaxAge, $clearHeaders);
    }
    
    if($clearHeaders) {
        header_remove();
    }
    
    if(ob_get_length() > 0) {
        if(DEBUG) {
            $array['__API_DEBUG__'] = ob_get_contents();
        }
        ob_end_clean();
    }
    
    header("Cache-Control: max-age=$cacheMaxAge, must-revalidate, proxy-revalidate");
    header("Content-Type: application/javascript");
    
    if(API_USE_JSONP_CALLBACK && isset($_REQUEST['callback'])) {
        
        $callback = $_REQUEST['callback'];

        if(is_string($callback) && strlen($callback) > 0) {
            die($callback.'('.json_encode($array).')');
        }
    }

    die(json_encode($array));
}

/**
 * Аналог instanceofTrait, только проверяет родительский класс
 * @param object|string $class -> Класс или объект
 * @param string        $trait -> Назание трейта
 * @return bool
 */
function instanceofParentTrait($class, string $trait) : bool
{
    $parent = get_parent_class($class);

    if($parent === false) {
        return false;
    }

    return instanceofTrait($parent, $trait);
}

/**
 * Аналог instanceof
 * @param object|string $class -> Класс или объект
 * @param string        $trait -> Назание трейта
 * @return bool
 */
function instanceofTrait($class, string $trait) : bool
{
    $traitList = class_uses($class);
    
    if($traitList === false || empty($traitList)) {
        
        return instanceofParentTrait($class, $trait);
    }
    
    return isset($traitList[$trait]);
}

/**
 * Возращает формат даты с русскими назаниями
 * 
 * @param string $format   -> Формат даты
 * @param mixed $timestamp -> Дата
 * @return string
 */
function rudate($format, $timestamp = 'time()')
{
    $ruSign = array('Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь');
    $ruFull = array('Января', 'Февраля', 'Марта', 'Апреля', 'Майя', 'Июня', 'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря');
    $index  = date('n', $timestamp)-1;
    $hasF   = strpos('F', $format) != -1;
    $hasM   = strpos('M', $format) != -1;

    if(strpos('m', $format) != -1) {
        if($hasF) $format = str_replace('F', $ruFull[$index], $format);
        if($hasM) $format = str_replace('M', substr($ruFull[$index], 0, 3), $format);
    } else {
        if($hasF) $format = str_replace('F', $ruSign[$index], $format);
        if($hasM) $format = str_replace('M', substr($ruSign[$index], 0, 3), $format);
    }

    return date($format, $timestamp);
}

// Обработчик глобальных ошибок
register_shutdown_function(function() {
        
    $error = error_get_last();

    if (is_int($error['type'])) {

        if (in_array($error['type'], array(E_COMPILE_ERROR, E_ERROR, E_CORE_ERROR, E_RECOVERABLE_ERROR, E_PARSE))) {

            $throw = new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
            
            // API handler
            if(defined('API_REQUEST_START')) {
                api_error_ans($throw);
            } else {
                err_log($throw);
            }
        }
    }
});

require_once APP_DIR . '/type.php';
require_once APP_DIR . '/event.php';
require_once APP_DIR . '/router.php';