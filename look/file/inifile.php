<?php

namespace Look\File;

use Look\File\Exceptions\INIFileException;

/**
 * Класс для работы с INI файлами
 */
class INIFile
{
    const TRUE = 'true';
    const FALSE = 'false';
    
    /**
     * Путь к файлу
     * @var string 
     */
    private $fileName;
    
    /**
     * Массив с данными
     * @var array 
     */
    private $values;
    
    /**
     * Сохранить файл, когда будет вызван деструктор
     * @var bool 
     */
    private $saveOnDestruct = true;
    
    /**
     * Имеются ли изменения в файле
     * @var bool
     */
    private $hasChange = false;
    
    /**
     * Считывает данные из INI файла
     * @param string $file адрес к файул
     */
    public function __construct(string $file = null)
    {
        if (!is_null($file)) {
            
            $this->loadFromFile($file);
        }
    }
    
    /**
     * Деструктор класса
     * Сохраняет измененные данные в файл
     */
    public function __destruct()
    {
        if($this->saveOnDestruct && $this->hasChange) {
            $this->updateFile();
        }
    }
    
    /**
     * Собирает данные в массив
     */
    public function initArray()
    {
        // Типы обработаем самостоятельно
        $this->values = parse_ini_file($this->fileName, true, INI_SCANNER_RAW);
        
        if($this->values == false) {
            $this->values = array();
            return;
        }
        
        // Файл конфегурации поддерживает только 4 типа boolean, int, double, string, array
        foreach($this->values as $section => $keys) {
            
            foreach($keys as $key => $value) {

                if($value === self::TRUE) {
                    $this->values[$section][$key] = true;
                }
                else if ($value === self::FALSE) {
                    $this->values[$section][$key] = false;
                }
                else if (!is_array($value) && is_numeric($value)) {
                    $this->values[$section][$key] = $value + 0;
                }
            }
        }
    }
    
    /**
     * Загружает INI файл
     * 
     * @param string $file путь к файлу
     * @return boolean Статус загрузки
     */
    public function loadFromFile(string $file) : bool
    {
        $this->fileName = $file;
        
        if (file_exists($file) && is_readable($file)){
            $this->initArray();
            return true;
        }
        return false;
    }
    
    /**
     * Производит чтение значения
     * @param string $section Секция
     * @param string $key Ключ
     * @param string|int|boolean $def Значение по умолчанию
     * @return string
     */
    public function read(string $section, string $key = null, $def = '')
    {
        if($key == null) {
            return $this->values[$section];
        }
        
        if (isset($this->values[$section][$key])) {
            return $this->values[$section][$key];
        } else {
            $this->write($section, $key, $def);
            return $def;
        }
    }
    
    /**
     * Присваевает новое значение
     * @param string $section наименование секции
     * @param string $key наименование ключа
     * @param string|int|boolean $value новое значение
     */
    public function write(string $section, string $key, $value)
    {
        if(isset($this->values[$section][$key])) {
            if($this->values[$section][$key] == $value) {
                return;
            }
        }
        
        $this->values[$section][$key] = $value;
        $this->hasChange = true;
    }
    
    /**
     * Создает новую секцию
     * @param string $name
     */
    public function addSection(string $name)
    {
        if(!isset($this->values[$name]))
            $this->values[$name] = array();
    }
    
    /**
     * Создает новый ключ
     * @param string $section название секции
     * @param string $name название ключа
     * @param string $value значение
     */
    public function addKey(string $section, string $name, $value = '')
    {
        if(!isset($this->values[$section])) {
            $this->addSection($section);
        }
        $this->values[$section][$name] = $value;
    }
    
    /**
     * Удаляет секцию
     * @param string $section наименование секции
     */
    public function deleteSection(string $section)
    {
        if (isset($this->values[$section]))
            unset($this->values[$section]);
    }
    
    /**
     * Удаляет ключ из секции
     * @param string $section наименование секции
     * @param string $key наименование ключа
     */
    public function deleteKey(string $section, string $key)
    {
        if (isset($this->values[$section][$key]))
            unset($this->values[$section][$key]);
    }
    
    /**
     * Возвращает секции
     * @return array
     */
    public function readSections() : array
    {
        return array_keys($this->values);
    }
    
    /**
     * Проверяет существование секции
     * 
     * @param string $name -> название секции
     * @return bool
     */
    public function hasSection(string $name) : bool
    {
        return isset($this->values[$name]);
    }
    
    /**
     * Возвращает ключ
     * 
     * @param string $section название секции
     * @return array
     */
    public function readKeys(string $section) : array
    {
        if (isset($this->values[$section])) {
            return array_keys($this->values[$section]);
        }
        return null;
    }
    
    /**
     * Проверяет сещуствование ключа
     * 
     * @param string $section -> название секции
     * @param string $key     -> название ключа
     * @return bool
     */
    public function hasKey(string $section, string $key) : bool
    {
        return isset($this->values[$section]) &&
               isset($this->values[$section][$key]);
    }
    
    /**
     * Преобразует значение переменной в строку
     * @param string $name название
     * @param type $value значение
     * @return string 
     */
    private function valueToStr(string $name, $value) : string
    {
        if (is_bool($value)) {
            $value = $value ? self::TRUE : self::FALSE;
        }
        else if (is_array($value)) {
            
            $vTmp = '';
            foreach($value as $vKey => $vValue) {
                
                // Если это не число, то добавляем ковычки
                if(!is_numeric($vKey)) {
                    $vKey = "'".$vKey."'";
                }
                
                $vTmp .= $name.'['.$vKey.']='.$vValue.PHP_EOL;
            }
            return $vTmp;
        }
        
        return $name.'='.$value.PHP_EOL;
    }
    
    /**
     * Сохраняет измененный массив в файл
     * @throws INIFileException Формируется, когда произошла ошибка сохранения
     * @return void
     */
    public function updateFile()
    {
        $result = '';
        
        foreach ($this->values as $sname => $section) {
            $result .= '['.$sname.']'.PHP_EOL;
            foreach($section as $key => $value) {
                $result .= $this->valueToStr($key, $value);
            }
            $result .= PHP_EOL;
        }
        
        if(!file_put_contents($this->fileName, $result, LOCK_EX)) {
            throw new INIFileException("Не удалось сохранить ini файл");
        }
    }
}