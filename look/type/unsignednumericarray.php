<?php

namespace Look\Type;

use Look\Type\Interfaces\IUnsignedArray;

/**
 * Базовый класс массива состоящего только из положительных числел
 */
class UnsignedNumericArray extends NumericArray implements IUnsignedArray
{
    /** {@inheritdoc} */
    public static function convertOffsetValue($value)
    {
        return ((is_double($value) || is_int($value)) && $value >= 0) ? $value : null;
    }
    
    /** {@inheritdoc} */
    static function __getEvalType(): string { return self::TUnsignedNumericArray; }
    
    /** {@inheritdoc} */
    static function __getItemEvalType(): string { return self::TUnsignedNumeric; }
}
