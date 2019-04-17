<?php

namespace Look\API\Type\Interfaces;

/**
 * Интерфейс скалярного массива
 */
interface IScalarArray extends IArray
{
    /**
     * Скалярный тип элемента (bool, string, integer, double, ...)
     * в соответствии со стандартом PHP
     * 
     * @return string
     */
    static function __getScalarItemType() : string;
}