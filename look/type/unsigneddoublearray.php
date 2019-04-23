<?php

namespace Look\Type;

/**
 * Базовый класс массива состоящего только из положительных числел с плавающей точкой
 */
class UnsignedDoubleArray extends DoubleArray
{
    /** {@inheritdoc} */
    public static function convertOffsetValue($value)
    {
        return (is_double($value) && $value >= 0) ? $value : null;
    }
    
    /** {@inheritdoc} */
    static function __getEvalType(): string { return self::TUnsignedDoubleArray; }
    
    /** {@inheritdoc} */
    static function __getItemEvalType(): string { return self::TUnsignedDouble; }
}