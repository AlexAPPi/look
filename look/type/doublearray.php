<?php

namespace Look\Type;

/**
 * Базовый класс массива состоящего только из числел с плавающей точкой
 */
class DoubleArray extends NumericArray
{
    /** {@inheritdoc} */
    public static function convertOffsetValue($value)
    {
        return is_double($value) ? $value : null;
    }
    
    /** {@inheritdoc} */
    static function __getEvalType(): string { return self::TDoubleArray; }
    
    /** {@inheritdoc} */
    static function __getSystemItemType(): string { return self::TDouble; }
    
    /** {@inheritdoc} */
    static function __getItemEvalType(): string { return self::TDouble; }
    
    /** {@inheritdoc} */
    static function __getScalarItemType(): string { return self::TDouble; }
}