<?php

namespace Look\API\Type\Arrays;

/**
 * Базовый класс массива с хранением bool типа
 */
class BoolArray extends ScalarArray
{
    /**
     * Новый bool массив
     * @param bool $items
     */
    public function __construct(bool ...$items)
    {
        parent::__construct($items);
    }
    
    /** {@inheritdoc} */
    public function offsetSet($offset, $value)
    {
        $original = $value;
        $value    = TypeManager::anyToBool($value);
        
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
    
    /** {@inheritdoc} */
    public function __getItemType(): string
    {
        return self::TBool;
    }
    
    /** {@inheritdoc} */
    public function __getScalarItemType(): string
    {
        return self::TBool;
    }
}
