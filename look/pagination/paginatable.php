<?php

namespace Look\Pagination;

use Look\Exceptions\SystemException;

use Look\Page\HtmlPage;

use Look\API\Type\TypeManager;

use Look\Url\Currect as UrlCurrect;
use Look\Url\Builder as UrlBuilder;

use Look\Page\Exceptions\PageEmptyException;
use Look\Page\Exceptions\PageNotFoundException;
use Look\Page\Exceptions\Page301RedirectException;

use Look\Pagination\ISectionable;

/**
 * Класс пагинации страниц
 * 
 * В версии 2.2 бал добавлен параметр $pagination_method, который реализует
 * CEO оптимизацию для поисковых систем (Яндекс, Google)
 * 
 * В версии 2.1 был добавлен параметр $pagination__paramsImportant, который
 * указывает важность параметров при определении canonical страницы
 * 
 * 
 * В версии 2.0 была пересмотрена модель представления пагинации
 * 
 * Рассмотрение пагинации происходит по секции:
 * 
 * - первая секция               .../section
 * - вторая секция и последующая .../section/page/2 и т.д
 * - страница показать все       .../section/page/all
 * 
 * @author Alexandr Shamarin <alexsandrshamarin@yandex.ru>
 */
trait Paginatable
{
    /** @var string Данный метод создает общую страницу просмотра всего списка */
    public static $PaginationMethod_ALL = 'all';
    
    /** @var string Данный метод создает:
     * для Google общую страницу просмотра из цикла prev/next (аналог all)
     * для Yandex обозначает главную страницу 1 и индексирует только ссылки c последующих, не прибавляя веса для страницы 1
     */
    public static $PaginationMethod_PrevNext_NoindexFollow = 'prevnext_noindexfollow';
    
    /** Приставка распознания пагинации страницы */
    public static $pagination__identifier = 'page';

    /** Индекс отображения всех страниц <b>showAllMode</b> */
    public static $pagination__showAllIdentifier = 'all';
    
    /** @var string Метод пагинации страниц */
    private $pagination_method = 'all';//static::$Method_ALL;
        
    /** @var mixed ссылка на страницу */
    private $pagination__url;
    
    /** @var \Look\Url\Builder объект ссылки c убранной секцией пагинации */
    private $pagination__fixUrl;

    /** @var int Индекс текущей страницы */
    private $pagination__currect = 1;

    /** @var int Максимально доступный индекс страницы */
    private $pagination__maxIndex = 0;

    /** @var int Максимальной количество элементов на 1 странице */
    private $pagination__limit = 30;

    /** @var int Смещение относительного начального элемента */
    private $pagination__offset = 0;

    /** @var int Общее количество элементов */
    private $pagination__total = 0;

    /** @var \Look\Pagination\ISectionable|array Массив с элементами */
    private $pagination__items = [];

    /** @var bool Флаг отображения всех страниц */
    private $pagination__showAll = false;

    /** @var bool Флаг зеркала основной страницы */
    private $pagination__currectIsCanonical = true;
    
    /** @var bool Флаг не существующей страницы */
    private $pagination__currectIsNotFound = false;
    
    /** @var bool Флаг редиректа на страницу без указателя */
    private $pagination__currectIsRedirect = false;
    
    /** @var bool Флаг пустой страницы страницу без указателя */
    private $pagination__isEmpty = false;
    
    /** @var bool Флаг необходимости подставки GET параметров в страницы пагинации */
    private $pagination__paramsImportant = false;

    /** @var string Название элементов для вывода элементов */
    private $pagination__itemName = ['Элемент', 'Элемента', 'Элементов'];
    
    /** @var string Название станиц для вывода */
    private $pagination__pageName = ['Страница', 'Страницы', 'Страниц'];

    /** @var string Формат вывода строки показа по несколько элементов */
    private $pagination__sectionFormat = 'Показывать по %limit% %Name@limit%';

