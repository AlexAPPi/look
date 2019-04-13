<?php

namespace LookPhp\Type;

use LookPhp\Type\Converter;
use LookPhp\Type\NumericArray;

/**
 * Базовый класс массива с хранением числового типа
 */
class UnsignedNumericArray extends NumericArray
{
    /** @var string Тип подставки данных */
    const ItemType = Converter::TUnsignedNumeric;
    
    /** @var string Базовый тип объекта */
    const EvalType = Converter::TUnsignedNumericArray;
    
    /** @var bool Конвертация из строки */
    const CanSetString = true;
    
    /**
     * @see MList
     */
    public function offsetSet($offset, $value)
    {
        $original = $value;
        
        // Преобразуем строку в значение
        if(is_string($value) && static::CanSetString) {
            $value = Converter::strToNumeric($value);
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
}
