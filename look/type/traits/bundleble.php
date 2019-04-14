<?php

namespace Look\Type\Traits;

use Closure;

/**
 * Позволяет объекту имет обработчик событий
 */
trait Bundleble
{
    /** @var array Массив глобальных событий */
    protected static $globalBundleList = [];
    
    /** @var array Массив замороженных событий */
    protected static $globalFreezeBundleList = [];
    
    /** @var array Массив замороженных событий */
    protected $singleFreezeBundleList = [];
    
    /**
     * Возвращает исправеное название для связки
     * @param string $eventName -> Название события
     * @return string
     */
    private static function __bundlebleFixName(string $eventName)
    {
        return strtolower($eventName);
    }
    
    /**
     * Регистрирует callback, при выполнении связки
     * 
     * @param string   $bundleName -> Название связки
     * @param callable $callback   -> Функция
     */
    public static function onBundle(string $bundleName, Closure $callback)
    {
        $fix = static::__bundlebleFixName($bundleName);
        
        if(!isset(static::$globalBundleList[$fix])) {
            static::$globalBundleList[$fix] = [];
        }
        
        static::$globalBundleList[$fix][] = $callback;
    }
    
    /**
     * Чистит callback, при выполнении связки
     * 
     * @param string   $bundleName -> Название связки
     * @param callable $callback   -> Функция
     */
    public static function unsetBundle(string ...$bundleName)
    {
        foreach($bundleName as $tmp) {
            $fix = static::__bundlebleFixName($tmp);
            unset(static::$globalBundleList[$fix]);
        }
    }
    
    /**
     * Замораживает выполнение связки у всех наследников
     * 
     * @param string   $bundleName -> Название связки
     */
    public static function freezeBundle(string ...$bundleName)
    {
        foreach($bundleName as $tmp) {
            $fix = static::__bundlebleFixName($tmp);
            static::$globalFreezeBundleList[$fix] = true;
        }
    }
    
    /**
     * Размораживает связку у всех наследников
     * 
     * @param string   $bundleName -> Название связки
     */
    public static function unfreezeBundle(string ...$bundleName)
    {
        foreach($bundleName as $tmp) {
            $fix = static::__bundlebleFixName($tmp);
            static::$globalFreezeBundleList[$fix] = false;
        }
    }
        
    /**
     * Выполняет связку
     * 
     * @param string $bundleName -> Название связки
     * @return $this
     */
    public function execBundle(string ...$bundleName)
    {
        foreach($bundleName as $tmp) {
            
            $fix = static::__bundlebleFixName($tmp);

            if((isset(static::$globalFreezeBundleList[$fix]) && static::$globalFreezeBundleList[$fix] === false) ||
               (isset($this->singleFreezeBundleList[$fix]) && $this->singleFreezeBundleList[$fix] === false)) {
                continue;
            }

            if(isset(self::$globalBundleList[$fix])) {
                $c = count(static::$globalBundleList[$fix]);
                for($i = 0; $i < $c; $i++) {
                    $currect = static::$globalBundleList[$fix][$i];
                    if($currect instanceof Closure) {
                        call_user_func($currect->bindTo($this, static::class));
                    } else {
                        call_user_func($currect);
                    }
                }
            }
        }
        return $this;
    }
    
    /**
     * Морозит локальное событие
     * 
     * @param string $eventName  -> Название события
     * @param bool   $withGlobal -> Заморозить совместно с глобальным
     * @return $this
     */
    public function freezeSingleBundle(string $eventName)
    {
        $fix = static::__bundlebleFixName($eventName);
        $this->singleFreezeBundleList[$fix] = true;
        return $this;
    }
    
    /**
     * Размораживает событие
     * 
     * @param string $eventName  -> Название события
     * @param bool   $withGlobal -> Разморозить совместно с глобальным
     * @return $this
     */
    public function unFreezeSingleBundle(string $eventName)
    {
        $fix = static::__eventableFixEventName($eventName);
        $this->singleFreezeBundleList[$fix] = false;
        return $this;
    }
}