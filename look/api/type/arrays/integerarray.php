<?php

namespace Look\API\Type\Arrays;

use Look\API\Type\TypeManager;

/**
 * Базовый класс массива состоящего только из целых числел
 */
class IntegerArray extends NumericArray
{
    /** @var bool Конвертация из строки */
    const CanSetString = false;

    /** @var bool Конвертация из значений с плавующей точкой */
    const CanSetFloat = false;
        
    /**
     * Базовый класс массива с хранением integer типа
     * @param int $items -> Элементы массива
     */
    public function __construct(int ...$items)
    {
        parent::__construct(...$items);
    }
    
    /** {@inheritdoc} */
    public function offsetSet($offset, $value)
    {
        $original = $value;
        
        // Преобразуем строку в значение
        if(static::CanSetString && is_string($value)) {
            $value = TypeManager::strToInt($value);
        }
        
        // Преобразуем в целове число
        if(static::CanSetFloat && is_double($value)) {
            $value = (int)$value;
        }
        
        if($value !== false && is_int($value)) {
            
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
    static function __getEvalType(): string { return self::TIntegerArray; }
    
    /** {@inheritdoc} */
    static function __getSystemItemType(): string { return self::TInteger; }
    
    /** {@inheritdoc} */
    static function __getItemEvalType(): string { return self::TInteger; }
    
    /** {@inheritdoc} */
    static function __getScalarItemType(): string { return self::TInteger; }
}