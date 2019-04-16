<?php

namespace Look\API\Type;

use Look\API\Type\TypeManager;
use Look\API\Type\NumericArray;

/**
 * Базовый класс массива с хранением числового типа
 */
class UnsignedNumericArray extends NumericArray
{
    /** @var string Тип подставки данных */
    const ItemType = TypeManager::TUnsignedNumeric;
    
    /** @var string Базовый тип объекта */
    const EvalType = TypeManager::TUnsignedNumericArray;
    
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
}
