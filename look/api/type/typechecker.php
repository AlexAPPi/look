<?php

namespace Look\API\Type;

use ReflectionClass;
use Look\API\Type\Interfaces\IType;

class TypeChecker
{
    private static $constants = [];
    
    /** @var array Буфер проверки типа */
    private static $classExistsBuf = [
        IType::TInteger  => true,
        IType::TInteger2 => true,
        IType::TDouble   => true,
        IType::TDouble2  => true,
        IType::TArray    => true,
        IType::TBool2    => true,
        IType::TBool     => true,
        IType::TFloat    => true,
    ];
        
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
        if(TypeManager::isScalarType($type)) {
            return true;
        }
        
        // Проверка по классам
        if(class_exists($type)) {
            static::$classExistsBuf[$type] = true;
            return true;
        }
        
        // Получаем все константы класса конвертора
        if(empty(static::$constants)) {
            $refl = new ReflectionClass(IType::class);
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