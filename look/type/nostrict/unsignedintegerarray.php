<?php

namespace Look\Type\NoStrict;

use Look\Type\TypeManager;
use Look\Type\Interfaces\INotStrict;
use Look\Type\UnsignedIntegerArray as StrictUnsignedIntegerArray;
/**
 * Базовый класс массива состоящего только из положительных целых числел
 */
class UnsignedIntegerArray extends StrictUnsignedIntegerArray implements INotStrict
{    
    /** {@inheritdoc} */
    public static function convertOffsetValue($value)
    {
        // Преобразуем строку в значение
        if(is_string($value)) {
            $fixValue = TypeManager::strToNumeric($value);
            if($fixValue !== false && $fixValue >= 0) {
                return (int)$fixValue;
            }
        }
        else if(is_int($value) || is_bool($value) || is_double($value)) {
            $value = (int)$value;
            if($value >= 0) {
                return $value;
            }
        }
        
        return null;
    }
}