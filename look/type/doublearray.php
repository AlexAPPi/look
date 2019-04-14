<?php

namespace Look\Type;

use Look\Type\Converter;
use Look\Type\NumericArray;

/**
 * Базовый класс массива с хранением float типа
 */
class DoubleArray extends NumericArray
{
    /** @var string Тип подставки данных */
    const ItemType = Converter::TDouble;
    
    /** @var string Базовый тип объекта */
    const EvalType = Converter::TDoubleArray;
    
    /** @var bool Конвертация из строки */
    const CanSetString = true;

    /** @var bool Конвертация из значений с плавующей точкой */
    const CanSetInt = true;
    
    /**
     * Базовый класс массива с хранением float типа
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
        // Преобразуем строку в значение
        if(static::CanSetString && is_string($value)) {
            $value = Converter::strToDouble($value);
        }
        
        // Преобразуем в целове число
        if(static::CanSetInt && is_int($value)) {
            $value = (double)$value;
        }
        
        if($value !== false && is_double($value)) {
            
            if(is_null($offset)) {
                $this->m_array[] = $value;
            } else {
                $this->m_array[$offset] = $value;
            }

            return;
        }

        $this->errorOffsetSet($offset, $value);
    }
}