<?php

namespace Look\Type;

/**
 * Массив строгой типизации
 */
abstract class StrictScalarArray extends ScalarArray
{
    /**
     * Выполняет подгонку значения
     * 
     * @param mixed $value  Значение
     * @return integer|string|bool|float|null null Если не удалось подогнать значение
     */
    abstract public static function convertOffsetValue($value);
    
    /** {@inheritdoc} */
    public function offsetSet($offset, $value)
    {
        $fixValue = static::convertOffsetValue($value);
        
        if($fixValue === null) {
            $this->errorOffsetSet($offset, $value);
            return;
        }
        
        if(is_null($offset)) {
            $this->m_array[] = $fixValue;
        } else {
            $this->m_array[$offset] = $fixValue;
        }
    }
}