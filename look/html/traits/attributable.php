<?php

namespace Look\Html\Traits;

/**
 * Позволяет объекту иметь атрибуты
 */
trait Attributable
{
    /** @var array */
    protected $attributes = [];
    
    public function attributes() : array
    {
        return $this->attributes;
    }
    
    /**
     * Возвращает значение атрибута
     * @param string $name
     * @return string|null
     */
    public function getAttribute(string $name) : ?string
    {
        return $this->attributes[$name];
    }
    
    /**
     * Устанавливает значение атрибута
     * @param string $name  -> Название
     * @param string $value -> Значение
     * @return void
     */
    public function setAttribute(string $name, string $value) : void
    {
        $this->attributes[$name] = $value;
    }
    
    /**
     * Устанавливает значение атрибутов
     * @param array $attributes -> Атрибуты
     * @return void
     */
    public function setAttributes(array $attributes) : void
    {
        foreach($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }
    }
    
    /**
     * Проверяет, задан ли данный атрибут
     * @param string $name  -> Название
     * @return bool
     */
    public function hasAttribute(string $name) : bool
    {
        return isset($this->attributes[$name]);
    }
    
    /**
     * Удаляет значение атрибута
     * @param string $name  -> Название
     * @return void
     */
    public function unsetAttribute(string $name) : void
    {
        unset($this->attributes[$name]);
    }
    
    /**
     * Преобразует аргументы в строку
     * @param callable $handler -> Конвертер значения
     * @return string|null
     */
    public function attributesToHTML(callable $handler = null) : ?string
    {
        $result = '';
        foreach ($this->attributes as $name => $value) {
            
            if($value === null) {
                continue;
            }
            
            if($handler) {
                $value = $handler($value);
            }
            
            $result .= " $name=\"$value\"";
        }
        return $result;
    }
}