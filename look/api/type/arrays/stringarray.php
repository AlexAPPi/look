<?php

namespace Look\API\Type\Arrays;

/**
 * Базовый класс массива состоящего только из строк
 */
class StringArray extends ScalarArray
{
    /**
     * Базовый класс массива данных
     * 
     * @param string $items -> Элементы массива
     */
    public function __construct(string ...$items)
    {
        parent::__construct(...$items);
    }
    
    /** {@inheritdoc} */
    public function offsetSet($offset, $value)
    {
        if(!is_string($value)) {
            $this->errorOffsetSet($offset, $value);
        }
        
        if (is_null($offset)) {
            $this->m_array[] = $value;
        } else {
            $this->m_array[$offset] = $value;
        }
    }
    
    /** {@inheritdoc} */
    static function __getEvalType(): string { return self::TStringArray; }
    
    /** {@inheritdoc} */
    static function __getSystemItemType(): string { return self::TString; }
    
    /** {@inheritdoc} */
    static function __getItemEvalType(): string { return self::TString; }
    
    /** {@inheritdoc} */
    static function __getScalarItemType(): string { return self::TString; }
}