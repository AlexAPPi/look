<?php

namespace LookPhp\Type;

use LookPhp\Type\Converter;
use LookPhp\Type\IntegerArray;

/**
 * Базовый класс массива с хранением integer типа
 */
class UnsignedIntegerArray extends IntegerArray
{
    /** @var string Тип подставки данных */
    const ItemType = Converter::TUnsignedInteger;
    
    /** @var string Базовый тип объекта */
    const EvalType = Converter::TUnsignedIntegerArray;
    
    /** @var bool Конвертация из строки */
    const CanSetString = true;

    /** @var bool Конвертация из значений с плавующей точкой */
    const CanSetFloat = true;
    
    /**
     * Базовый класс массива с хранением unsigned int типа
     * @param int $items -> Элементы массива
     */
    public function __construct(int ...$items) {
        parent::__construct(...$items);
    }
    
    /**
     * @see NumericArray
     */
    public function offsetSet($offset, $value)
    {
        $original = $value;
        
        // Преобразуем строку в значение
        if(static::CanSetString && is_string($value)) {
            $value = Converter::strToInt($value);
        }
        
        // Преобразуем в целове число
        if(static::CanSetFloat && is_double($value)) {
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
}