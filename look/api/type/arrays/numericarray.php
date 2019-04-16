<?php

namespace Look\API\Type\Arrays;

use Look\API\Type\TypeManager;

/**
 * Базовый класс массива с хранением числового типа
 */
class NumericArray extends ScalarArray
{
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
            $value = TypeManager::strToNumeric($value);
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
    
    /** {@inheritdoc} */
    public function __getItemType(): string
    {
        return self::TNumeric;
    }
    
    /** {@inheritdoc} */
    public function __getScalarItemType(): string
    {
        return self::TFloat;
    }
}
