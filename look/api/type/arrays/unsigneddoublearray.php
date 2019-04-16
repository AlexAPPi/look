<?php

namespace Look\Type;

use Look\Type\Converter;
use Look\Type\NumericArray;

/**
 * Базовый класс массива с хранением float типа
 */
class UnsignedDoubleArray extends NumericArray
{
    /** @var string Тип подставки данных */
    const ItemType = Converter::TUnsignedDouble;
    
    /** @var string Базовый тип объекта */
    const EvalType = Converter::TUnsignedDoubleArray;
    
    /** @var bool Конвертация из строки */
    const CanSetString = true;

    /** @var bool Конвертация из значений с плавующей точкой */
    const CanSetInt = true;
    
    /**
     * Базовый класс массива с хранением unsigned float типа
     * @param int $items -> Элементы массива
     */
    public function __construct(float ...$items) {
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
            $value = Converter::strToDouble($value);
        }
        
        // Преобразуем в целове число
        if(static::CanSetInt && is_int($value)) {
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
}