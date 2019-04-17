<?php

namespace Look\API\Type;

use Look\API\Type\Exceptions\UnsignedIntegerException;

/**
 * Базовый класс не отрицательного целого числа
 */
class UnsignedInteger extends UnsignedNumeric
{
    /** @var bool Конвертация из float */
    const CanSetDouble = false;
    
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
    public function setValue($value) : void
    {
        // Преобразуем строку в значение
        if(static::CanSetString && is_string($value)) {
            $value = TypeManager::strToInt($value);
        }
        
        // Передано значение double
        else if(static::CanSetDouble && is_double($value)) {
            $value = (int)$value;
        }
        
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