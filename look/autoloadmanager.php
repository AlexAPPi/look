<?php

namespace Look;

use Throwable;
use Look\API\Type\Interfaces\IType;
use Look\Exceptions\SystemLogicException;

/**
 * Базовый класс менеджера загрузки классов
 * 
 * @author Alexandr Shamarin <alexsandrshamarin@yandex.ru>
 */
class AutoLoadManager
{
    /** Название метода, который вызывается при загрузке класса */
    const onAutoloadMethod = '__onAutoload';
    
    /** @var array Доступные подгонки типа */
    private static $convert = ['.' => 'dot', '-' => 'slash'];
    
    /** Private */
    private function __construct() {}
    
    /**
     * Преобразует имя класса в поддерживаемый вид
     * 
     * Заменяет спец символы на доступные
     * 
     * Например: \Look\Some-Class.SameType => \Look\SomeSlashClassDotSameType
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
     * Строит путь к файлу
     * @param string $globalDir  -> Папка
     * @param string $globalName -> Название
     * @return string
     */
    protected static function buildPath(string $globalDir, string $globalName)
    {
        $lglobalName = strtolower($globalName);
        $path        = str_replace('\\', DIRECTORY_SEPARATOR, $lglobalName);
        $prevPath    = $path . '.php';
        $basePath    = $globalDir . DIRECTORY_SEPARATOR;
        $fullPath    = $basePath . $prevPath;
        
        return $fullPath;
    }

    /**
     * Автоматически подключает указанный файл
     * @param type $globalName
     * @return string
     */
    public static function load(string $globalName) : string
    {
        // Файлы из приложения имеют вес больше, чем файлы движка
        $fullPath = static::buildPath(ROOT_APP_DIR, $globalName);
        if(!file_exists($fullPath)) {
            $fullPath = static::buildPath(ROOT_LOOK_DIR, $globalName);
        }
        
        include $fullPath;
        
        // Вызываем функцию, которая нужна для автозагрузчика
        if(method_exists($globalName, static::onAutoloadMethod)) {
            call_user_func([$globalName, static::onAutoloadMethod]);
        }
        
        // Проводим проверку синтексиса типов
        if(DEBUG) {
            if(is_subclass_of($globalName, IType::class)) {
                
            }
        }
        
        return $fullPath;
    }
}

spl_autoload_register(AutoLoadManager::class . '::load', true, true);