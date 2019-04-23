<?php

namespace Look\Type;

/**
 * Базовый класс массива состоящего только из строк
 */
class StringArray extends StrictScalarArray
{
    /** {@inheritdoc} */
    public static function convertOffsetValue($value)
    {
        return is_string($value) ? $value : null;
    }
    
    /** {@inheritdoc} */
    static function __getEvalType(): string { return self::TStringArray; }
    
    /** {@inheritdoc} */
    static function __getSystemItemType(): string { return self::TString; }
    
    /** {@inheritdoc} */
    static function __getItemEvalType(): string { return self::TString; }
    
    /** {@inheritdoc} */
    static function __getScalarItemType(): string { return self::TString; }
}