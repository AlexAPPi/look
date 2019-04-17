<?php

namespace Look\API\Type\Arrays;

use Look\API\Type\TypeManager;

/**
 * Базовый класс массива состоящего только из boolean
 */
class BooleanArray extends ScalarArray
{
    /**
     * Новый bool массив
     * @param bool $items
     */
    public function __construct(bool ...$items)
    {
        parent::__construct(...$items);
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
    static function __getEvalType(): string { return self::TBoolArray; }
    
    /** {@inheritdoc} */
    static function __getSystemItemType(): string { return self::TBool; }
    
    /** {@inheritdoc} */
    static function __getItemEvalType(): string { return self::TBool; }
    
    /** {@inheritdoc} */
    static function __getScalarItemType(): string { return self::TBool; }
}
