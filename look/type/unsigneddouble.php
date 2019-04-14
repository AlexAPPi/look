<?php

namespace Look\Type;

use Look\Type\Converter;
use Look\Type\UnsignedNumeric;
use Look\Type\Exceptions\UnsignedDoubleException;

/**
 * Базовый класс не отрицательного числа
 */
class UnsignedDouble extends UnsignedNumeric
{
    /** @var string Базовый тип массива */
    const EvalType = Converter::TUnsignedDouble;
        
    /**
     * Конструктор не отрицательного числа
     * 
     * @param double $value
     * @throws UnsignedDoubleException -> Возникает при передаче отрицательного числа
     */
    public function __construct(float $value)
    {
        $this->setValue($value);
    }
    
    /**
     * @see IValue
     * @throws UnsignedDoubleException -> Возникает при передаче отрицательного числа
     */
    public function setValue($value)
    {
        if(!is_double($value) || $value < 0) {
            throw new UnsignedDoubleException('value');
        }
        
        $this->m_value = $value;
    }
}