    /** @var string Формат вывода строки показать сразу все */
    private $pagination__showAllFormat = 'Показать сразу %total% %Name@total%';
    
    /**
     * Удаляет из url данные для пагинации
     * @param string $url Url
     * @return string
     */
    public static function clearPaginationFromUrl(string $url) : string
    {
        $cUrl = new UrlBuilder($url);
        
        $penultSection = $cUrl->getSection(-2);
        $lastSection   = $cUrl->getLastSection();

        // Шаблон пагинации имеет вид:
        // Для 1 страницы      .../section
        // Для 2 и последующих .../section/$indicator/$index
        if(static::$pagination__identifier === $penultSection) {
            
            // Вытаскиваем index
            // Index может быть только положительным числом
            // Удаляем из url префикс страницы
            // Теперь url вида .../section/page/all
            // будет иметь вид .../section
            if(TypeManager::strToUnsignedInt($lastSection) !== false || $lastSection === static::$pagination__showAllIdentifier) {
                
                $cUrl->removeSection(-2, 2)->save();
                
                if($cUrl->isRelativeOnConstruct()) {
                    return $cUrl->getRelative();
                }
                
                return $cUrl->getAbsolute();
            }
        }
        
        return $url;
    }
    
    /**
     * Возвращает список свойств для инициализации __sleep метода
     * @return array
     */
    protected function __paginationSleep() : array
    {
        return [
            'pagination_method',
            'pagination__url',
            'pagination__fixUrl',
            'pagination__currect',
            'pagination__maxIndex',
            'pagination__limit',
            'pagination__offset',
            'pagination__total',
            'pagination__items',
            'pagination__showAll',
            'pagination__currectIsCanonical',
            'pagination__currectIsNotFound',
            'pagination__currectIsRedirect',
            'pagination__isEmpty',
            'pagination__paramsImportant',
            'pagination__itemName',
            'pagination__pageName',
            'pagination__sectionFormat',
            'pagination__showAllFormat'
        ];
    }
    
    /**
     * Инициализация __wakeup метода
     * @return array
     */
    protected function __paginationWakeup() : void
    {
        
    }
    
    /**
     * Устанавливает метод пагинации
     * 
     * @param string $method
     * @return void
     */
    public function paginationMethod(string $method) : void
    {
        $this->pagination_method = $method;
    }
    
