<?php

namespace Look\Type\NoStrict;

use Look\Type\TypeManager;
use Look\Type\Interfaces\INotStrict;
use Look\Type\DoubleArray as StrictDoubleArray;

/**
 * Базовый класс массива состоящего только из числел с плавающей точкой
 */
class DoubleArray extends StrictDoubleArray implements INotStrict
{
    /** {@inheritdoc} */
    public static function convertOffsetValue($value)
    {
        // Преобразуем строку в значение
        if(is_string($value)) {
            $fixValue = TypeManager::strToNumeric($value);
            if($fixValue !== false) {
                return (float)$fixValue;
            }
        }
        else if(is_int($value) || is_bool($value) || is_double($value)) {
            return (float)$value;
        }
        
        return null;
    }
}