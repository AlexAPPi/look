<?php

namespace Look\Html;

use Look\Type\HTMLWrap;

/**
 * Объект навигации страницы
 */
class Navigation extends HTMLWrap
{    
    /**
     * @var string Зеркало страницы
     */
    protected $canonical = null;
    
    /**
     * @var string Предыдущая страница пагинации 
     */
    protected $prevLink = null;
    
    /**
     * @var string Следующая страница пагинации 
     */
    protected $nextLink = null;

    /**
     * Возвращает зеркало для страницы или тэг
     * @param boolean $tag -> Вернуть html тэг
     * @return string|null
     */
    public function getCanonical(bool $tag = false) : ?string
    {
        $canonical = $this->canonical;
        
        if ($tag) {

            if (empty($canonical)) {
                return '';
            }
            
            return '<link rel="canonical" href="' . $canonical . '"/>' . PHP_EOL;
        }

        return $canonical;
    }    

    /**
     * Устанавливает зеркало для страницы
     * @param string $url -> URL зеркальной страницы
     * @return void
     */
    public function setCanonical(string $url) : void
    {
        $this->canonical = $url;
    }
    
    /**
     * Проверяет, является ли данная страница каноничной
     * @param string $url -> URL зеркальной страницы
     * @return bool
     */
    public function hasCanonical() : bool
    {
        return !empty($this->canonical);
    }
    
    /**
     * Возвращает следующую страницу пагинации
     * @param boolean $tag -> Вернуть html тэг
     * @return string|null
     */
    public function getPrevPagination(bool $tag = false) : ?string
    {
        $prevLink = $this->prevLink;
        
        if ($tag) {

            if (empty($prevLink)) {
                return '';
            }
            
            return '<link rel="prev" href="' . $prevLink . '"/>' . PHP_EOL;
        }

        return $prevLink;
    }
    
    /**
     * Устанавливает предыдущую страницу пагинации
     * @param string $url -> URL страницы
     * @return void
     */
    public function setPrevPagination(string $url) : void
    {
        $this->prevLink = $url;
    }
    
    /**
     * Проверяет существует ли предыдущая страница пагинации
     * @return void
     */
    public function hasPrevPagination() : bool
    {
        return !empty($this->prevLink);
    }
    
    /**
     * Возвращает следующую страницу пагинации
     * 
     * @param boolean $tag -> Вернуть html тэг
     * @return string
     */
    public function getNextPagination(bool $tag = false) : ?string
    {
        $nextLink = $this->nextLink;
        
        if ($tag) {

            if (empty($nextLink)) {
                return '';
            }
            
            return '<link rel="next" href="' . $nextLink . '"/>' . PHP_EOL;
        }

        return $nextLink;
    }
    
    /**
     * Устанавливает следующую страницу пагинации
     * 
     * @param string $url -> URL страницы
     * @return void
     */
    public function setNextPagination(string $url) : void
    {
        $this->nextLink = $url;
    }
    
    /**
     * Проверяет существует ли следующая страница пагинации
     * @return void
     */
    public function hasNextPagination() : bool
    {
        return !empty($this->nextLink);
    }
    
    /** {@inheritdoc} */
    protected function buildHTML(int $offset, int $tabSize, string $mainTabStr, string $tabStr): ?string
    {
        $html = '';
        
        if($this->hasCanonical()) {
            $html .= $mainTabStr . $this->getCanonical(true);
        }
        
        if($this->hasPrevPagination()) {
            $html .= $mainTabStr . $this->getPrevPagination(true);
        }
        
        if($this->hasNextPagination()) {
            $html .= $mainTabStr . $this->getNextPagination(true);
        }
        
        return $html;
    }
}