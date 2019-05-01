<?php

namespace Look\Html;

use Throwable;
use Look\Url;
use Look\Type\HTMLWrap;
use Look\Exceptions\InvalidArgumentException;

use Look\Html\Page;

use Look\Html\Traits\Bufferable;
use Look\Html\Traits\Attributable;

/**
 * Класс шапки страницы
 */
class Body extends HTMLWrap
{
    use Bufferable;
    use Attributable;
    
    /** @var Page */
    protected $page;
    
    /**
     * Класс шапки страницы
     * @param Page $page -> Объект страницы
     */
    public function __construct(Page &$page)
    {
        $this->page = &$page;
    }
    
    /**
     * Возврщает объект страницы
     * @return Page
     */
    public function &page() : Page
    {
        return $this->page;
    }
    
    /** {@inheritdoc} */
    protected function buildHTML(int $offset, int $tabSize, string $mainTabStr, string $tabStr) : ?string
    {
        $attrs = $this->attributesToHTML();
        
        $html = "$mainTabStr<body$attrs>\n"
              . $this->insertBuffer
              . "$mainTabStr</body>";
        
        return $html;
    }
}