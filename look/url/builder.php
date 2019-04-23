<?php

namespace Look\Url;

use Look\Url\Exceptions\URLBuilderException;

/**
 * Класс для работы с URL
 * 
 * @author Alexandr Shamarin <alexsandrshamarin@yandex.ru>
 */
class Builder
{
    const HTTP  = 'http';
    const HTTPS = 'https';
    
    /** @var string Полный URL */
    protected static $currectUrl = '';
    
    /** @var string Базовый URL */
    protected static $baseUrl = '';
    
    /** @var string Протокол */
    protected $scheme;
    
    /** @var string Хост */
    protected $host;
    
    /** @var int Смещение относительно базового домена (.ru, .com) */
    protected $baseDomainOffset = 1;
    
    /**
     * @var array Список доменов<br>
     * <b>Этот список перевернут [0] -> Базовый домен</b>
     */
    protected $domainList;
    
    /** @var string Порт */
    protected $port;
    
    /** @var string Пользователь */
    protected $user;
    
    /** @var string Пароль */
    protected $pass;
    
    /** @var string Путь */
    protected $path;
    
    /** @var string Строка с параметрами */
    protected $query;
    
    /** @var string Строка после # */
    protected $fragment;
    
    /** @var array Секции $path */
    protected $sections;

    /** @var array Параметры хранящиеся в url */
    protected $params;
    
    /** @var array Хранение временных значений */
    protected $storage;
    
    /** @var bool Сбросить данные после вывода */
    protected $restoreOutput = false;
    
    /** @var bool Собрать относительный путь */
    protected $toRelative = false;
    
    /** @var bool Конструктору был передан относительный путь */
    protected $isRelative = false;
    
    /**
     * Класс для работы с URL
     * <br>
     * парсит и позволяет манипулировать данными
     * <br>
     * <br>
     * Если в url передать относительный домен
     * будет подставлен корректный домен
     * 
     * @param  string $url -> Url адрес, по умолчанию используется корректный URL
     * @throws URLBuilderException
     */
    public function __construct(string $url = null)
    {
        if(empty($url)) {
            
            $url = self::detectCurrectURL();
        }
        else if($url[0] == '/') {
            
            // Делаем подставку корректного домена
            $url = self::detectBaseUrl() . $url;
            $this->isRelative = true;
        }
        
        $tmp = parse_url($url);
        
        if($tmp) {
            
            foreach($tmp as $i => $value) {
                $this->{$i} = $value;
            }
            
            parse_str($this->query, $this->params);

            $this->sections = [];
            $this->setHost($this->host);
            
            if($this->path !== null) {
                $sections = $this->parseSections($this->path);
                $this->addSection(...$sections);
            }
            
            $this->save();
            
        } else throw new URLBuilderException("Формат: $url не поддерживается");
    }
    
    /**
     * Конструктору был передан относительный путь
     * @return bool
     */
    public function isRelativeOnConstruct() : bool
    {
        return $this->isRelative;
    }
    
    /**
     * Определяет базовый URL
     * 
     * @return string
     */
    public static function detectBaseUrl()
    {
        if(empty(self::$baseUrl)) {
            
            self::detectCurrectURL();
        }
        
        return self::$baseUrl;
    }

    /**
     * Определяет полный url
     * 
     * @return string
     */
    public static function detectCurrectURL()
    {
        if(empty(self::$currectUrl)) {
            
            $protocol = self::HTTP;
        
            if ((isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1)) ||
                (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == self::HTTPS ||
                 !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on')
            ) {
                $protocol = self::HTTPS;
            }

            self::$baseUrl    = $protocol . '://' . $_SERVER['SERVER_NAME'];
            self::$currectUrl = self::$baseUrl . $_SERVER['REQUEST_URI'];
        }
         
        return self::$currectUrl;
    }
    
