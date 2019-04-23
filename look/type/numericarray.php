<?php

namespace Look\Type;

/**
 * Базовый класс массива состоящего только из числел
 */
class NumericArray extends StrictScalarArray
{
    /** {@inheritdoc} */
    public static function convertOffsetValue($value)
    {
        return (is_double($value) || is_int($value)) ? $value : null;
    }
    
    /** {@inheritdoc} */
    static function __getEvalType(): string { return self::TNumericArray; }
    
    /** {@inheritdoc} */
    static function __getSystemItemType(): string { return self::TDouble; }
    
    /** {@inheritdoc} */
    static function __getItemEvalType(): string { return self::TDouble; }
    
    /** {@inheritdoc} */
    static function __getScalarItemType(): string { return self::TDouble; }
}
