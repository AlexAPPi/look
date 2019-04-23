<?php

namespace Look\Type;

use Look\Type\Interfaces\IUnsigned;
use Look\Type\Exceptions\InvalidArgumentException;
use Look\Type\Exceptions\UnsignedNumericException;

/**
 * Базовый класс не отрицательного числа с плавающей точкой
 */
class UnsignedNumeric extends Numeric implements IUnsigned
{
    /** {@inheritdoc} */
    public function setValue($value) : void
    {
        try { parent::setValue($value); }
        catch (InvalidArgumentException $ex) {
            throw new UnsignedNumericException('value', 0, $ex);
        }
        
        if($this->m_value < 0) {
            throw new UnsignedNumericException('value');
        }
    }
    
    /** {@inheritdoc} */
    public static function __getEvalType(): string { return self::TUnsignedNumeric; }
    
    /** {@inheritdoc} */
    public static function __getSystemEvalType() : string { return self::TDouble; }
}