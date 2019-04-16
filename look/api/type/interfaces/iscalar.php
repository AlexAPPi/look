<?php

namespace Look\API\Type\Interfaces;

/**
 * Базовое представление переменной
 */
interface IScalar extends IType
{
    /**
     * Возвращает название скалярного типа из перечня IType
     * 
     * @return string
     */
    static function __getEvalType() : string;
    
    /**
     * Преобразует объект в строку
     * @return string
     */
    function __toString() : string;
    
    /**
     * Устанавливает значение
     * 
     * @param mixed $value -> Значение
     * @return void
     */
    function setValue($value) : void;
    
    /**
     * Возвращает скалярное значение
     * 
     * @return mixed
     */
    function getValue();
}