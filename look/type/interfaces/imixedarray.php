<?php

namespace Look\Type\Interfaces;

/**
 * Массив состоящий из разных типов
 */
interface IMixedArray extends IArray
{
    /**
     * Проверяет, является ли элемент массива объектом класса
     * @return bool
     */
    static function __checkItemTypeIsInstance() : bool;
    
    /**
     * Проверяет, является ли элемент массива скалярным типом
     * @return bool
     */
    static function __checkItemTypeIsScalar() : bool;
}