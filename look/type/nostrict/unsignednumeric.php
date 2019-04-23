<?php

namespace Look\Type\NoStrict;

use Look\Type\UnsignedNumeric as StrictUnsignedNumeric;
use Look\Type\Exceptions\InvalidArgumentException;
use Look\Type\Exceptions\UnsignedNumericException;

/**
 * Базовый класс не отрицательного числа с плавающей точкой
 */
class UnsignedNumeric extends StrictUnsignedNumeric
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
}