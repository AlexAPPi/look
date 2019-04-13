<?php

namespace LookPhp\Type;

use LookPhp\Type\CArray;
use LookPhp\Type\Converter;
use LookPhp\Type\Exceptions\ArrayTypedException;

/**
 * Реализует работу по сборке массива определенного типа
 * Для реализации работы типизироанных массиов,<br>
 * нужно указать в const класса типы данных из класса <b>\LookPhp\Type\Converter::T*</b><br>
 * 
 * const <b>ItemType</b> -> Назание класса по умолчанию<br>
 * const <b>EvalType</b> -> Базовый тип массива<br>
 * 
 * Например для создания массива типа unsigned int<br><br>
 * const <b>ItemType</b> = \LookPhp\Type\Converter::TUnsignedInteger;<br>
 * const <b>EvalType</b> = \LookPhp\Type\Converter::TUnsignedIntegerArray;<br>
 * 
 * Например для создания массива типа из определенного типа класса A<br><br>
 * const <b>ItemType</b> = \A::class<br>
 * const <b>EvalType</b> = \A::class . ' ' . \LookPhp\Type\Converter::TArray<br>
 *
 * Значение const <b>ItemsArgName</b> = <b>'items'</b> типизированных значений передается под определенным именем аргумента,<br>
 * при формировании констурктора класса, нужно указывать его как аргумент для передачи добавляемых элементов<br>
 * TypedArray->____construct(...<b>$items</b>)
 */
class TypedArray extends CArray
{
    /** @var string Базовый тип массива */
    const EvalType = Converter::TArray;
    
    /** @var string Назание класса по умолчанию */
    const DefaultItemType = Converter::TMixed;

    /** @var string Тип подставки данных */
    const ItemType = Converter::TMixed;
    
    /**
     * Базовый класс типизироанного массива данных
     * 
     * @param mixed $items -> Передаваемые значения
     */
    public function __construct(...$items)
    {
        parent::__construct(...$items);
    }
        
    /**
     * @see CArray
     */
    public function offsetSet($offset, $value)
    {
        $valType = gettype($value);
        
        if($valType == Converter::TObject) {
            $valType = get_class($value);
        }
        
        if(static::ItemType == static::DefaultItemType
        || is_subclass_of($valType, static::ItemType)
        || Converter::compareTypes(static::ItemType, $valType)) {
            
            if(is_null($offset)) {
                $this->m_array[] = $value;
            } else {
                $this->m_array[$offset] = $value;
            }
        }
        else
        {
            $this->errorOffsetSet($offset, $value);
        }
    }
    
    /**
     * Возвращает ошибку связанную с типом
     * 
     * @param mixed $offset -> Смещение
     * @param mixed $value  -> Значение
     * 
     * @throws ArrayTypedException
     */
    protected function errorOffsetSet($offset, $value)
    {
        throw new ArrayTypedException($offset, $value, static::ItemType);
    }
}