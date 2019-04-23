<?php

namespace Look\Type\NoStrict;

use Look\Type\TypeManager;
use Look\Type\Interfaces\INotStrict;
use Look\Type\NumericArray as StrictNumericArray;

/**
 * Базовый класс массива состоящего только из числел
 */
class NumericArray extends StrictNumericArray implements INotStrict
{
    /** {@inheritdoc} */
    public static function convertOffsetValue($value)
    {
        // Преобразуем строку в значение
        if(is_string($value)) {
            $value = TypeManager::strToNumeric($value);
        }
        else if(is_bool($value)) {
            $value = (int)$value;
        }
        
        return (is_double($value) || is_int($value)) ? $value : null;
    }
}