    /**
     * Парсит секции пути
     * 
     * @param  string $path     -> Путь типа /section/.../section
     * @param  string $delimetr -> Разделитель элементов
     * @return array
     */
    protected function parseSections(string $path, string $delimetr = '/')
    {
        // FIX / = ["", ""] || [".", "."]
        if(is_null($path) || $delimetr == $path) {
            
            return [];
        }
        
        $tmp = explode($delimetr, $path);
        
        // Убираем пустую секцию
        if(isset($path[0]) && $path[0] == $delimetr) {
            return array_slice($tmp, 1);
        }
        
        return $tmp;
    }
    
    /**
     * Устанавливает заданные значения по умолчанию
     * 
     * @return $this
     */
    public function save()
    {
        $names = get_object_vars($this);
                        
        foreach($names as $name => $value) {
            
            if($name == 'storage')
                continue;
            
            $this->storage[$name] = $this->{$name};
        }

        return $this;
    }
    
    /**
     * Делает откат заданных данных
     * 
     * @return $this
     */
    public function restore()
    {
        foreach($this->storage as $i => $value) {
            $this->{$i} = $value;
        }
        
        return $this;
    }
    
    /**
     * Активирует процесс восстановления исходных данных после вывода
     * <br>
     * Тоесть после того как будет вызвана функция __toString
     * <br>
     * Данные URL откатятся к последней точке сохранения
     * 
     * @return $this
     */
    public function restoreOnOutput()
    {
        $this->restoreOutput = true;
        return $this;
    }
    
    /**
     * Возвращает host
     * 
     * @return string
     */
    public function getHost()
    {
        // Переворачивает список доменов
        return implode('.', array_reverse($this->domainList));
    }
    
    /**
     * Устанавливает host
     * 
     * @param  string $host -> строка хоста
     * @return $this
     */
    public function setHost($host)
    {
        if(stripos($host, '://') !== false ||
           stripos($host, 'www.') === 0) {

            $data = parse_url($host);
            $host = $data['host'];
        }
        
        // Переворачиваем для удобства хранения
        $this->domainList = array_reverse($this->parseSections($host, '.'));
        
        return $this;
    }
    
    /**
     * Возвращает смещение относительного регионального домена<br><br>
     * 
     * @return $this
     */
    public function getBaseDomainOffset()
    {
        return $this->baseDomainOffset;
    }
    
    /**
     * Устанавливает смещение относительного регионального домена<br><br>
     * 
     * Это нужно, для указания базового домена:
     * 
     * - mydomain.ru        -> setBaseDomainOffset(1)
     * - mydomain.hoster.ru -> setBaseDomainOffset(2)
     * 
     * @param int $offset -> Количество доменов (по умолчанию 1)
     * @return $this
     */
    public function setBaseDomainOffset($offset)
    {
        $this->baseDomainOffset = $offset;
        
        return $this;
    }
        
    /**
     * Возвращает список доменов<br><br>
     * <b>глобальные домены .ru, .com не вырезаются</b>
     * 
     * @return array
     */
    public function getDomainList()
    {
        return $this->domainList;
    }
    
    /**
     * Возвращает количество доменов с учетом (.ru, .com, ..., host.ru, ...)
     * 
     * @return array
     */
    public function getDomainAllCount()
    {
        return count($this->domainList);
    }
    
    /**
     * Возвращает домен указанного уровня
     * 
     * @param int $level -> Уровень домена
     * @return string
     */
    public function getDomain($level)
    {
        return $this->domainList[$level];
    }
    
    /**
     * Проверяет домен указанного уровня
     * 
     * @param  int    $level -> Уровень домена
     * @return bool
     */
    public function hasDomain($level)
    {
        return isset($this->domainList[$level]);
    }
    