    /**
     * Анализирует url
     * 
     * @param string $url -> Url страницы пагинации
     * @return $this
     */
    public function initPagination($url = null)
    {
        if(is_null($this->pagination__url)) {
            
            $this->pagination__url    = !empty($url) ? $url : UrlCurrect::getUri();
            $this->pagination__fixUrl = new UrlBuilder($this->pagination__url);
            
            $penultSection = $this->pagination__fixUrl->getSection(-2);
            $lastSection   = $this->pagination__fixUrl->getLastSection();

            // Шаблон пагинации имеет вид:
            // Для 1 страницы      .../section
            // Для 2 и последующих .../section/$indicator/$index
            if(static::$pagination__identifier === $penultSection) {

                // Проверим задан ли следующий индекс числом
                // или использована команда showALL

                // Вытаскиваем index
                // Index может быть только положительным числом
                $numIndex = TypeManager::strToUnsignedInt($lastSection);

                if ($numIndex !== false) {

                    $this->pagination__currect = $numIndex;

                    // Удаляем из url префикс страницы
                    // Теперь url вида .../section/page/[0-9*]
                    // будет иметь вид .../section
                    $this->pagination__fixUrl->removeSection(-2, 2)
                                             ->save();
                    
                    // Если индекс равен нулу то такой страницы не существует
                    // SEO рекоменация Яндекс Разработчика
                    if ($this->pagination__currect == 0) {

                        $this->pagination__currectIsNotFound = true;
                        return false;
                    }

                    // Если указан индекс равный 1 то редиректим на страницу без индекса
                    if ($this->pagination__currect == 1) {

                        $this->pagination__currectIsRedirect = true;
                        return false;
                    }
                                        
                    // SEO рекомендация Яндекс Разработчика
                    // Все страницы пагинации выводить с canonical URL без параметра page
                    $this->pagination__currectIsCanonical = false;
                }
                else if ($lastSection === static::$pagination__showAllIdentifier && 
                         static::$Method_ALL == $this->pagination_method) {

                    // Удаляем из url префикс страницы
                    // Теперь url вида .../section/page/all
                    // будет иметь вид .../section
                    $this->pagination__fixUrl->removeSection(-2, 2)
                                             ->save();

                    $this->pagination__showAll = true;

                    // УТОЧНЕНИЯ ПО Canocical
                    // Страница show-all без фильтров является основной страницей
                    // Страницы пагинации являются зеркалами для основной страницы show-all
                    // Если даже указаны фильтры, и пользователь указал show-all => основная страница show-all без фильтров
                    // В версии 2.1 предусмотрен вес get параметров для бащового URL
                    if($this->pagination__fixUrl->hasParams() && !$this->pagination__paramsImportant) {
                        $this->pagination__currectIsCanonical = false;
                    } else {
                        $this->pagination__currectIsCanonical = true;
                    }
                }
                else {

                    // Если выполнен переход по ссылке .../section/page/...
                    // не поддерживаемого формата, то такой страницы не существует
                    $this->pagination__currectIsNotFound = true;
                    return false;
                }
            }
            else {
                
                // SEO рекомендация Яндекс Разработчика
                // Все страницы пагинации выводить с canonical URL без параметра page
                $this->pagination__currectIsCanonical = false;
            }
        }
        else {
            throw new SystemException('Класс уже инициализирован');
        }
        
        return $this;
    }
    
    /**
     * Задает идентификатор пагинации
     * @param string $identifier -> Идентификатор
     * @return void
     */
    protected function setPaginationIdentifier(string $identifier) : void
    {
        static::$pagination__identifier = $identifier;
    }
    
    /**
     * Задает идентификатор пагинации общей страницы
     * @param string $identifier -> Идентификатор
     * @return void
     */
    protected function setPaginationShowAllIdentifier(string $identifier) : void
    {
        static::$pagination__showAllIdentifier = $identifier;
    }
    
    /**
     * Указывает, что параметры в url нужны
     * @return void
     */
    protected function setPaginationParamsImportant() : void
    {
        $this->pagination__paramsImportant = true;
    }
        
    /**
     * Указывает, что параметры в url не нужны
     * @return void
     */
    protected function setPaginationParamsNotImportant() : void
    {
        $this->pagination__paramsImportant = false;
    }
    
    /**
     * Возвращает статус важности параметров
     * @return bool
     */
    protected function getPaginationParamsIsImportant() : bool
    {
        return $this->pagination__paramsImportant;
    }
    
    /**
     * Создает страницы пагинации
     * 
     * @param array  $items -> Массив элементов страницы или объект выборки
     * @param int    $limit -> Количество элементов на 1 странице
     * @return bool
     */
    protected function initPaginationItems($items, $limit = 30) : bool
    {
        if(!is_array($items) && !$items instanceof ISectionable) {
            throw new Exception ('Элементы пагинации должны быть массивом или секциями');
        }
        
        $this->pagination__items  = $items;
        $this->pagination__limit  = $limit;
        
        // Получаем информацию о количестве элементов
        if ($this->pagination__items instanceof ISectionable)
            $this->pagination__total = $this->pagination__items->count();
        else
            $this->pagination__total = count($this->pagination__items);

        if ($this->pagination__total == 0) {

            $this->pagination__isEmpty = true;
            return false;
        }

        // Ставим верхнее ограничение по кол товара
        // Мы допускаем, что на странице вообще не будет товара
        // Пример:
        // у нас 50 продуктов и на каждой странице мы выводим по 30
        // Для вычисления максимальной доступной страницы нам нужно 50/30
        // и получившееся число округлить в большую сторону даже если оно меньше 0,5
        // это поможем нам вывести неполный список продуктов который остался
        $this->pagination__maxIndex = ceil($this->pagination__total / $this->pagination__limit);

        if ($this->pagination__currect > $this->pagination__maxIndex) {

            $this->pagination__currectIsNotFound = true;
            return false;
        }

        // ($this->currect - 1) мы выполняем, чтобы расчет первого индекса был с 0
        $this->pagination__offset = ($this->pagination__currect - 1) * $this->pagination__limit;
        return true;
    }

