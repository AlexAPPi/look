<?php

namespace Look\API\Type\Arrays;

use Look\API\Type\TypeManager;

/**
 * Базовый класс массива состоящего только из числел с плавающей точкой
 */
class DoubleArray extends NumericArray
{
    /** @var bool Конвертация из строки */
    const CanSetString = false;

    /** @var bool Конвертация из значений с плавующей точкой */
    const CanSetInt = false;
    
    /**
     * Новый float массив
     * @param float $items
     */
    public function __construct(float ...$items)
    {
        parent::__construct(...$items);
    }
    
    /** {@inheritdoc} */
    public function offsetSet($offset, $value)
    {
        // Преобразуем строку в значение
        if(static::CanSetString && is_string($value)) {
            $value = TypeManager::strToDouble($value);
        }
        
        // Преобразуем в целове число
        else if(static::CanSetInt && is_int($value)) {
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
    
    /** {@inheritdoc} */
    static function __getEvalType(): string { return self::TDoubleArray; }
    
    /** {@inheritdoc} */
    static function __getSystemItemType(): string { return self::TDouble; }
    
    /** {@inheritdoc} */
    static function __getItemEvalType(): string { return self::TDouble; }
    
    /** {@inheritdoc} */
    static function __getScalarItemType(): string { return self::TDouble; }
}