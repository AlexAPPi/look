<?php

namespace Look\API\Type\Arrays;

use Look\API\Type\TypeManager;

/**
 * Базовый класс массива состоящего только из числел
 */
class NumericArray extends ScalarArray
{
    /** @var bool Конвертация из строки */
    const CanSetString = false;
    
    /** {@inheritdoc} */
    public function offsetSet($offset, $value)
    {
        $original = $value;
        
        // Преобразуем строку в значение
        if(static::CanSetString && is_string($value)) {
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
    static function __getEvalType(): string { return self::TNumericArray; }
    
    /** {@inheritdoc} */
    static function __getSystemItemType(): string { return self::TDouble; }
    
    /** {@inheritdoc} */
    static function __getItemEvalType(): string { return self::TDouble; }
    
    /** {@inheritdoc} */
    static function __getScalarItemType(): string { return self::TDouble; }
}
