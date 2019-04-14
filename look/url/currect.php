<?php

namespace Look\Url;

use Look\Url\Builder;

/**
 * Класс Currect - предназначен для работы с корректным URL, а также с адресной строкой.
 */
class Currect extends Builder
{
    use \Look\Type\Traits\Singleton;
    use \Look\Type\Traits\Settingable;
    
    protected $urlStr;
    protected $base;
    
    /**
     * Конструктор класса
     */
    private function __construct()
    {
        $baseDomainOffset = $this->getSetting('baseDomainOffset', 2);
        
        $this->urlStr = Builder::detectCurrectURL();
        $this->base   = Builder::detectBaseUrl();
        
        parent::__construct($this->urlStr);
        
        $this->setBaseDomainOffset($baseDomainOffset);
    }
    
    /**
     * Инициализирует работу класса
     * 
     * @return $this
     */
    public static function init()
    {
        return self::getInstance();
    }
}