<?php

namespace Look\Page;

use Look\Layout\LayoutBuilder;

/**
 * Конструктор хлебных крошек
 */
trait BreadCrumbs
{        
    /**
     * @var string Название файла разметки
     */
    protected $breadCrumbs__Layout = 'breadcrumbs';
    
    /**
     * @var boolean Использование хлебных крошек 
     */
    protected $useBreadCrumbs = true;
    
    /**
     * @var boolean Список хлебных крошек 
     */
    protected $breadCrumbs    = [];
    
    /**
     * Добавляет секцию
     * 
     * @param string $url   -> URL секции
     * @param string $title -> Название секции
     * @return $this
     */
    public function addBreadCrumb($url, $title)
    {
        $this->breadCrumbs[] = [
            'title' => $title,
            'url'   => $url
        ];
        
        return $this;
    }
    
    /**
     * Включает вывод хлебных крошек
     * 
     * @return $this
     */
    public function displayBreadCrumbs()
    {
        $this->useBreadCrumbs = true;
        
        return $this;
    }
    
    /**
     * Отключает вывод хлебных крошек
     * 
     * @return $this
     */
    public function hideBreadCrumbs()
    {
        $this->useBreadCrumbs = false;
        
        return $this;
    }
    
    /**
     * Очищает хлебные крошки
     * 
     * @return $this
     */
    public function clearBreadCrumbs()
    {
        $this->breadCrumbs = [];
        
        return $this;
    }
    
    /**
     * Собирает html шаблон хлебных крошек
     * 
     * @return string
     */
    public function buildBreadCrumbs()
    {
        if(!$this->useBreadCrumbs) {
            return '';
        }
        
        return LayoutBuilder::get($this->breadCrumbs__Layout, ['breadCrumbs' => $this->breadCrumbs]);
    }
}