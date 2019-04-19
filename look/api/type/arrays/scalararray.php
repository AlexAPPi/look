<?php

namespace Look\API\Type\Arrays;

use Look\API\Type\Interfaces\IScalarArray;

/**
 * Класс реализующий работу массива состоящего только из скалярных типов
 */
class ScalarArray extends ArrayWrap implements IScalarArray
{    
    /** {@inheritdoc} */
    final static function __extendsSystemType(): bool { return true; }
    
    /** {@inheritdoc} */
    final static function __getSystemEvalType(): string { return self::TArray; }
    
    /** {@inheritdoc} */
    static function __getEvalType(): string { return self::TScalarArray; }
    
    /** {@inheritdoc} */
    static function __getSystemItemType(): string { return self::TString; }
    
    /** {@inheritdoc} */
    static function __getItemEvalType(): string { return self::TScalar; }
    
    /** {@inheritdoc} */
    static function __getScalarItemType(): string { return self::TString; }
    
    /**
     * Базовый класс массива данных
     * 
     * @param mixed $items   -> Передаваемые значения
     */
    public function __construct(...$items)
    {
        parent::__construct(...$items);
    }
    
    /**
     * Присваивает значение заданному смещению (ключу)
     * 
     * @param mixed $offset Ключ
     * @param mixed $value  Значение
     */
    public function offsetSet($offset, $value)
    {
        if(!is_scalar($value)) {
            $this->errorOffsetSet($offset, $value);
        }
        
        if (is_null($offset)) {
            $this->m_array[] = $value;
        } else {
            $this->m_array[$offset] = $value;
        }
    }
}