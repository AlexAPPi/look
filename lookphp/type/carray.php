<?php

namespace LookPhp\Type;

use LookPhp\Type\Converter;
use LookPhp\Type\Interfaces\IArray;

/**
 * Класс реализующий работу массива
 */
class CArray implements IArray
{
    /** @var string Базовый тип массива */
    const EvalType = Converter::TArray;
    
    /** @var string Назание класса по умолчанию */
    const DefaultItemType = Converter::TMixed;
    
    /** @var string Тип элемента */
    const ItemType = Converter::TMixed;
    
    /** @var string Разрешить парсинг строки */
    const ParseStr = true;
    
    /** @var array Массив */
    protected $m_array = [];
    
    /**
     * Базовый класс массива данных
     * 
     * @param bool  $convert -> Парсинг строки
     * @param mixed $items   -> Передаваемые значения
     */
    public function __construct(...$items)
    {
        if(isset($items[0])) {
            if(count($items) == 1) {
                // Вытаскиваем массив
                if($items[0] instanceof IArray) { $items = $items[0]->__toArray(); }
                else if(static::ParseStr == true && is_string($items[0]))   {
                    $parse = Converter::strToArray($items[0]);
                    if($parse === false) {
                        // Определяем, является ли значение массивом
                        if(Converter::detectBaseType($items[0], $tmpFix, $tmpType)) {
                            if($tmpType == Converter::TArray) { $items = $tmpFix; }
                            else                              { $items = [$tmpFix]; }
                        }
                    }
                    else {
                        $items = $parse;
                    }
                }
            }
            
            $this->setValue($items);
        }
    }
    
    /**
     * Преобразует объект в массив
     *  
     * @return array
     */
    public function __toArray() : array
    {
        return $this->m_array;
    }
    
    /**
     * Преобразует объект в строку
     *  
     * @return array
     */
    public function __toString() : string
    {
        $str = json_encode($this->m_array);
        return $str === false ? '[]' : $str;
    }
    
    /**
     * Возвращает размер массива
     * 
     * @return int
     */
    public function count() : int
    {
        return count($this->m_array);
    }
    
    /**
     * Возвращает объект списка
     * 
     * @return array
     */
    public function getList()
    {
        return $this->m_array;
    }
    
    /**
     * Устанавливает значение в массив
     * @param mixed $items
     */
    public function setValue($items) : void
    {
        // Добавление элементов
        foreach($items as $key => $value) {
            $this[$key] = $value;
        }
    }
    
    /**
     * Возвращает объект списка
     * 
     * @return array
     */
    public function getValue()
    {
        return $this->m_array;
    }
    
    /**
     * Присваивает значение заданному смещению (ключу)
     * 
     * @param mixed $offset Ключ
     * @param mixed $value  Значение
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->m_array[] = $value;
        } else {
            $this->m_array[$offset] = $value;
        }
    }

    /**
     * Определяет, существует или нет данное смещение (ключ)
     * Данный метод выполняется при использовании isset() или empty() на объектах, реализующих интерфейс ArrayAccess.
     * 
     * @param  mixed $offset Ключ
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->m_array[$offset]);
    }

    /**
     * Удаляет значение данного смещения (ключа)
     * 
     * @param  mixed $offset Ключ
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->m_array[$offset]);
    }

    /**
     * Возвращает заданное смещение (ключ)
     * 
     * @param mixed $offset Ключ
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->m_array[$offset]) ? $this->m_array[$offset] : null;
    }
    
    /**
     * Задает данные, которые должны быть сериализованы в JSON
     * 
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->m_array;
    }
    
    /**
     * Перемотать итератор на первый элемент
     * @return mixed
     */
    public function rewind()
    {
        return reset($this->m_array);
    }
    
    /**
     * Возврат текущего элемента
     * @return mixed
     */
    public function current()
    {
        return current($this->m_array);
    }
    
    /**
     * Возврат ключа текущего элемента
     * @return mixed
     */
    public function key()
    {
      return key($this->m_array);
    }
    
    /**
     * Переход к следующему элементу
     * @return mixed
     */
    public function next()
    {
      return next($this->m_array);
    }
    
    /**
     * Проверяет корректность текущей позиции
     * @return bool
     */
    public function valid()
    {
      return key($this->m_array) !== null;
    }
    
    /**
     * Возвращаемый тип
     * 
     * @return string
     */
    public static function __getEvalType() : string
    {
        return static::EvalType;
    }
    
    /**
     * Возвращаемый тип элемента
     * 
     * @return string
     */
    public static function __getItemType() : string
    {
        return static::ItemType;
    }
}