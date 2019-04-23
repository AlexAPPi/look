<?php

namespace Look\Type\NoStrict;

use Look\Type\TypeManager;
use Look\Type\Interfaces\INotStrict;
use Look\Type\IntegerArray as StrictIntegerArray;

/**
 * Базовый класс массива состоящего только из целых числел
 */
class IntegerArray extends StrictIntegerArray implements INotStrict
{
    /** {@inheritdoc} */
    public static function convertOffsetValue($value)
    {
        // Преобразуем строку в значение
        if(is_string($value)) {
            $fixValue = TypeManager::strToNumeric($value);
            if($fixValue !== false) {
                return (int)$fixValue;
            }
        }
        else if(is_bool($value) || is_int($value) || is_double($value)) {
            return (int)$value;
        }
        
        return null;
    }
}