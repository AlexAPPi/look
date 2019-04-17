<?php

namespace Look\API\Type;

use Look\API\Type\TypeManager;
use Look\API\Type\Exceptions\UnsignedDoubleException;

/**
 * Базовый класс не отрицательного числа
 */
class UnsignedDouble extends UnsignedNumeric
{
    /** @var bool Конвертация из int */
    const CanSetInteger = false;
    
    /**
     * Конструктор не отрицательного числа с плавующей точной
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
    public function setValue($value) : void
    {
        // Преобразуем строку в значение
        if(static::CanSetString && is_string($value)) {
            $value = TypeManager::strToInt($value);
        }
        
        // Передано значение double
        else if(static::CanSetInteger && is_int($value)) {
            $value = (float)$value;
        }
        
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