<?php

namespace Look\Type\NoStrict;

use Look\Type\TypeManager;
use Look\Type\Interfaces\INotStrict;
use Look\Type\UnsignedNumericArray as StrictUnsignedNumericArray;

/**
 * Базовый класс массива состоящего только из положительных числел
 */
class UnsignedNumericArray extends StrictUnsignedNumericArray implements INotStrict
{
    /** {@inheritdoc} */
    public static function convertOffsetValue($value)
    {
        // Преобразуем строку в значение
        if(is_string($value)) {
            $fixValue = TypeManager::strToNumeric($value);
            if($fixValue !== false && $fixValue >= 0) {
                return (float)$fixValue;
            }
        }
        else if(is_int($value) || is_bool($value) || is_double($value)) {
            $value = (float)$value;
            if($value >= 0) {
                return $value;
            }
        }
        
        return null;
    }
}
