<?php

namespace Look\API\Type\Interfaces;

use Iterator;
use Countable;
use ArrayAccess;
use JsonSerializable;

/**
 * Интерфейс скалярного массива
 */
interface IArray extends ArrayAccess, JsonSerializable, Countable, Iterator, IType
{
    /**
     * Преобразует объект в массив
     *  
     * @return array
     */
    function __toArray() : array;
    
    /**
     * Преобразует объект в строку
     *  
     * @return array
     */
    function __toString() : string;
        
    /**
     * Возвращает название типа элемента массива в соответствии со стандартом ITYPE
     *
     * @return string
     */
    static function __getItemEvalType() : string;
    
    /**
     * Возвращаемый тип элемента в соответствии со стандартом PHP
     * 
     * @return string
     */
    static function __getSystemItemType() : string;
}