    /**
     * Устанавливает название домена
     * 
     * @param  int    $level -> Уровень домена
     * @param  string $name  -> Название домена
     * @return string
     * @throws URLBuilderException
     */
    public function setDomain($level, $name)
    {        
        if($level < 0) {
            
            $count = $this->getDomainAllCount();
            $level = $count + $level;
        }
        
        if(!$this->hasDomain($level)) {
            
            throw new URLBuilderException('Попытка установить название для не существующего уровня домена');
        }
        
        $this->domainList[$level] = $name;
        
        return $this;
    }
    
    /**
     * Возвращает количество доменов с поддоменами без учета (.ru, .com, ..., host.ru, ...)
     * 
     * @return array
     */
    public function getDomainCount()
    {
        return $this->getDomainAllCount() - $this->baseDomainOffset;
    }
    
    /**
     * Возвращает основной домен (домен 2 уровня или домен с указанным смещением)
     * 
     * @return array
     */
    public function getBaseDomain()
    {
        return $this->domainList[$this->baseDomainOffset];
    }
    
    /**
     * Устанавливает название основного домена (домен 2 уровня или домен с указанным смещением)
     * 
     * @param string $newName -> Новое название
     * @return $this
     */
    public function setBaseDomain($newName)
    {
        $this->domainList[$this->baseDomainOffset] = $newName;
        
        return $this;
    }
    
    /**
     * Возвращает смещение суб доменов
     * 
     * @return int
     */
    public function getSubDomainOffset()
    {
        return $this->baseDomainOffset + 1;
    }
    
    /**
     * Возвращает список под доменов
     * 
     * @return array
     */
    public function getSubDomainList()
    {
        return array_slice($this->getDomainList(), $this->getSubDomainOffset());
    }
            
    /**
     * Возвращает количество под доменов
     * 
     * @return array
     */
    public function getSubDomainCount()
    {
        return $this->getDomainCount() - 1;
    }
        
    /**
     * Возвращает суб домен<br><br>
     * <b>$level = 0 - это первый суб домен</b><br><br>
     * Если передать <b>$level = -1</b>, то вернется последний суб домен
     * 
     * @param int $level -> Уровень поддомена 
     * 
     * @return string
     */
    public function getSubDomain($level = 0)
    {
        if($level < 0) {
            
            $count = $this->getSubDomainCount();
            $level = $count + $level;
        }
        
        return $this->getDomain($this->getSubDomainOffset() + $level);
    }
    
    /**
     * Возвращает суб домен<br><br>
     * <b>$level = 0 - это первый суб домен</b><br><br>
     * Если передать <b>$level = -1</b>, то вернется последний суб домен
     * 
     * @param int    $level   -> Уровень поддомена 
     * @param string $newName -> Новое название
     * 
     * @return $this
     */
    public function setSubDomain($level, $newName)
    {
        if($level < 0) {
            
            $count = $this->getSubDomainCount();
            $level = $count + $level;
        }

        $this->setDomain($this->getSubDomainOffset() + $level, $newName);
        
        return $this;
    }
    
    /**
     * Проверяет существует ли суб домен<br><br>
     * <b>$level = 0 - это первый суб домен</b>
     * 
     * @param int $level -> Индекс поддомена 
     * 
     * @return array
     */
    public function hasSubDomain($level = 0)
    {
        return isset($this->domainList[$this->getSubDomainOffset() + $level]);
    }
    
    /**
     * Добавляет поддомен
     * 
     * Функция может принимать:<br>
     * - несколько аргументов сразу (sub1, sub2, sub3, ...)
     * - схему sub3.sub2.sub1....
     * - смежный вариант (sub1, sub3.sub2, ...)
     * 
     * @param string $name -> Название поддомена
     * @return $this
     */
    public function addSubDomain(... $name)
    {
        foreach($name as $sub) {
            
            $items = $this->parseSections($sub, '.');
            
            foreach($items as $item) {
                
                $this->domainList[] = $item;
            }
        }
        
        return $this;
    }
    
    /**
     * Возвращает первый суб домен
     * 
     * @return string
     */
    public function getFirstSubDomain()
    {
        return $this->getSubDomain(0);
    }
    
