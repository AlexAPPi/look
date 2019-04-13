<?php

namespace LookPhp\Type\Interfaces;

/**
 * Базовое представление переменной
 * 
 * Для установки типа можно задать константу типа:<br>
 * const <b>EvalType</b> = \LookPhp\Type\Convertor::<b>T*</b>
 */
interface IValue
{
    /**
     * Возвращает название типа
     * 
     * @return string
     */
    static function __getEvalType() : string;
    
    /**
     * Устанавливает значение
     * @param mixed $value -> Значение
     */
    function setValue($value) : void;
    
    /**
     * Возвращает значение
     * 
     * @return mixed
     */
    function getValue();
}