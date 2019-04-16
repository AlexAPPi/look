<?php

namespace Look\API\Type\Interfaces;

/**
 * Интерфейс скалярного массива
 */
interface IScalarArray extends IArray
{
    /**
     * Скалярный тип элемента (bool, string, integer, double, ...)
     * 
     * @return string
     */
    static function __getScalarItemType() : string;
}