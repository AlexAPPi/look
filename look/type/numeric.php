<?php

namespace Look\Type;

use Look\Type\Interfaces\IScalar;
use Look\Type\Exceptions\NumericException;

/**
 * Значение типа integer или double
 */
class Numeric implements IScalar
{
    /** @var double|int Значение */
    protected $m_value = null;
    
    /**
     * Конструктор числа
     * 
     * @param double|integer $value
     * 
     * @throws NumericException -> Возникает, если передано не число
     */
    public function __construct($value)
    {
        $this->setValue($value);
    }
    
    /** {@inheritdoc} */
    public function __toString(): string
    {
        return (string)$this->m_value;
    }
    
    /** {@inheritdoc} */
    public function setValue($value): void
    {
        if(!is_double($value) && !is_int($value)) {
            throw new NumericException('value');
        }
        
        $this->m_value = $value;
    }
    
    /** {@inheritdoc} */
    public function getValue()
    {
        return $this->m_value;
    }
    
    /** {@inheritdoc} */
    public static function __extendsSystemType(): bool { return true; }
    
    /** {@inheritdoc} */
    public static function __getSystemEvalType(): string { return self::TDouble; }
    
    /** {@inheritdoc} */
    public static function __getEvalType(): string { return self::TNumeric; }
}