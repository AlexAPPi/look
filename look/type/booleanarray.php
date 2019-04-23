<?php

namespace Look\Type;

/**
 * Базовый класс массива состоящего только из boolean
 */
class BooleanArray extends StrictScalarArray
{
    /** {@inheritdoc} */
    public static function convertOffsetValue($value)
    {
        return is_bool($value) ? $value : null;
    }
    
    /** {@inheritdoc} */
    static function __getEvalType(): string { return self::TBoolArray; }
    
    /** {@inheritdoc} */
    static function __getSystemItemType(): string { return self::TBool; }
    
    /** {@inheritdoc} */
    static function __getItemEvalType(): string { return self::TBool; }
    
    /** {@inheritdoc} */
    static function __getScalarItemType(): string { return self::TBool; }
}
