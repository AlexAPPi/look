<?php

namespace Look\Html;

use Throwable;
use Look\Url;
use Look\Type\HTMLWrap;
use Look\Page\Util\CssMinimizer;
use Look\Exceptions\InvalidArgumentException;

/**
 * Класс шапки страницы
 */
class Head extends HTMLWrap
{
    /** @var string Назание параметра который отвечает за антикеш в браузере */
    const unCacheParam = 'v';
    
    /** @var string Кодировка */
    protected $encoding = Encoding::UTF8;

    /** @var string Заголовок страницы */
    protected $title;
    
    /** @var \Look\Page\Robots */
    protected $robots;
    
    /** @var \Look\Page\Meta -> мета данные */
    protected $meta;
    
    /** @var \Look\Page\Navigation -> Навигация страницы */
    protected $navigation;
    
    /** @var array */
    protected $css = [];
    
    /**
     * Класс шапки страницы
     */
    public function __construct()
    {
        $this->meta       = new Meta();
        $this->robots     = new Robots();
        $this->navigation = new Navigation();
    }
    
    /**
     * Возвращает объект конструктора robots
     * @return \Look\Html\Robots
     */
    public function &robots() : Robots
    {
        return $this->robots;
    }
    
    /**
     * Возвращает объект конструктора meta тегов
     * @return \Look\Html\Meta
     */
    public function &meta() : Meta
    {
        return $this->meta;
    }
    
    /**
     * Возвращает объект конструктора навигации страницы
     * @return \Look\Html\Navigation
     */
    public function &navigation() : Navigation
    {
        return $this->navigation;
    }
    
    /**
     * Возвращает кодировку страницы
     * @return string
     */
    public function getEncoding() : string
    {
        return $this->encoding;
    }
    
    /**
     * Устанавливает кодировку страницы
     * @param string $encoding -> Кодировка в соответствии стандарта
     * @return void
     */
    public function setEncoding(string $encoding) : void
    {
        $this->encoding = $encoding;
    }
    
    /**
     * Возвращает заголовок или тег <title>{VALUE}</title>
     * @return string
     */
    public function getTitle(bool $tag = false) : ?string
    {
        if ($tag) {
            return "<title>$this->title</title>";
        }
        
        return $this->title;
    }
    
    /**
     * Устанавливает заголовок странице
     * @param string $title заголовок
     * @return void
     */
    public function setTitle(string $title) : void
    {
        $this->title = $title;
    }
    
    /**
     * Возвращает фразу или тег <meta name="description" content="{VALUE}">
     * @return string|null
     */
    public function getDescription(bool $tag = false) : ?string
    {
        if(!$this->meta->has('description')) {
            return null;
        }
        
        if($tag) {
            return $this->meta->buildMetaTag('description');
        }
        
        return $this->meta->get('description');
    }
    
    /**
     * Устанавливает описание для страницы
     * @param string $description описание
     * @return void
     */
    public function setDescription(string $description) : void
    {
        $this->meta->set('description', $description);
    }
    
    /**
     * Вызвращает фразу или тег <meta name="keywords" content="{VALUE}">
     * @return string|null
     */
    public function getKeywords(bool $tag = false) : ?string
    {
        if(!$this->meta->has('keywords')) {
            return null;
        }
        
        if ($tag) {
            return $this->meta->buildMetaTag('keywords');
        }
        
        return $this->meta->get('keywords');
    }
    
    /**
     * Устанавливает ключевые слова 
     * @param array|string $keywords список ключевых слов
     * @return void
     */
    public function setKeywords($keywords) : void
    {
        // unset
        if($this->meta->has('keywords')) {
            $this->meta->unset('keywords');
        }
        
        $this->addKeywords($keywords);
    }
    
    /**
     * Добавляет ключевые слова
     * 
     * @param array|string $keywords список ключевых слов
     * @throws \InvalidArgumentException
     */
    public function addKeywords($keywords) : void
    {
        if(!is_string($keywords)
        && !is_array($keywords)) {
            throw new InvalidArgumentException('keywords');
        }
        
        $value = [];
        
        if($this->meta->has('keywords')) {
            $value = explode(',', $this->meta->get('keywords'));
        }
        
        if(is_string($keywords)) {
            $keywords = explode(',', $keywords);
        }
                
        $this->meta->set('keywords', implode(',', array_unique(
            array_merge($value, $keywords)
        )));
    }
    
