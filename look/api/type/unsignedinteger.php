<?php

namespace Look\Type;

use Look\Type\Converter;
use Look\Type\UnsignedNumeric;
use Look\Type\Exceptions\UnsignedIntegerException;

/**
 * Базовый класс не отрицательного числа
 */
class UnsignedInteger extends UnsignedNumeric
{
    /** @var string Базовый тип массива */
    const EvalType = Converter::TUnsignedInteger;
    
    /**
     * Конструктор не отрицательного числа
     * 
     * @param int $value
     * 
     * @throws UnsignedIntegerException -> Возникает при передаче отрицательного числа
     */
    public function __construct(int $value)
    {
        $this->setValue($value);
    }
    
    /**
     * @see IValue
     * @throws UnsignedIntegerException -> Возникает при передаче отрицательного числа
     */
    public function setValue($value)
    {
        if(!is_int($value) || $value < 0) {
            throw new UnsignedIntegerException('value');
        }
        
        $this->m_value = $value;
    }
}