    /**
     * Функция формирует автоматически заголовки, метатеги и редиректы для данной страницы
     * 
     * <b>1)</b> Если количество элементов для вывода на страницы нет, то создает исключение пустой страницы (<b>PageEmptyException</b>)<br>
     * <b>2)</b> Если такой страницы не существует, то создает исключение <b>404</b> страницы <b>PageNotFoundException</b><br>
     * <b>3)</b> Если человек перешол по url <b>/.../section/page/1</b> => перенаправляет его на страницу <b>/.../section/</b><br>
     * 
     * @return void
     * 
     * @throws PageEmptyException       -> Пустая страница
     * @throws PageNotFoundException    -> Страница не существует
     * @throws Page301RedirectException -> 301 редирект
     */
    public function initPaginationException() : void
    {
        if($this->pagination__isEmpty)            throw new PageEmptyException();
        if($this->pagination__currectIsNotFound)  throw new PageNotFoundException();
        if($this->pagination__currectIsRedirect)  throw new Page301RedirectException($this->getPaginationFirstLink());
    }

    /**
     * Функция автоматически устанавливает для страницы meta теги:
     * 
     * @param HtmlPage &$page -> Объект страницы
     * @return void
     * 
     * <b>1)</b> <link rel="canonical" ...><br>
     * <b>2)</b> <link rel="prev" ...><br>
     * <b>3)</b> <link rel="next" ...><br>
     */
    public function initPaginationSEO(HtmlPage &$page) : void
    {
        if($this->pagination_method == static::$PaginationMethod_ALL) {
            if(!$this->pagination__currectIsCanonical) {
                $page->setCanonical($this->getPaginationBaseUrl());
            }
        } else if($this->pagination_method == static::$PaginationMethod_PrevNext_NoindexFollow) {
            $index = false;
            // Разрешаем индекс для 1 страницы
            if($this->pagination__currect == 1) {
                $index = true;
            }
            
            // Для Яндекса запрещаем индексировать текст на 2,3, ... страницах
            $page->robotAccessIndex($index, 'yandex');
        }
        
        $prev = $this->getPaginationPrevLink();
        $next = $this->getPaginationNextLink();

        if($prev !== null) $page->setPrevPagination($prev);
        if($next !== null) $page->setNextPagination($next);
    }
    
    /**
     * Возвращает url основной страницы, зеркалами которой являются остальные страницы
     * 
     * @return string
     */
    public function getPaginationBaseUrl() : string
    {
        // УТОЧНЕНИЯ ПО Canocical
        // Страница show-all без фильтров (GET|POST) параметров является основной страницей
        // Страницы пагинации являются зеркалами для основной страницы show-all
        // Если даже указаны фильтры, и пользователь указал show-all => основная страница show-all без фильтров
        // В версии 2.1 была предусмотрена ситуация, когда url страницы мог иметь обязательные get параметры
        return $this->pagination__fixUrl->restoreOnOutput()
                                        ->addSection(static::$pagination__identifier)
                                        ->addSection(static::$pagination__showAllIdentifier)
                                        ->removeParamsFromFlag(!$this->pagination__paramsImportant)
                                        ->getRelative();
    }
    
    /**
     * Возвращает смещение отностительно 1 элемента
     * 
     * @return int
     */
    public function getPaginationOffset() : int
    {
        return $this->pagination__offset;
    }
    
    /**
     * Возвращает количество доступных элементов
     * 
     * @return int
     */
    public function getPaginationTotal() : int
    {
        return $this->pagination__total;
    }
    
