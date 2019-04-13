<?php

namespace LookPhp\Type;

use LookPhp\Type\Converter;
use LookPhp\Type\TypedArray;

/**
 * Базовый класс массива с хранением bool типа
 */
class BoolArray extends TypedArray
{
    /** @var string Тип подставки данных */
    const ItemType = Converter::TBool;
    
    /** @var string Базовый тип объекта */
    const EvalType = Converter::TBoolArray;
    
    /**
     * @see TypedArray
     */
    public function offsetSet($offset, $value)
    {
        $original = $value;
        $value    = Converter::anyToBool($value);
        
        if($value !== null) {
            
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
