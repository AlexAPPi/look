<?php

namespace Look\Event;

/**
 * Селектор события
 * @author Alexandr Shamarin <alexsandrshamarin@yandex.ru>
 */
class EventSelector
{
    /** Формируется при вызове функции */
    const callEvent         = 'call';
    
    /** Формируется перед вызовов функции */
    const beforeCallEvent   = 'beforeCall';
    
    /** Формируется после вызовова функции */
    const afterCallEvent    = 'afterCall';
    
    /** Формируется перед вызовом функции */
    const beforeReturnEvent = 'beforeReturn';
    
    /** Формируется вовремя возврата функции */
    const onReturnEvent     = 'onReturn';
    
    private $class;
    private $func;
    private $name;
    
    /**
     * Парсит строки в событие
     * @param string $selector
     * @return \Look\Event\EventSelector
     */
    public static function parse(string $selector) : EventSelector
    {
        $tmp  = explode('#', $selector, 1);
        $name = $tmp[0];
        
        $class = null;
        $func  = null;
        
        if(isset($tmp[1])) {
            $tmp2 = explode('::', $tmp[1], 1);
            $class = $tmp2[0];
            $func  = $tmp2[1];
        }
        
        $class = in_array($class, [null, '*', '']) ? null : $class;
        $func  = in_array($func, [null, '*', '']) ? null : $func;
        
        return new EventSelector($name, $class, $func);
    }
    
    /**
     * @param string      $name  -> Назание события (регистронезависимо)
     * @param string|null $class -> Класс
     * @param string|null $func  -> Функция
     */
    public function __construct(string $name, ?string $class = null, ?string $func = null)
    {
        $this->name  = strtolower($name);
        $this->class = $class;
        $this->func  = $func;
    }
    
    /**
     * Возращает назание события
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }
    
    /**
     * Возращает назание класса
     * @return string
     */
    public function getClass() : ?string
    {
        return $this->class;
    }
    
    /**
     * Возращает назание класса
     * @return string
     */
    public function getFunction() : ?string
    {
        return $this->func;
    }
    
    /**
     * Имеет ли данный селектор класс
     * @return bool
     */
    public function hasClass() : bool
    {
        return isset($this->class);
    }
    
    /**
     * Имеет ли данный селектор функцию
     * @return bool
     */
    public function hasFunction() : bool
    {
        return isset($this->func);
    }
    
    /**
     * Формирует селектор в виде строки
     * @return string
     */
    public function __toString() : string
    {
        $hasClass = $this->hasClass();
        $hasFunc  = $this->hasFunction();
        
        if($hasClass) {
            
            if($hasFunc) {
                return "{$this->getName()}#{$this->getClass()}::{$this->getFunction()}";
            }
            
            return "{$this->getName()}#{$this->getClass()}::*";
        }
        
        if($hasFunc) {
            
            return "{$this->getName()}#*::{$this->getFunction()}";
        }
        
        return "{$this->getName()}";
    }
}