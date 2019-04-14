<?php

namespace Look\Page;

use Throwable;
use Look\Url\Builder as Url;
use Look\Page\Helper\CssMinimizer;

/**
 * Класс шапки страницы
 */
class Head
{
    /** @var string Назание параметра который отвечает за антикеш в браузере */
    public $unCacheParam = 'v';
    
    /** @var \Look\Page\Meta -> мета данные */
    public $meta;
    
    /** @var \Look\Page\Navigation -> Навигация страницы */
    public $navigation;
    
    /** @var array */
    private $css = [];
    
    /**
     * Класс шапки страницы
     */
    public function __construct()
    {
        $this->meta       = new Meta();
        $this->navigation = new Navigation();
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
                    $result[$file] = $url->getRelative();
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Возращает сформированный список файлов для html вставки 
     * @return string
     */
    public function getBuildedCssHtmlList() : string
    {
        $files  = $this->getBuildedCssList();
        $result = '';
        foreach($files as $file => $href) {
            try {
                $result .= PHP_EOL . '<link href="'.$href.'" crossorigin="anonymous" integrity="'.CssMinimizer::getIntegrity(basename($file)).'" type="text/css" rel="stylesheet"/>';
            } catch(Throwable $ex) {}
        }
        return $result . PHP_EOL;
    }
}