    /**
     * Удаляет полностью или частично ключевые слова
     * @param null|array $keywords -> Ключевые слова, которые нужно удалить
     * @param 
     * @return void
     */
    public function unsetKeywords($keywords = null) : void
    {
        if(!$this->meta->has('keywords')) {
            return;
        }
        
        if($keywords === null) {
            $this->meta->unset('keywords');
            return;
        }
        
        $originalKeywords = explode(',', $this->meta->get('keywords'));
        
        $result = [];
        foreach($keywords as $curWord) {
            $del = false;
            foreach($originalKeywords as $unWord) {
                if($unWord == $curWord) {
                    $del = true;
                }
            }
            if(!$del) {
                $result[] = $curWord;
            }
        }
        $this->addKeywords($result);
    }
    
    /**
     * Добавляет css файлы на страницу
     * @param int    $cacheTime -> Время кеширования в секундах (0 - не кешировать)
     * @param string $file      -> Список файлов
     */
    public function addCss(int $cacheTime, string ...$file) : void
    {
        foreach($file as $tmp) {
            $this->css[$tmp] = $cacheTime;
        }
    }
    
    /**
     * Добавляет css файлы на страницу не кешируя их
     * при формировании списка к ним будет подстален параметр v + time()
     * файл будет иметь вид типа /css/file.css?v123
     * @param int    $cacheTime -> Время кеширования в секундах (0 - не кешировать)
     * @param string $file      -> Список файлов
     */
    public function addCssNoCache(string ...$file) : void
    {
        $this->addCss(0, ...$file);
    }
    
    /**
     * Удаляет css файлы со страницы
     * @param string $file
     */
    public function removeCss(string ...$file) : void
    {
        foreach($file as $tmp) {
            if(isset($this->css[$tmp])) {
                unset($this->css[$tmp]);
            }
        }
    }
    
    /**
     * Возвращает список css файлов
     * @return array
     */
    public function getCssList() : array
    {
        return array_keys($this->css);
    }
    
    /**
     * Возвращает список css файлов c ременем кеша
     * @return array
     */
    public function getCssListWithCacheStatus() : array
    {
        return $this->css;
    }
    
    /**
     * Создает результирующий список css файлов для страницы
     * @return array
     */
    public function getBuildedCssList() : array
    {
        $result = [];
        $groups = [];
        foreach($this->css as $file => $cache) {
            if(!isset($groups[$cache])) {
                $groups[$cache] = [];
            }
            $groups[$cache][] = $file;
        }
        foreach($groups as $cache => $files) {
            if($cache > 0) {
                $tmp = CssMinimizer::combineMinimizeCssFiles($cache, $files);
                $result[$tmp] = CssMinimizer::getHrefForFile($tmp);
            } else {
                foreach($files as $file) {
                    $url = new Url($file);
                    $url->setParam(time(), 1);
                    $result[$file] = $url->isRelativeOnConstruct() ? $url->getRelative() : $url->getAbsolute();
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Возращает сформированный список файлов для html вставки 
     * @param bool $integrity -> Использовать атрибут integrity
     * @return string
     */
    public function getBuildedCssHtmlList(bool $integrity = false) : string
    {
        $files  = $this->getBuildedCssList();
        $result = '';
        foreach($files as $file => $href) {
            try {
                $attrs = ' ';
                if($integrity) {
                    $attrs = ' integrity="'.CssMinimizer::getIntegrity(basename($file)).'" ';
                }
                $result .= "<link href=\"{$href}\" crossorigin=\"anonymous\"{$attrs}type=\"text/css\" rel=\"stylesheet\"/>\n";
            } catch(Throwable $ex) {}
        }        
        return $result;
    }
    
    /** {@inheritdoc} */
    protected function buildHTML(int $offset, int $tabSize, string $mainTabStr, string $tabStr): ?string
    {
        return "$mainTabStr<head>\n"
             . "{$mainTabStr}{$tabStr}<meta http-equiv=\"Content-type\" content=\"text/html; charset=$this->encoding\"/>\n"
             . "{$mainTabStr}{$tabStr}{$this->getTitle(true)}\n"
             . $this->meta->__toHTML($offset + 1, $tabSize)
             . $this->robots->__toHTML($offset + 1, $tabSize)
             . $this->navigation->__toHTML($offset + 1, $tabSize)
             . "$mainTabStr</head>";
    }
}