    /**
     * Возвращает Url страницы
     * 
     * @return string
     */
    public function getPaginationUrl() : string
    {
        return $this->pagination__url;
    }

    /**
     * Возвращает Url страницы без номера текущей страницы
     * @return \Look\Url\Builder
     */
    public function getPaginationFixUrl() : \Look\Url\Builder
    {
        return $this->pagination__fixUrl;
    }

    /**
     * Возвращает ссылку на первую страницу
     * 
     * @return string
     */
    public function getPaginationFirstLink() : string
    {        
        return $this->pagination__fixUrl->restoreOnOutput()
                                        //->removeParamsFromFlag(!$this->pagination__paramsImportant)
                                        ->getRelative();
    }
    
    /**
     * Возвращает номер текущей страницы
     * 
     * @return int Номер текущей страницы
     */
    public function getPaginationCurrect() : int
    {
        return $this->pagination__currect;
    }

    /**
     * Возвращает ограничения на количество элементов на 1 страницы
     * 
     * @return int Номер текущей страницы
     */
    public function getPaginationLimit() : int
    {
        return $this->pagination__limit;
    }
    
    /**
     * Возвращает максимальное значение страницы пагинации
     * 
     * @return int Номер последней страницы
     */
    public function getPaginationMaxPage() : int
    {
        return $this->pagination__maxIndex;
    }
    
    /**
     * Возвращает статус активности отображения полного списка
     * 
     * @return bool
     */
    public function paginationShowAll() : bool
    {
        return $this->pagination__showAll;
    }
    
    /**
     * Возвращает корректный список элементов
     * 
     * @return mixed
     */
    public function getPaginationCurrectList()
    {
        // Выборка колекции доступна по интерфесу секций
        if ($this->pagination__items instanceof ISectionable) {

            if ($this->paginationShowAll()) {
                return $this->pagination__items->get();
            }

            return $this->pagination__items->limit($this->pagination__limit)
                                           ->offset($this->pagination__offset)
                                           ->get();
        }

        // Обычный интерфейс array
        
        if ($this->paginationShowAll()) {
            return $this->pagination__items;
        }

        return array_splice($this->pagination__items, $this->pagination__offset, $this->pagination__limit);
    }
    
    /**
     * Проверяет существование предыдущей страницы
     * 
     * @return bool
     */
    public function paginationHasPrev() : bool
    {
        return $this->pagination__currect > 1;
    }
    
    /**
     * Проверяет существование следующей страницы
     * 
     * @return bool
     */
    public function paginationHasNext() : bool
    {
        return $this->pagination__currect < $this->pagination__maxIndex;
    }
    
    /**
     * Возвращает ссылку на предыдущую страницу
     * 
     * @return string|null Возвращет ссылку или <b>NULL</b> если предыдущей страницы не существует
     */
    public function getPaginationPrevLink() : ?string
    {
        if($this->paginationHasPrev()) {
            
            // SEO: 
            // Для страницы 1 убераем индекс страницы
            if($this->pagination__currect - 1 == 1) {
                
                return $this->getPaginationFirstLink();
            }
            
            return $this->pagination__fixUrl->restoreOnOutput()
                                            ->addSection(static::$pagination__identifier)
                                            ->addSection($this->pagination__currect - 1)
                                            //->removeParamsFromFlag(!$this->pagination__paramsImportant)
                                            ->getRelative();
        }
        
        return null;
    }
    
    /**
     * Возвращает ссылку на следующую страницу
     * 
     * @return string|null Возвращет ссылку или <b>FALSE</b> если следующей страницы не существует
     */
    public function getPaginationNextLink() : ?string
    {
        if($this->paginationHasNext()) {
            
            return $this->pagination__fixUrl->restoreOnOutput()
                                            ->addSection(static::$pagination__identifier)
                                            ->addSection($this->pagination__currect + 1)
                                            //->removeParamsFromFlag(!$this->pagination__paramsImportant)
                                            ->getRelative();
        }
        
        return null;
    }
    
