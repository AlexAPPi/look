<?php

namespace Look;

use Look\File\INIFile;

/**
 * Класс для работы с настройками
 */
final class Settings
{
    use \Look\Type\Traits\Singleton;
    
    /**
     * Название файла с настройками
     */
    const FileName = 'settings.ini';
    
    /**
     * @var \Look\File\INIFile файл настроек 
     */
    protected $settingFile;

    /**
     * Конструктор класса
     */
    private function __construct()
    {
        $this->settingFile = new INIFile(APP_DIR . DIRECTORY_SEPARATOR . static::FileName);
    }
    
    public function __destruct()
    {
        unset($this->settingFile);
        $this->settingFile = null;
    }
    
    /**
     * Возвращает значение
     * 
     * @param string $section Секция
     * @param string $key Ключ
     * @param string|int|boolean $def Значение по умолчанию
     * @return mixed
     */
    public static function get(string $section, string $key, $def = '')
    {
        return self::getInstance()->settingFile->read($section, $key, $def);
    }
    
    /**
     * Присваевает новое значение
     * 
     * @param string $section           -> наименование секции
     * @param string $key               -> наименование ключа
     * @param string|int|boolean $value -> новое значение
     * @return void
     */
    public static function set(string $section, string $key, $value) : void
    {
        self::getInstance()->settingFile->write($section, $key, $value);
    }
    
    /**
     * Создает новую секцию
     * 
     * @param string $name -> название секции
     * @return void
     */
    public static function addSection(string $name) : void
    {
        self::getInstance()->settingFile->addSection($name);
    }
    
    /**
     * Удаляет секцию
     * 
     * @param string $name -> наименование секции
     * @return void
     */
    public static function deleteSection(string $name) : void
    {
        self::getInstance()->settingFile->deleteSection($name);
    }
    
    /**
     * Создает новый ключ в секции
     * 
     * @param string $section -> название секции
     * @param string $name    -> название ключа
     * @param string $value   -> значение
     * @return void
     */
    public static function addKey(string $section, string $name, $value = '') : void
    {
        self::getInstance()->settingFile->addKey($section, $name, $value);
    }
    
    /**
     * Удаляет ключ из секции
     * 
     * @param string $section -> наименование секции
     * @param string $key     -> наименование ключа
     * @return void
     */
    public static function deleteKey(string $section, string $key) : void
    {
        self::getInstance()->settingFile->deleteKey($section, $key);
    }
}