    /**
     * Возвращает последний суб домен
     * 
     * @return string
     */
    public function getLastSubDomain()
    {
        return $this->getSubDomain(-1);
    }
    
    /**
     * Удаляет поддомен<br><br>
     * <b>$index = 0 - это первый поддомен</b><br><br>
     * Если передать <b>$index = -1</b>, то поддомен удалится с конца
     * 
     * @param int $index -> Индекс поддомена (по умолчанию -1)
     * @param int $count -> Количество удаляемых поддоменов (по умолчанию 1)
     * 
     * @return $this
     * @throws URLBuilderException
     */
    public function removeSubDomain($index = -1, $count = 1)
    {        
        if($index > $count || -1 * $index > $count) {
            
            throw new URLBuilderException("Попытка удалить не существующий поддомен");
        }
        
        $tmp = [];
        $c   = count($this->domainList);
        
        if($index < 0) {
            
            $index = $c + $index;
        }
        
        for($i = 0; $i < $c; $i++) {
            
            if($i >= $index && $count > 0) {
                
                $count--;
                continue;
            }
            
            $tmp[] = $this->domainList[$i];
        }
        
        $this->domainList = $tmp;
        
        return $this;
    }
    
    /**
     * Удаляет последний поддомен
     * 
     * @return $this;
     */
    public function removeLastSubDomain()
    {
        $this->removeSubDomain();
        
        return $this;
    }
    
    /**
     * Удаляет все поддомены<br><br>
     *
     * @param int $before -> Индекс под домена
     * 
     * @return $this
     */
    public function removeAllSubDomains($before = 0)
    {
        if($before === 0) {
            
            $this->domainList = array_splice($this->domainList, 0, $this->getSubDomainOffset());
        }
        else {
            
            $this->domainList = array_splice($this->domainList, 0, $this->getSubDomainOffset() + $before);
        }
        
        return $this;
    }
    
    /**
     * Возвращает список секций
     * 
     * @return array
     */
    public function getSections()
    {
        return $this->sections;
    }
        
    /**
     * Возвращает список секций в виде пирамиды
     * 
     * [0] => /section<br>
     * [1] => /section/section2<br>
     * [2] => /section/section2/section3<br>
     * [3] => /section/section2/section3/section4<br>
     * ...<br>
     * 
     * @return array
     */
    public function getSectionsPiramide()
    {
        $res  = [];
        $buff = '';
        
        foreach($this->sections as $section) {
            
            $buff .= '/' . $section;
            $res[] = $buff;
        }
        
        return $res;
    }
    
    /**
     * Возвращает секцию под указанным индексом<br>
     * <br>
     * Если передать отрицательный индекс,
     * секция будет возвращена относительно конца секций
     * <br>
     * То есть: <b>-1 (минус один)</b> - это последняя секция
     * 
     * @param int $index
     * 
     * @return string
     */
    public function getSection($index)
    {        
        if($index < 0) {
            
            $count = $this->getSectionCount();
            $index = $count + $index;
        }
        
        if(!isset($this->sections[$index])) {
            return false;
        }
        
        return $this->sections[$index];
    }
    
    /**
     * Проверяет секции на полное или частичное соответствие
     * 
     * @param string $list -> Список секций по порядку
     * 
     * @return bool Description
     */
    public function checkSectionList(... $list)
    {
        $i = 0;
        
        foreach($list as $item) {
            
            $item = $this->parseSections($item);
            
            foreach($item as $section) {
                
                if($this->sections[$i] != $section) {
                    
                    return false;
                }
                
                $i++;
            }
        }
        
        return true;
    }
    
