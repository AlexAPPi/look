<?php

namespace LookPhpPhp;

use Exception;

/**
 * Базовый класс менеджера загрузки классов
 * 
 * @author Alexandr Shamarin <alexsandrshamarin@yandex.ru>
 */
class AutoLoadManager
{
    /** @var array Доступные подгонки типа */
    private static $convert = ['.' => 'dot', '-' => 'slash'];
    
    /** Private */
    private function __construct() {}
    
    /**
     * Преобразует имя класса в поддерживаемый вид
     * 
     * Заменяет спец символы на доступные
     * 
     * Например: \LookPhp\Some-Class.SameType => \LookPhp\SomeSlashClassDotSameType
     * 
     * @param string $globalName
     * @return string
     */
    public static function convert(string $globalName) : string
    {
        $result = '';
        $tmp1 = explode('\\', $globalName);
        $className = $tmp1[count($tmp1) -1];
        foreach(static::$convert as $key => $code) {
            if(strpos($className, $key) !== false) {
                array_pop($tmp1);
                $tmp2 = '';
                for($i = 0; $i < count($tmp1); $i++) {
                    $tmp2 .= $tmp1[$i]. '\\';
                }
                $result = $tmp2 . str_replace($key, $code, $className);
            }
        }
        return $result;
    }
    
    /**
     * Автоматически подключает указанный файл
     * @param type $globalName
     * @return string
     */
    public static function load(string $globalName) : string
    {
        $lglobalName = strtolower($globalName);
        $path        = str_replace('\\', DIRECTORY_SEPARATOR, $lglobalName);
        $prevPath    = $path . '.php';
        $basePath    = BASE_DIR . DIRECTORY_SEPARATOR;
        $fullPath    = $basePath . $prevPath;
        
        try {
            require_once $fullPath;
        } catch (Exception $ex) {
            err_log($ex);
            throw $ex;
        }
        
        return $fullPath;
    }
}

spl_autoload_register(__NAMESPACE__ . '\\' . AutoLoadManager::class . '::load', true, true);