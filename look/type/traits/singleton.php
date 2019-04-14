<?php

namespace Look\Type\Traits;

/**
 * Позволяет статическому классу иметь только один экземпляр,
 * для избежания дублей
 */
trait Singleton
{
    /**
     * Первый и единственный экземпляр класса
     * @var self 
     */
    private static $_instance = null;
    
    /**
     * Создает единственный экземпляр класса
     * @return $this
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self;
        }
 
        return self::$_instance;
    }
    
    /**
     * Инициализация класса
     * @return self
     */
    public static function Init()
    {
        return static::getInstance();
    }
    
    private function __construct() {}
}