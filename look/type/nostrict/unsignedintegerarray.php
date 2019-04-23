<?php

namespace Look\Type;

use Look\Type\TypeManager;

/**
 * Базовый класс массива состоящего только из положительных целых числел
 */
class UnsignedIntegerArray extends IntegerArray
{    
    /** {@inheritdoc} */
    public function offsetSet($offset, $value)
    {
        $original = $value;
        
        // Преобразуем строку в значение
        if(static::CanSetString && is_string($value)) {
            $value = TypeManager::strToInt($value);
        }
        
        // Преобразуем в целове число
        else if(static::CanSetFloat && is_double($value)) {
            $value = (int)$value;
        }
        
        if($value !== false && is_int($value) && $value >= 0) {
            
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
    static function __getEvalType(): string { return self::TUnsignedIntegerArray; }
    
    /** {@inheritdoc} */
    static function __getItemEvalType(): string { return self::TUnsignedInteger; }
}