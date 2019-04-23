<?php

namespace Look\Type;

use Look\Type\Exceptions\UnsignedIntegerException;

/**
 * Базовый класс не отрицательного целого числа
 */
class UnsignedInteger extends UnsignedNumeric
{    
    /** {@inheritdoc} */
    public function setValue($value) : void
    {
        if(!is_int($value) || $value < 0) {
            throw new UnsignedIntegerException('value');
        }
        
        $this->m_value = $value;
    }
    
    /** {@inheritdoc} */
    public static function __getEvalType(): string { return self::TUnsignedInteger; }
    
    /** {@inheritdoc} */
    public static function __getSystemEvalType() : string { return self::TInteger; }
}