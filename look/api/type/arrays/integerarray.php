<?php

namespace Look\API\Type\Arrays;

use Look\API\Type\TypeManager;
use Look\API\Type\Arrays\NumericArray;

/**
 * Базовый класс массива с хранением integer типа
 */
class IntegerArray extends NumericArray
{
    /** @var bool Конвертация из строки */
    const CanSetString = true;

    /** @var bool Конвертация из значений с плавующей точкой */
    const CanSetFloat = true;
        
    /**
     * Базовый класс массива с хранением integer типа
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
    public function __getItemType(): string
    {
        //return self::;
    }
    
    /** {@inheritdoc} */
    public function __getScalarItemType(): string
    {
        return self::TInteger2;
    }
}