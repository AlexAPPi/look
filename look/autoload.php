<?php

defined('DEBUG') ||
/** @var string Режим отладки */
define('DEBUG', true);

/** @var string Директория сайта /Look */
define('LOOK_DIR', __DIR__);

/** @var string Родительская директория директории движка /look/../ */
define('ROOT_LOOK_DIR', LOOK_DIR . '/..');

defined('ROOT_APP_DIR') ||
/** @var string Родительская директория директории приложения /app/../  */
define('ROOT_APP_DIR', ROOT_LOOK_DIR);

defined('PUBLIC_DIR') ||
/** @var string Путь к публичной директории сайта */
define('PUBLIC_DIR', ROOT_APP_DIR . '/public');

defined('LOG_DIR') ||
/** @var string Папка с логами */
define('LOG_DIR', ROOT_APP_DIR . '/log');

defined('APP_NAME') ||
/** @var string Название приложения */
define('APP_NAME', 'app');

defined('APP_DIR') ||
/** @var string Путь к директории приложения */
define('APP_DIR', ROOT_APP_DIR . '/' . APP_NAME);

/** @var string Путь к директории с API методами */
define('API_DIR', APP_DIR . '/api');

/** @var string Путь к директории с контроллерами страниц */
define('CONTROLLER_DIR', APP_DIR . '/controller');

/** @var string Путь к директории с шаблонами страниц */
define('VIEW_DIR', APP_DIR . '/view');

/** @var string Путь к директории с шаблонами слоев */
define('LAYOUT_DIR', APP_DIR . '/layout');

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

// Инициализирует работу автозагрузчика классов
require LOOK_DIR . '/autoloadmanager.php';
require LOOK_DIR . '/util.php';

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

// Загружаем структуру приложения
include APP_DIR . '/type.php';
include APP_DIR . '/event.php';
include APP_DIR . '/router.php';