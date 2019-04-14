<?php

namespace Look\Token\Container;

use Look\Settings;
use Look\Exceptions\SystemException;

/**
 * Класс шифровки данных
 */
class Coder
{
    /**
     * Единственный экземпляр класса
     * @var Coder 
     */
    private static $instance = null;
    
    private function __clone() {}
    private function __wakeup() {}
    
    /**************************************************************************/
    
    private $packPass;
    private $packSalt;
    
    /**************************************************************************/
    
    private function __construct()
    {
        $this->packPass = Settings::get('coder', 'code', null);
        $this->packSalt = Settings::get('coder', 'salt', null);
        
        if($this->packPass == null || strlen($this->packPass) < 10 || $this->packSalt = null || strlen($this->packSalt) < 10) {
            throw new SystemException("Для шифрования нельзя использовать пустой пароль или пароль длинной меньше 10 символов");
        }
    }
    
    /**
     * Инициализирует единственный объект данного класса.
     * @return $this
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
    
    /**
     * Шифрование
     * @param string $string строка
     * @param bolean $encode расшифровать
     * @param string $packPass ключ 1
     * @param string $packSalt ключ 2 
     * @return type
     */
    public static function code($string, $encode = false, $packPass = null, $packSalt = null)
    {
        if($encode) {
            $string = self::urlDecode($string);
        }
        
        $instance = self::getInstance();
        
        $seq   = $packPass == null ? $instance->packPass : $packPass;
        $salt  = $packSalt == null ? $instance->packSalt : $packSalt;
        $len   = strlen($string);
        $gamma = '';
        
        while (strlen($gamma) < $len) {
            $seq    = pack("H*", sha1($seq . $salt)); 
            $gamma .= substr($seq, 0, 8);
        }

        if($encode) {
            return $string^$gamma;
        }
        
        return self::urlEncode($string^$gamma);
    }
    
    /**
     * Шифрует строку
     * @param string $string      - Шифруемая строка
     * @param string $packPass    - ключ 1
     * @param string $packSource  - ключ 2 
     * @return string
     */
    public static function decode($string, $packPass = null, $packSource = null) {
        return self::code($string, false, $packPass, $packSource);
    }
    
    /**
     * Расшифровывает строку
     * @param string $string      - Зашифрованная строка
     * @param string $packPass    - ключ 1
     * @param string $packSource  - ключ 2 
     * @return string
     */
    public static function encode($string, $packPass = null, $packSource = null) {
        return self::code($string, true, $packPass, $packSource);
    }
    
    /**
     * Шифрует ключ для доступной передачи через URL
     * @param string $key - ключ
     * @return string
     */
    public static function urlEncode($key) {
        return rtrim(strtr(base64_encode($key), '+/', '-_'), '='); 
    }
    
    /**
     * Приводит ключ к нормальному виду, если его передали по URL
     * @param string $key 
     * @return string
     */
    public static function urlDecode($key) {
        return base64_decode(str_pad(strtr($key, '-_', '+/'), strlen($key) % 4, '=', STR_PAD_RIGHT)); 
    }
}