    /**
     * Проверяет секции на полное соответствие
     * 
     * @param string $list -> Список секций по порядку
     * 
     * @return bool Description
     */
    public function checkSectionListFullMatch(... $list)
    {
        $i = 0;
        
        foreach($list as $item) {
            
            $item = $this->parseSections($item);
            
            foreach($item as $section) {
                
                if($this->sections[$i] != $section) {
                    
                    return false;
                }
                
                $i++;
            }
        }
        
        if($i < count($this->sections))
            return false;
        
        return true;
    }
    
    /**
     * Возвращает количество секций в url
     * 
     * @return int
     */
    public function getSectionCount()
    {        
        return count($this->sections);
    }
    
    /**
     * Возвращает первую секцию url
     * 
     * @return string
     */
    public function getFirstSection()
    {
        return @$this->sections[0];
    }
    
    /**
     * Возвращает последнюю секцию пути url
     * 
     * @return string
     */
    public function getLastSection()
    {
        $count = $this->getSectionCount();
        return @$this->sections[$count - 1];
    }
    
    /**
     * Чистит секции
     * 
     * @return $this
     */
    public function clearSections()
    {
        $this->sections = [];
        
        return $this;
    }
    
    /**
     * Добавялет секцию в url
     * <br>
     * Функция может принимать:
     * - несколько названий секций (section1, section2, section3, ...)
     * - схему (section1/section2/section3)
     * - смежный вариант
     * 
     * @param string $name -> Название секции
     * 
     * @return $this
     */
    public function addSection(... $name)
    {
        if(!is_array($this->sections) || empty($this->sections)) {
            
            $this->sections = [];
        }
        
        foreach($name as $new) {
            
            $items = $this->parseSections($new);
            
            foreach($items as $item) {
                
                $this->sections[] = $item;
            }
        }
        
        return $this;
    }
    
    /**
     * Удаляет секцию в url
     * <br>
     * Если передать отрицательный индекс,
     * секция будет относительно конца
     * <br>
     * То есть: <b>-1 (минус один)</b> - это последняя секция
     * 
     * @param  int $index -> Индекс секции
     * @param  int $count -> Количество секций после этого индекса
     * @return $this
     */
    public function removeSection($index, $count = 1)
    {
        $tmp = [];
        $c   = count($this->sections);
        
        if($index < 0) {
            
            $index = $c + $index;
        }
        
        for($i = 0; $i < $c; $i++) {
            
            if($i >= $index && $count > 0) {
                
                $count--;
                continue;
            }
            
            $tmp[] = $this->sections[$i];
        }
        
        $this->sections = $tmp;
        
        return $this;
    }

    /**
     * Удаляет первую секцию
     * 
     * @return $this
     */
    public function removeFirstSection()
    {
        $this->sections = array_slice($this->sections, 1);
        
        return $this;
    }
    
    /**
     * Удаляет последнюю секцию в url
     *
     * @return $this
     */
    public function removeLastSection()
    {
        $this->sections = array_slice($this->sections, -1);
        
        return $this;
    }
    
    /**
     * Удаляет секцию в url<br><br>
     * Функция может принимать несколько индексов секций
     * 
     * @param int $index -> Индекс секции
     * @return $this
     */
    public function removeSectionByIndex(... $index)
    {
        $tmp = [];
        $c   = $this->getSectionCount();
        
        for($i = 0; $i < $c; $i++) {
            
           if(in_array($i, $index, true)) {
               continue;
           }
           
           $tmp[] = $this->sections[$i];
        }
        
        $this->sections = $tmp;
        
        return $this;
    }
    
    /**
     * Переименовывает секцию в url
     * 
     * @param int    $index -> Индекс секции
     * @param string $name  -> Новое название
     * @return $this
     * @throws URLBuilderException
     */
    public function setSection($index, $name)
    {
        if($this->hasSection($index)) {
            
            $this->sections[$index] = $name;
            
        } else {
            
            throw new URLBuilderException('Секция с индексом (' . $index . ') не найдена');
        }

        return $this;
    }
    
