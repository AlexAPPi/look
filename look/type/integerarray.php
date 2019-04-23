<?php

namespace Look\Type;

/**
 * Базовый класс массива состоящего только из целых числел
 */
class IntegerArray extends NumericArray
{
    /** {@inheritdoc} */
    public static function convertOffsetValue($value)
    {
        return is_int($value) ? $value : null;
    }
    
    /** {@inheritdoc} */
    static function __getEvalType(): string { return self::TIntegerArray; }
    
    /** {@inheritdoc} */
    static function __getSystemItemType(): string { return self::TInteger; }
    
    /** {@inheritdoc} */
    static function __getItemEvalType(): string { return self::TInteger; }
    
    /** {@inheritdoc} */
    static function __getScalarItemType(): string { return self::TInteger; }
}