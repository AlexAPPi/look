<?php

namespace Look\API\Type\Arrays;

use Look\API\Type\TypeManager;

/**
 * Базовый класс массива состоящего только из положительных числел с плавающей точкой
 */
class UnsignedDoubleArray extends DoubleArray
{
    /** {@inheritdoc} */
    public function offsetSet($offset, $value)
    {
        $original = $value;
        
        // Преобразуем строку в значение
        if(static::CanSetString && is_string($value)) {
            $value = TypeManager::strToDouble($value);
        }
        
        // Преобразуем в целове число
        else if(static::CanSetInt && is_int($value)) {
            $value = (double)$value;
        }
        
        if($value !== false && is_double($value) && $value >= 0) {
            
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
    static function __getEvalType(): string { return self::TUnsignedDoubleArray; }
    
    /** {@inheritdoc} */
    static function __getItemEvalType(): string { return self::TUnsignedDouble; }
}