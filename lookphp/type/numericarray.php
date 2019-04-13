<?php

namespace LookPhp\Type;

use LookPhp\Type\Converter;
use LookPhp\Type\TypedArray;

/**
 * Базовый класс массива с хранением числового типа
 */
class NumericArray extends TypedArray
{
    /** @var string Тип подставки данных */
    const ItemType = Converter::TNumeric;
    
    /** @var string Базовый тип объекта */
    const EvalType = Converter::TNumericArray;
    
    /** @var bool Конвертация из строки */
    const CanSetString = true;
    
    /**
     * @see TypedArray
     */
    public function offsetSet($offset, $value)
    {
        $original = $value;
        
        // Преобразуем строку в значение
        if(is_string($value) && static::CanSetString) {
            $value = Converter::strToNumeric($value);
        }
        
        if($value !== false && (is_double($value) || is_int($value))) {
            
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
