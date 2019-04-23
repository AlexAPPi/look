<?php

namespace Look\Type;

use Look\Type\Exceptions\UnsignedDoubleException;

/**
 * Базовый класс не отрицательного числа
 */
class UnsignedDouble extends UnsignedNumeric
{    
    /** {@inheritdoc} */
    public function setValue($value) : void
    {
        if(!is_double($value) || $value < 0) {
            throw new UnsignedDoubleException('value');
        }
        
        $this->m_value = $value;
    }
    
    /** {@inheritdoc} */
    public static function __getEvalType(): string { return self::TUnsignedDouble; }
    
    /** {@inheritdoc} */
    public static function __getSystemEvalType() : string { return self::TDouble; }
}