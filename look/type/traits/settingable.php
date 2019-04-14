<?php

namespace Look\Type\Traits;

use Look\Settings;

/**
 * Позволяет объекту иметь настройки
 */
trait Settingable
{
    /**
     * Возвращает значение настройки
     * 
     * @param string $key Ключ
     * @param string|int|boolean $def Значение по умолчанию
     * @return mixed
     */
    public function getSetting(string $key, $def = '')
    {
        return Settings::get(static::class, $key, $def);
    }
    
    /**
     * Присваевает новое значение
     * 
     * @param string $key               -> наименование ключа
     * @param string|int|boolean $value -> новое значение
     * @return void
     */
    public function setSetting(string $key, $value) : void
    {
        Settings::set(static::class, $key, $value);
    }
    
    /**
     * Удаляет ключ
     * 
     * @param string $key     -> наименование ключа
     * @return void
     */
    public function unsetSetting(string $key) : void
    {
        Settings::deleteKey(static::class, $key);
    }
    
    /**
     * Удаляет все настройки
     * 
     * @return void
     */
    public function deleteSettings() : void
    {
        Settings::deleteSection(static::class);
    }
}

