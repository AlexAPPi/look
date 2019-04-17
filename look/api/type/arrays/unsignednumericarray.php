<?php

namespace Look\API\Type\Arrays;

use Look\API\Type\TypeManager;

/**
 * Базовый класс массива состоящего только из положительных числел
 */
class UnsignedNumericArray extends NumericArray
{
    /** {@inheritdoc} */
    public function offsetSet($offset, $value)
    {
        $original = $value;
        
        // Преобразуем строку в значение
        if(static::CanSetString && is_string($value)) {
            $value = TypeManager::strToNumeric($value);
        }
        
        // Определенное типизироанное значение
        // Значение должно быть больше >= 0
        if($value !== false && (is_double($value) || is_int($value)) && $value >= 0) {
            
            if(is_null($offset)) {
                $this->m_array[] = $value;
            } else {
                $this->m_array[$offset] = $value;
            }

            return;
        }

        $this->errorOffsetSet($offset, $original);
    }
    
    /** {@inheritdoc} */
    static function __getEvalType(): string { return self::TNumericArray; }
    
    /** {@inheritdoc} */
    static function __getItemEvalType(): string { return self::TNumeric; }
}
