<?php

ob_start();

/** @var bool Режим отладки */
define('DEBUG', true);

/** @var float Время начала обработки API запроса */
define('API_REQUEST_START', microtime(true));

/** @var bool Возможность отправлять callback функцию при ответе на запрос (JSONP) */
define('API_USE_JSONP_CALLBACK', true);

/** @var array Параметры запроса */
$_API_REQUEST = $_REQUEST;

/**
 * Отправляет json
 * 
 * @param array $array        -> Массив
 * @param int   $cacheMaxAge  -> Максимальное время кеша
 * @param bool  $clearHeaders -> Удалять заголовки отправленые ранее
 */
function api_send_json(array $array, int $cacheMaxAge = 1, bool $clearHeaders = true)
{
    global $_API_REQUEST;

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

    if(API_USE_JSONP_CALLBACK && isset($_API_REQUEST['callback'])) {

        $callback = $_API_REQUEST['callback'];

        if(is_string($callback) && strlen($callback) > 0) {
            die($callback.'('.json_encode($array).')');
        }
    }

    die(json_encode($array));
}

/**
 * Преобразует параметры вызова в читаемый вид
 * @return array
 */
function api_request_params() : array
{
    global $_API_REQUEST;

    $requestParams = [];
    $params        = $_API_REQUEST;

    $requestParams[] = [
        'key'   => 'method',
        'value' => basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))
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
 * Ведет лог ошибок
 * 
 * @param Throwable $ex
 * @return int
 */
function api_err_log(Throwable $ex) : int
{
    $time = time();
    
    try
    {
        $dump = '';
        //$dump .= PHP_EOL . '[CODE]:' . PHP_EOL;
        //$dump .= err_get_file_context($ex->getFile(), $ex->getLine()) . PHP_EOL;

        // Подробные данные об ошибке
        if($ex instanceof \Look\Exceptions\SystemException) {
            $dump .= '[TRACE]:' . PHP_EOL;
            $dump .= str_replace("\n", PHP_EOL, $ex->getFullTraceAsString()) . PHP_EOL;
        } else {
            $dump .= '[TRACE]:' . PHP_EOL;
            $dump .= str_replace("\n", PHP_EOL, $ex->getTraceAsString()) . PHP_EOL;
        }
        
        if(defined('LOG_DIR')) {
        
            $date     = date('Y_m_d_H_i', $time);
            $fileName = LOG_DIR . "/api_error_$date.txt";
            $errorStr = sprintf('%s => %s[Error#%s]: %s in %s on line %s%s[URL]: %s%s%s',
                date('d.m.Y H:i:s'), PHP_EOL, $ex->getCode(),
                $ex->getMessage(), $ex->getFile(), $ex->getLine(),
                $dump, $_SERVER['REQUEST_URI'], PHP_EOL, PHP_EOL
            );

            file_put_contents($fileName, $errorStr, FILE_APPEND | LOCK_EX);
        }
        else { echo 'autoload file error'; }
        
    } catch(Throwable $ex) { echo 'error in api'; }

    return $time;
}

/**
 * Выводит ошибку в JSON формате
 * @param type $ex
 */
function api_error_ans(Throwable $ex) : void
{
    $time    = api_err_log($ex);
    $code    = -5121994;
    $mess    = 'fatal server error';

    try
    {
        $matches = [];

        // Ловим ошибки связанные с типами аргументов
        if(preg_match('/Argument ([0-9]*) .*must be of the type (.*), ?(.*) given/', $ex->getMessage(), $matches)) {

            $last     = $ex->getTrace()[0];
            $funcInfo = new ReflectionMethod($last['class'], $last['function']);
            $params   = $funcInfo->getParameters();
            $param    = $params[(int)$matches[1] - 1];
            $newEx    = \Look\Type\Exceptions\AutoArgumentException::of($param->name, (string)$param->getType());

            // Подмена кода и сообщения ошибки
            $code = $newEx->getCode();
            $mess = $newEx->getMessage();                
        }
    }
    catch(Throwable $ex)
    {
        $time = api_err_log($ex);
        $code = -5121994;
        $mess = 'fatal server error';
    }

    api_send_json([
        'error' => [
            'error_code'     => $code,
            'error_msg'      => $mess,
            'request_params' => api_request_params()
        ]
    ]);
}

try
{
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    
    require __DIR__ . '/autoload.php';
    
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    
    $params     = $_API_REQUEST;
    $urlCurrect = \Look\Url\Currect::getInstance();
    \Look\API\Controller::handle($urlCurrect, $params);
}
catch(Throwable $ex) { api_error_ans($ex); }

if(DEBUG && ob_get_length() > 0) {
    echo ob_get_contents();
}

ob_end_clean();