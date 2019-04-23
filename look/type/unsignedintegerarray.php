<?php

namespace Look\Type;

/**
 * Базовый класс массива состоящего только из положительных целых числел
 */
class UnsignedIntegerArray extends IntegerArray
{
    /** {@inheritdoc} */
    public static function convertOffsetValue($value)
    {
        return (is_int($value) && $value >= 0) ? $value : null;
    }
    
    /** {@inheritdoc} */
    static function __getEvalType(): string { return self::TUnsignedIntegerArray; }
    
    /** {@inheritdoc} */
    static function __getItemEvalType(): string { return self::TUnsignedInteger; }
}