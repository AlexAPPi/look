<?php

namespace Look\Type;

use Look\Type\Interfaces\IMixedArray;

/**
 * Класс реализующий работу массива
 */
class ObjectArray extends ArrayWrap implements IMixedArray
{
    /** Тип объекта */
    const EvalItemType = self::TObject;
        
    /** {@inheritdoc} */
    final static function __extendsSystemType(): bool { return true; }
    
    /** {@inheritdoc} */
    final static function __checkItemTypeIsInstance() : bool { return true; }
    
    /** {@inheritdoc} */
    final static function __checkItemTypeIsScalar() : bool { return false; }
    
    /** {@inheritdoc} */
    final static function __getSystemEvalType(): string { return self::TArray; }
    
    /** {@inheritdoc} */
    static function __getEvalType(): string { return self::TArray; }
    
    /** {@inheritdoc} */
    static function __getSystemItemType(): string { return self::EvalItemType; }
    
    /** {@inheritdoc} */
    static function __getItemEvalType(): string { return static::EvalItemType; }
    
    /**
     * Присваивает значение заданному смещению (ключу)
     * 
     * @param mixed $offset Ключ
     * @param mixed $value  Значение
     */
    public function offsetSet($offset, $value)
    {
        // Если указан точный тип, проверим его
        if(static::EvalItemType != self::TObject
        && (get_class($value) != static::EvalItemType
        && !is_subclass_of($value, static::EvalItemType)
        && !instanceofTrait($value, static::EvalItemType))) {
            $this->errorOffsetSet($offset, $value);
        }
        
        if (is_null($offset)) {
            $this->m_array[] = $value;
        } else {
            $this->m_array[$offset] = $value;
        }
    }
}