    /**
     * Переименовывает секцию в url
     * 
     * @param string $old -> Название секции
     * @param string $new -> Новое название
     * @return $this
     */
    public function setSectionByName($old, $new)
    {
        foreach($this->sections as &$tmp1) {
            
            if($tmp1 == $old)
                $tmp1 = $new;
        }
        
        return $this;
    }
    
    /**
     * Проверяет есть ли такие секции
     * <br>
     * Функция может принимать несколько названий секций
     * <br>
     * При передаче нескольких секций, проверяются все
     * 
     * @param string $name -> Название секции
     * @return bool
     */
    public function hasSection(... $index) : bool
    {
        foreach($index as $tmp1) {
            
            if(!isset($this->sections[$tmp1])) {
                
                return false;
            }
        }

        return true;
    }
    
    /**
     * Проверяет есть ли такие секции
     * <br>
     * Функция может принимать несколько названий секций
     * <br>
     * При передаче нескольких секций, проверяются все
     * 
     * @param string $name -> Название секции
     * @return bool
     */
    public function hasSectionByName(... $name) : bool
    {
        foreach($name as $tmp1) {
            
            if(!in_array($tmp1, $this->sections)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Проверяет есть ли такая последовательность из секций
     * <br>
     * При передаче нескольких секций, проверяются все
     * 
     * @param array|string  $name   -> Название секции
     * @param int           $index  -> Найденый индекс
     * @param int           $offset -> Смещение секций
     * @return bool
     */
    public function hasSectionSequence($name, &$index = null, $offset = 0) : bool
    {
        // Если это строка то парсим секции
        if(is_string($name)) {
            $name = $this->parseSections($name);
        } else if(!is_array($name)) {
            return false;
        }
        
        $f     = 0;
        $inner = false;
        $c     = count($name);
        
        foreach($this->sections as $section) {
            
            if($offset > 0) {
                
                $offset--;
                continue;
            }
            
            for($i = $f; $i < $c; $i++) {

                $inner = $name[$i] == $section;
                
                if($inner) {
                    
                    $f++;
                    $index++;
                    break;
                }
                
                $f = 0;
            }
        }

        return $inner;
    }
    
    /**
     * Проверяет является ли секция следующей
     * 
     * @param int    $offset -> Индекс секции
     * @param string $name   -> Название следующей секции
     * 
     * @param bool
     */
    public function isNextSection($offset, $name) : bool
    {
        $max = $this->getSectionCount() - 1;
        
        // 9 >= 9 || -1 * -9 > 9
        // Не должно быть больше кол секций
        // Отрицательный индекс больше кол секций + 1
        if($offset >= $max || -1 * $offset > $max) {
            
            return false;
        }
        
        $tmp = $this->getSection($offset + 1);
        
        return $tmp == $name;
    }
    
    /**
     * Проверяет является ли секция предыдущей
     * 
     * @param int    $offset -> Индекс секции
     * @param string $name   -> Название предыдущей секции
     * 
     * @param bool
     */
    public function isPrevSection($offset, $name) : bool
    {
        $max = $this->getSectionCount() - 1;
        
        // Не должно быть больше кол секций
        // Отрицательный индекс больше кол секций + 1
        if($offset > $max || -1 * $offset >= $max) {
            
            return false;
        }
        
        $tmp = $this->getSection($offset - 1);
        
        return $tmp == $name;
    }
    
    
    /**
     * Возвращает значение параметра
     * 
     * @param string $name -> Название параметра
     * @return mixed
     */
    public function getParam($name)
    {
        return $this->params[$name];
    }
        
    /**
     * Устанавливает значение для параметра
     * 
     * @param string $name  -> Название параметра
     * @param string $value -> Значение
     * @return $this
     */
    public function setParam(string $name, string $value = null)
    {
        if(!is_array($this->params) || !isset($this->params)) {
            $this->params = [];
        }
        
        $this->params[$name] = $value;
        return $this;
    }
    
    /**
     * Устанавливает значения для параметров
     * 
     * @param string $name  -> Название параметра
     * @param string $value -> Значение
     * @return $this
     */
    public function setParams($params)
    {
        foreach($params as $key => $value)
        {
            $this->setParam($key, $value);
        }
        
        return $this;
    }
    
    /**
     * Проверяет задан ли такой параметр
     * <br>
     * Функция может принимать несколько названий параметров
     * <br>
     * При передаче нескольких параметров, проверяются все
     * 
     * @param string $name -> Название секции
     * @return bool
     */
    public function hasParam(string ... $name) : bool
    {
        $ok  = false;
        $tmp = array_keys($this->params);
        
        foreach($name as $tmp1) {
                        
            if(in_array($tmp1, $tmp))
                $ok = true;
        }

        return $ok;
    }
    
    /**
     * Проверяет заданы ли параметры
     *
     * @return boolean
     */
    public function hasParams() : bool
    {
        return count($this->params) > 0 ? true : false;
    }
        
    /**
     * Удаляет параметр
     * <br>
     * Функция может принимать несколько названий параметров
     * 
     * @param string $name -> Название секции
     * @return $this
     */
    public function removeParam(string ... $name)
    {
        $tmp = [];
        
        foreach($this->params as $key => $value) {
            
            if(!in_array($key, $name))
                $tmp[$key] = $value;
        }

        $this->params = $tmp;
        
        return $this;
    }
    
    /**
     * Удаляет список параметров
     * 
     * @return $this
     */
    public function removeParams()
    {
        $this->params = [];
        
        return $this;
    }
    
    /**
     * Удаляет список параметров по флагу
     * 
     * @return $this
     */
    public function removeParamsFromFlag(bool $flag)
    {
        if($flag)
            return $this->removeParams();
        
        return $this;
    }
    
    /**
     * Проверяет совпадение параметра
     * 
     * @param string $name   -> Название параметра
     * @param string $value  -> Значение
     * @param bool   $strict -> Проверка типа
     * 
     * @return bool
     */
    public function checkParam($name, $value, $strict = false) : bool
    {
        if($strict)
        {
            return $this->params[$name] === $value;
        }
        
        return $this->params[$name] == $value;
    }
    
    /**
     * Возвращает hash
     * 
     * @return string
     */
    public function getFragment()
    {
        return $this->fragment;
    }
    
    /**
     * Устанавливает hash
     * 
     * @return $this
     */
    public function setFragment($text)
    {
        $this->fragment = $text;
        
        return $this;
    }
    
    /**
     * Очищает строку hash
     * 
     * @return $this
     */
    public function clearFragment()
    {
        $this->fragment = '';
        
        return $this;
    }
    
    /**
     * Проверяет url
     * 
     * @return boolean
     */
    public function isCurrect() : bool
    {
        $url = $this->build();
        return !empty($url) && parse_url($url) !== false;
    }
    
    /**
     * Собирает абсолютный URL
     * <br>
     * <br>
     * Метод не вызывает <b>restoreOnOutput</b> проверку
     * 
     * @return string
     */
    public function build() : string
    {
        $url  = '';
        $www  = false;
        $user = false;
        $pass = false;
        $host = false;
        $port = false;
        
        if(!is_null($this->scheme) && strlen($this->scheme) > 0) {
            
            $url .= $this->scheme;
            
            if($this->scheme != 'www') {
                $url .= '://';
            } else {
                $www = true;
            }
        }
        
        if(!is_null($this->user) && strlen($this->user) > 0) {
            $url .= $this->user;
            $user = true;
        }
        
        if(!is_null($this->pass) && strlen($this->pass) > 0) {
            $url .= ':' . $this->pass;
            $pass = true;
        }
        
        $this->host = $this->getHost();
        
        if(!empty($this->host) && strlen($this->host) > 0) {
            
            if($user) $url .= '@';
            
            $url .= $this->host;
            $port = true;
        }
        
        if(!is_null($this->port) && strlen($this->port) > 0) {
            $url .= ':' . $this->port;
            $port = true;
        }
        
        $url .= $this->buildRelative();
        
        return $url;
    }

    /**
     * Собирает относительный URL
     * <br>
     * <br>
     * Метод не вызывает <b>restoreOnOutput</b> проверку
     * 
     * @return string
     */
    public function buildRelative() : string
    {        
        $url  = empty($this->sections)    ? '/' : '/' . implode('/', $this->sections);
        $url .= count($this->params) == 0 ? ''  : '?' . http_build_query($this->params);
        $url .= empty($this->fragment)    ? ''  : '#' . $this->fragment;
        
        return $url;
    }

    /**
     * Возвращает относительный URL
     * 
     * @return string
     */
    public function getRelative() : string
    {
        $this->toRelative = true;
        
        return (string)$this;
    }
    
    /**
     * Возвращает абсолютный URL
     * 
     * @return string
     */
    public function getAbsolute() : string
    {
        $this->toRelative = false;
        
        return (string)$this;
    }
    
    /**
     * Возвращает URL
     * 
     * @return string
     */
    public function get()
    {
        return (string)$this;
    }
    
    /**
     * Это HTTP URL
     * 
     * @return string
     */
    public function isHttp()
    {
        return strtolower($this->scheme) == self::HTTP;
    }
    
    /**
     * Это HTTPS URL
     * 
     * @return string
     */
    public function isHttps()
    {
        return strtolower($this->scheme) == self::HTTPS;
    }
    
    /**
     * Изменяет протокол на http
     * 
     * @return $this
     */
    public function toHttp()
    {
        $this->scheme = self::HTTP;
        
        return $this;
    }
    
    /**
     * Изменяет протокол на https
     * 
     * @return $this
     */
    public function toHttps()
    {
        $this->scheme = self::HTTPS;
        
        return $this;
    }
    
    /**
     * Конвертирует Класс URL в строку
     * 
     * @return string
     */
    public function __toString() : string
    {
        $res = $this->toRelative ? $this->buildRelative() : $this->build();
        
        // Сброс изменений после вывода
        if($this->restoreOutput) {
            
            $this->restore();
            $this->toRelative    = false;
            $this->restoreOutput = false;
        }
        
        return $res;
    }
        
    /**
     * Удаляет из URL все запрещенные спецсимволы.
     * 
     * @param string $str -> строка для операции
     * 
     * @return string
     */
    public function prepareUrl(string $str) : string
    {
        $str = strtolower($str);
        $str = preg_replace('%\s%i', '-', $str);
        $str = str_replace('`', '', $str);
        $str = preg_replace('%[^/-a-zа-я\d]%i', '', $str);
        $str = substr($str, 0, 255);
        
        return $str;
    }
    
    /**
     * Возвращает назание файла
     * @return string|null
     */
    public function getFile() : ?string
    {
        return $this->getLastSection();
    }
    
    /**
     * Возвращает назание файла без типа
     * @return string|null
     */
    public function getFileName() : ?string
    {
        $section = $this->getLastSection();
        if($section !== null) {
            $tmp = explode('.', $section);
            if(count($tmp) > 1) {
                array_pop($tmp);
                return implode('.', $tmp);
            }
        }
        return null;
    }
    
    /**
     * Возвращает тип файла
     * @return string|null
     */
    public function getFileType() : ?string
    {
        $section = $this->getLastSection();
        if($section !== null) {
            $tmp = explode('.', $section);
            if(count($tmp) > 1) {
                $tmp = $tmp[count($tmp) - 1];
            }
        }
        return null;
    }
    
    /**
     * Данное url является ссылкой на файл
     * @return bool
     */
    public function isFile() : bool
    {
        $section = $this->getLastSection();
        if($section !== null) {
            $delim   = count(explode('.', $section));
            return $delim > 1;
        }
        return false;
    }
}