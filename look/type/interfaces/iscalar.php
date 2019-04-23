<?php

namespace Look\Type\Interfaces;

/**
 * Базовое представление переменной
 */
interface IScalar extends IType
{    
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
     * 
     * @throw \InvalidArgumentException
     */
    function setValue($value) : void;
    
    /**
     * Возвращает скалярное значение
     * 
     * @return mixed
     */
    function getValue();
}