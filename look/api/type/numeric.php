<?php

namespace Look\API\Type;

use Look\API\Type\TypeManager;
use Look\API\Type\Interfaces\IScalar;
use Look\API\Type\Exceptions\NumericException;

/**
 * Значение типа integer или double
 */
class Numeric implements IScalar
{
    /** @var bool Конвертация из строки */
    const CanSetString = false;
    
    /** @var double|int Значение */
    protected $m_value = 0;
    
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
        // Преобразуем строку в значение
        if(static::CanSetString && is_string($value)) {
            $value = TypeManager::strToNumeric($value);
        }
        
        if($value !== false && (is_double($value) || is_int($value))) {
            $this->m_value = $value;
            return;
        }
        
        throw new NumericException('value');
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