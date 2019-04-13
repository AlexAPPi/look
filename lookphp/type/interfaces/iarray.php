<?php

namespace LookPhp\Type\Interfaces;

use Iterator;
use Countable;
use ArrayAccess;
use JsonSerializable;
use LookPhp\Type\Interfaces\IValue;

/**
 * Интерфейс массива
 */
interface IArray extends ArrayAccess, JsonSerializable, Countable, Iterator, IValue
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
}