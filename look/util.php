<?php

/**
 * Возвращает IP клиента
 * @return string
 */
function get_client_ip()
{
    try
    {
        return (new \Look\Client\IP\Detector())->get();
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
        $fileName = LOG_DIR . DIRECTORY_SEPARATOR . 'dev_' . $date . '.txt';
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
        $fileName = LOG_DIR . DIRECTORY_SEPARATOR . 'error_' . $date . '.txt';
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
        if($hasF) { $format = str_replace('F', $ruFull[$index], $format); }
        if($hasM) { $format = str_replace('M', substr($ruFull[$index], 0, 3), $format); }
    } else {
        if($hasF) { $format = str_replace('F', $ruSign[$index], $format); }
        if($hasM) { $format = str_replace('M', substr($ruSign[$index], 0, 3), $format); }
    }

    return date($format, $timestamp);
}