<?php

namespace Look\Type;

use Closure;
use JsonSerializable;

/**
 * Базовое представление Enum
 */
abstract class Enum implements JsonSerializable
{
    protected $__enum_value;
    
    /**
     * Перебирает все значения
     * @param callable $callback
     */
    final protected static function values(Closure $callback) : void
    {
        $vars = get_class_vars(static::class);
        foreach($vars as $name => $value) {
            if(strpos($name, '__enum') !== false) {
                continue;
            }
            $callback = $callback->bindTo(null, 'static');
            if($callback($name, $value) === false) {
                return;
            }
        }
    }
    
    /**
     * Проверяет существование значения
     * @param mixed $value Значение
     * @return static|null
     */
    final public static function hasValue($value)
    {
        $return = null;
        $class  = static::class;
        static::values(function($name) use ($class, $value, &$return) {
            if($class::${$name}->getValue() == $value) {
                $return = $class::${$name};
                return false;
            }
        });
        return $return;
    }
    
    /**
     * Проверяет существует значение или нет
     * @param string $name
     * @return bool
     */
    final public static function valueExists(string $name) : bool
    {
        return property_exists(static::class, $name);
    }
    
    /**
     * Возвращает значение
     * @param string $name
     * @return mixed
     */
    final function getValueByName(string $name)
    {
        return static::${$name}->getValue();
    }
    
    /**
     * Возвращает значение
     * @return mixed
     */
    public function getValue()
    {
        return $this->__enum_value;
    }
    
    /**
     * Создает enum объект
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->__enum_value = $value;
    }
    
    /**
     * Обертка возврата значения
     * @return mixed
     */
    public function __invoke()
    {
        return $this->__enum_value;
    }
        
    /**
     * Выполняется при загрузке класса
     */
    public static function __onAutoload() : void
    {
        $class = static::class;
        static::values(function($name, $value) use ($class) {
            $class::${$name} = new $class($value);
        });
    }
    
    /**
     * Sleep
     * @return array
     */
    public function __sleep() : array
    {
        $res  = [];
        static::values(function($name) use (&$res) {
            $res[] = $name;
        });
        return $res;
    }
    
    /**
     * Wake Up
     */
    public function __wakeup() {}
    
    /**
     * Преобразует объект в Json
     * @return mixed
     */
    public function jsonSerialize()
    {
        return $this->__enum_value;
    }
    
    /**
     * Преобразует объект в строку
     * @return string
     */
    public function __toString() : string
    {
        return (string)$this->__enum_value;
    }
}