    /**
     * Возвращает ссылку на последнюю страницу
     * 
     * @return string
     */
    public function getPaginationLastLink() : string
    {
        return $this->pagination__fixUrl->restoreOnOutput()
                                        ->addSection(static::$pagination__identifier)
                                        ->addSection($this->pagination__maxIndex)
                                        //->removeParamsFromFlag(!$this->pagination__paramsImportant)
                                        ->getRelative();
    }
    
    /**
     * Возвращает url страницы, для просмотре всех страниц
     * 
     * @return string
     */
    public function getPaginationShowAllPage() : string
    {
        return $this->pagination__fixUrl->restoreOnOutput()
                                        ->addSection(static::$pagination__identifier)
                                        ->addSection(static::$pagination__showAllIdentifier)
                                        //->removeParamsFromFlag(!$this->pagination__paramsImportant)
                                        ->getRelative();
    }
    
    /**
     * Возвращает массив ссылок на доступные ссылки пагинации с учетом радиуса
     * <br>
     * <br>
     * Элемент массива имеет тип: ['number' => x, 'url' => '/.../section/page/x', 'isCurrect' => false|true, 'first' => false|true, 'last' => false|true]<br>
     * isCurrect показывает, что это страница которая открыта сейчас
     * 
     * @param int  $radius     -> Радиус (количество) страниц от центра
     * @param bool $withStatus -> Возвращает массив с элементами типа [number, url, isCurrect]
     * 
     * @return array
     */
    public function getPaginationPagesLink($radius = 2, $withStatus = false) : array
    {
        $res   = [];
        $curr  = $this->pagination__currect;
        $end   = $this->pagination__maxIndex;
        $start = 1;
        
        // [currect] +1 +2 +3 ...
        // 1 2 [currect] + 1 + 2 + 3 ... 
        if($curr > $radius)
            $start = $curr - $radius;
        
        if ($curr < $end - $radius)
            $end = $curr + $radius;
        
        for($i = $start; $i <= $end; $i++) {
            
            $url    = null;
            $isCurrect = $i == $this->pagination__currect;
            
            if($i == 1)
                $url = $this->getPaginationFirstLink();
            else
                $url = $this->pagination__fixUrl->restoreOnOutput()
                                                ->addSection(self::$pagination__identifier)
                                                ->addSection($i)
                                                //->removeParamsFromFlag(!$this->pagination__paramsImportant)
                                                ->getRelative();
            
            if($withStatus)
                $res[$i] = ['number' => $i, 'url' => $url, 'isCurrect' => $isCurrect, 'first' => $i == $start, 'last' => $i == $end];
            else
                $res[$i] = $url;
        }
        
        return $res;
    }

    /**
     * Название страницы
     * <br>
     * Формат для установки: <br>
     * <b>['Страница', 'Страницы', 'Страниц']</b>
     * <br>
     * <b>['Лист', 'Листа', 'Листов']</b>
     * 
     * @param array $name
     * @return void
     */
    public function setPaginationPageName(array $name) : void
    {
        $this->pagination__pageName = $name;
    }

    /**
     * Название элемента
     * <br>
     * Формат для установки: <br>
     * <b>['Продукт', 'Продукта', 'Продуктов']</b>
     * <br>
     * <b>['Категория', 'Категории', 'Категорий']</b>
     * 
     * @param array $name
     * @return void
     */
    public function setPaginationItemName(array $name) : void
    {
        $this->pagination__itemName = $name;
    }
    
    /**
     * Устанавливает формат для вывода секции
     * <br>Доступные параметры:<br>
     * <b>%limit</b> -> Количество элементов на 1 странице
     * <br>
     * <b>%name</b>  -> Название элемента (Которое было установлено через <b>setItemName</b>)
     * <br>
     * <b>%total</b> -> Общее количество элементов
     * 
     * <b>Если нужно выводить название с большой буквы достаточно написать %Name вместо %name</b>
     * 
     * @param string $format -> Формат типа "Показать по <b>%limit</b> <b>%name</b>"
     * 
     * @return void
     */
    public function setPaginationSectionFormat($format) : void
    {
        $this->pagination__sectionFormat = $format;
    }

