<?php

try {

    /** @var bool Режим отладки */
    define('DEBUG', true);

    /** @var float Время начала обработки запроса */
    define('APP_START', microtime(true));
    
    require_once __DIR__ . '/../autoload.php';

    // TEST
}
catch (Throwable $ex) { err_log($ex); }
