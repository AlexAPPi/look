<?php

namespace Look\API\Type\Arrays;

use Look\API\Type\Interfaces\IArray;
use Look\API\Type\Exceptions\ArrayTypedException;

/**
 * Обертка для массива
 */
abstract class ArrayWrap implements IArray
{
    /** @var array Массив */
    protected $m_array = [];
    
    /**
     * Базовый класс массива данных
     * 
     * @param mixed $items -> Передаваемые значения
     */
    public function __construct(...$items)
    {
        if($items && is_array($items)) {
            
            foreach($items as $key => $value) {
                $this[$key] = $value;
            }
        }
    }
    
    /** {@inheritdoc} */
    public function __sleep() : array
    {
        return ['m_array'];
    }
    
    /** {@inheritdoc} */
    public function __wakeup() : void
    {
        
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
        return serialize($this->m_array);
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
     * Бросает исключение связанной с неправильным типом
     * 
     * @param mixed $offset -> Смещение
     * @param mixed $value  -> Значение
     * 
     * @throws ArrayTypedException
     */
    protected function errorOffsetSet($offset, $value)
    {
        throw new ArrayTypedException($offset, $value, $this->__getItemEvalType());
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
     * Возвращает ссылку на элемент массива
     * 
     * @param mixed $offset Ключ
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->m_array[$offset];
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
}