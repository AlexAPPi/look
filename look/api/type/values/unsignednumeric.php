<?php

namespace Look\Type;

use Look\API\Type\Interfaces\IScalar;
use Look\API\Type\Exceptions\UnsignedNumericException;

/**
 * Базовый класс не отрицательного числа
 */
class UnsignedNumeric implements IScalar
{
    /** @var double|int Значение */
    protected $m_value = 0;
    
    /**
     * Конструктор не отрицательного числа
     * 
     * @param double|int $value
     * 
     * @throws UnsignedNumericException -> Возникает при передаче отрицательного числа
     */
    public function __construct($value)
    {
        $this->setValue($value);
    }
    
    /**
     * @see IValue
     * @throws UnsignedNumericException -> Возникает при передаче отрицательного числа
     */
    public function setValue($value)
    {
        if((!is_int($value) && !is_double($value)) || $value < 0) {
            throw new UnsignedNumericException('value');
        }
        
        $this->m_value = $value;
    }
    
    /**
     * Возвращает значение
     * @return double|int
     */
    public function getValue()
    {
        return $this->m_value;
    }

    /**
     * @see IValue
     */
    public function __getEvalType() : string
    {
        return self::TUnsignedNumeric;
    }
}