    /**
     * Устанавливает формат для вывода секции
     * <br>Доступные параметры:<br>
     * <b>%limit</b> -> Количество элементов на 1 странице
     * <br>
     * <b>%name</b>  -> Название элемента (Которое было установлено через <b>setItemName</b>)
     * <br>
     * <b>%total</b> -> Общее количество элементов
     * 
     * <b>Если нужно выводить название с большой буквы достаточно написать %Name вместо %name</b>
     * 
     * @param string $format -> Формат типа "Показать сразу <b>%total</b> <b>%name</b>"
     * 
     * @return void
     */
    public function setPaginationShowAllFormat($format) : void
    {
        $this->pagination__showAllFormat = $format;
    }

    /**
     * Подставялет все доступные параметры в формат строки
     * 
     * @param string $format -> Формат строки для подстановки
     * @return string
     */
    public function setPaginationParamsToFormat($format) : string
    {
        $nameLimit  = $this->getPaginationItemNameFor($this->pagination__limit);
        $nameTotal  = $this->getPaginationItemNameFor($this->pagination__total);
        $nameOffset = $this->getPaginationItemNameFor($this->pagination__offset);
        
        $pageCurrect = $this->getPaginationPageNameFor($this->pagination__currect);
        $pageMax     = $this->getPaginationPageNameFor($this->pagination__maxIndex);
        
        $arr1 = ['%limit%',   '%name@limit%',   '%Name@limit%',
                 '%total%',   '%name@total%',   '%Name@total%',
                 '%offset%',  '%name@offset%',  '%Name@offset%',
                 '%currect%', '%page@currect%', '%Page@currect%',
                 '%maxpage%', '%page@maxpage%', '%Page@maxpage%'];
        
        $arr2 = [$this->pagination__limit,    strtolower($nameLimit),   $nameLimit,
                 $this->pagination__total,    strtolower($nameTotal),   $nameTotal,
                 $this->pagination__offset,   strtolower($nameOffset),  $nameOffset,
                 $this->pagination__currect,  strtolower($pageCurrect), $pageCurrect,
                 $this->pagination__maxIndex, strtolower($pageMax),     $pageMax];
        
        return str_replace($arr1, $arr2, $format);
    }

    /**
     * Функция склонения числительных в русском языке
     *
     * @param int    $number Число которое нужно просклонять
     * @param array  $titles Массив слов для склонения
     * @return string
     **/
    private static function digitCase(int $number, array $titles) : string
    {
        if($number < 0) {
            $number = -1 * $number;
        }
        
        $cases = array (2, 0, 1, 1, 1, 2);
        return $titles[ ($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)] ];
    }
    
    /**
     * Возвращает скланенное название элемента для числа
     * 
     * @param string|number $number
     * @return string
     */
    public function getPaginationItemNameFor($number) : string
    {
        return static::digitCase($number, $this->pagination__itemName);
    }
    
    /**
     * Возвращает скланенное название элемента для страниц
     * 
     * @param string|number $number
     * @return string
     */
    public function getPaginationPageNameFor($number) : string
    {
        return static::digitCase($number, $this->pagination__pageName);
    }
    
    /**
     * Возвращает строку для вывода текста для секции
     * 
     * @return string
     */
    public function getPaginationSectionText() : string
    {
        return $this->setPaginationParamsToFormat($this->pagination__sectionFormat);
    }

    /**
     * Возвращает строку для вывода текста для показа сразу всех элементов
     * 
     * @return string
     */
    public function getPaginationShowAllText() : string
    {
        return $this->setPaginationParamsToFormat($this->pagination__showAllFormat);
    }
}