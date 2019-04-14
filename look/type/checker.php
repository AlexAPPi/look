<?php

namespace Look\Type;

use Closure;
use ReflectionClass;
use Look\Type\Converter;

class Checker
{
    private static $constants = [];
    
    /** @var array Буфер проверки типа */
    private static $classExistsBuf = [
        Converter::TInteger  => true,
        Converter::TInteger2 => true,
        Converter::TDouble   => true,
        Converter::TDouble2  => true,
        Converter::TArray    => true,
        Converter::TBool2    => true,
        Converter::TBool     => true,
        Converter::TFloat    => true,
    ];
    
    /**
     * Проверяет, является ли объект анонимной функцией
     * @param  mixed $closure -> Обхект проверки
     * @return bool
     */
    public static function isClosure($closure)
    {
        return is_object($closure) && ($closure instanceof Closure);
    }
    
    /**
     * Проверяет, существует ли данный тип
     * @param string $type -> Тип данных
     * @return bool
     */
    public static function typeExists(string $type)
    {
        // Проверка буфера
        if(isset(static::$classExistsBuf[$type]) &&
            static::$classExistsBuf[$type] === true) {
            return true;
        }
        
        // Скалярный тип
        if(Converter::isScalarType($type)) {
            return true;
        }
        
        // Проверка по классам
        if(class_exists($type)) {
            static::$classExistsBuf[$type] = true;
            return true;
        }
        
        // Получаем все константы класса конвертора
        if(empty(static::$constants)) {
            $refl = new ReflectionClass(Converter::class);
            static::$constants = $refl->getConstants();
            unset($refl);
        }
        
        // Проверка существования типа из класса конвертора
        foreach(static::$constants as $key => $value)
        {
            if(substr($key, 0, 1) == 'T' && $type == $value) {
                static::$classExistsBuf[$type] = true;
                return true;
            }
        }
        
        static::$classExistsBuf[$type] = false;
        return false;
    }
}