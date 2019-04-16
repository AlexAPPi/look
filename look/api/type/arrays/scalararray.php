<?php

namespace Look\API\Type\Arrays;

use Look\API\Type\Interfaces\IScalarArray;
use Look\API\Type\Exceptions\ArrayTypedException;

/**
 * Класс реализующий работу массива
 */
class ScalarArray implements IScalarArray
{
    /** @var string Разрешить парсинг строки */
    const ParseStr = true;
    
    /** @var array Массив */
    protected $m_array = [];
    
    /** {@inheritdoc} */
    final static function __getSystemEvalType(): string { return self::TArray; }
    
    /** {@inheritdoc} */
    final static function __extendsSystemType(): bool { return true; }
    
    /** {@inheritdoc} */
    final static function __checkItemTypeIsClass() : bool { return false; }
    
    /** {@inheritdoc} */
    final static function __checkItemTypeIsScalar() : bool { return true; }
    
    /**
     * Базовый класс массива данных
     * 
     * @param bool  $convert -> Парсинг строки
     * @param mixed $items   -> Передаваемые значения
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
    public function __getItemType(): string
    {
        return self::TScalar;
    }
    
    /** {@inheritdoc} */
    public function __getScalarItemType(): string
    {
        return self::TString;
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
     * Бросает исключение связанной с неправильным типом
     * 
     * @param mixed $offset -> Смещение
     * @param mixed $value  -> Значение
     * 
     * @throws ArrayTypedException
     */
    protected function errorOffsetSet($offset, $value)
    {
        throw new ArrayTypedException($offset, $value, $this->__getItemType());
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