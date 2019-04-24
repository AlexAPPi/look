<?php

namespace Look\Exchange1C;

use SimpleXMLElement;
use simplexml_load_file;
use libxml_get_errors;
use libxml_use_internal_errors;

use Look\Exchange1C\Entity\OfferPrice;
use Look\Exchange1C\Entity\Characteristic;

/**
 * Обработчик файла типа xml стандарта CommerceML2
 *
 * Документация: http://v8.1c.ru/edi/edi_stnd/131/
 */
class CommerceML2
{
    /** @var int Ошибка связанная с файловой системой, (не удалось открыть файл/файл не найден) */
    const OpenError    = 0;
    /** @var int Ошибка связанная с парсингом файла */
    const ParseError   = 1;
    /** @var int Ошибка связанная с передачей неверного формата */
    const FormatError  = 2;
    /** @var int Ошибка связанная с не поддерживаемой версией схемы файла */
    const SupportError = 3;
    
    /** @var array Поддерживаемые версии CommerceML2 */
    protected $m_supportVersions = ['2.04', '2.07'];
    /** @var string Версия файла */
    protected $m_version;
    /** @var int Время создания файла */
    protected $m_createDate;
    /** @var bool Файл загружен */
    protected $m_load;
    /** @var SimpleXMLElement XML объект структуры файла */
    protected $m_xml;
    /** @var bool Содержит каталог */
    protected $m_hasProduct    = false;
    /** @var bool Содержит предложения */
    protected $m_hasOffer      = false;
    /** @var bool Содержит типы цен */
    protected $m_hasPriceType  = false;
    /** @var bool Содержит информацию о складах */
    protected $m_hasStock      = false;
    /** @var bool Содержит только изменения каталога */
    protected $m_importOnlyNew = false;
    /** @var int Смещение относительно начального предложения */
    protected $m_importOfferOffset     = 0;
    /** @var int Смещение относительно начального продукта */
    protected $m_importProductOffset   = 0;
    /** @var int Смещение относительно начального склада */
    protected $m_importStockOffset     = 0;
    /** @var int Смещение относительно начального типа цены */
    protected $m_importPriceTypeOffset = 0;
    /** @var int Код ошибки */
    protected $m_error = -1;
    /** @var null|array Ошибки парсинга */
    protected $m_parseError = [];
    
    /**
     * Устанавливает смещение для импорта товаров
     * @param int $offset Смещение относительно 1 товара
     * @return $this
     */
    public function setProductOffset(int $offset) { $this->m_importProductOffset = $offset; return $this; }
    
    /**
     * Устанавливает смещение для импорта данных о типе цены
     * @param int $offset Смещение относительно 1 типа
     * @return $this
     */
    public function setPriceTypeOffset(int $offset) { $this->m_importPriceTypeOffset = $offset; return $this; }
    
    /**
     * Устанавливает смещение для импорта данных о складе
     * @param int $offset Смещение относительно 1 склада
     * @return $this
     */
    public function setStockOffset(int $offset) { $this->m_importStockOffset = $offset; return $this; }
    
    /**
     * Устанавливает смещение для импорта предложений
     * @param int $offset Смещение относительно 1 предложения
     * @return $this
     */
    public function setOfferOffset(int $offset) { $this->m_importOfferOffset = $offset; return $this; }    

    /**
     * Проверяет, может ли файл обработать данную версию
     * @param string $version
     * @return bool
     */
    public function checkSupportVersion(string $version) { return in_array($version, $this->m_supportVersions); }
    
    /**
     * Версия файла
     * @return string
     */
    public function getVersion() { return $this->m_version; }
    
    /**
     * Время формирования файла
     * @return int
     */
    public function getCreateDate() { return $this->m_createDate; }
    
    /**
     * Содержит данные о товарах
     * @return bool
     */
    public function hasImport() { return $this->m_hasProduct; }
    
    /**
     * Содержит данные о типах цен
     * @return bool
     */
    public function hasPriceType() { return $this->m_hasPriceType; }
    
    /**
     * Содержит данные о складах
     * @return bool
     */
    public function hasStock() { return $this->m_hasStock; }
    
    /**
     * Содержит данные о предложениях
     * @return bool
     */
    public function hasOffer() { return $this->m_hasOffer; }
    
    /**
     * Содержит только изменения каталога
     * @return bool
     */
    public function importOnlyNew() { return $this->m_importOnlyNew; }
    
    /**
     * Возвращает статус загрузки файла
     * @return bool
     */
    public function isLoad() { return $this->m_load; }
    
    /**
     * Проверяет, возникла ли ошибка
     * @return bool
     */
    public function hasError() : bool { return $this->m_error !== -1; }
    
    /**
     * Возвращает код ошибки
     * @return array
     */
    public function getError() : int { return $this->m_error; }
    
    /**
     * Возвращаем список ошибок парсинга файла
     * @return array
     */
    public function getParseError() : array { return $this->m_parseError; }
    
    /**
     * Выполняет предарительную загрузку данных файла
     * @param string $filepath -> Путь к файлу
     * @return boolean
     */
    public function __construct(string $filepath)
    {
        $this->m_load = false;
        if(is_readable($filepath)) {
            $prev = libxml_use_internal_errors();
            try {
                libxml_use_internal_errors(true);
                $this->m_xml = simplexml_load_file($filepath);
                if($this->m_xml != false) {

                    // Определяем версию и дату формирования документа
                    foreach ($this->m_xml->attributes() as $key => $val) {
                        switch($key) {
                            case 'ВерсияСхемы':      $this->m_version    = (string)$val;            break;
                            case 'ДатаФормирования': $this->m_createDate = strtotime((string)$val); break;
                            default: break;
                        }
                    }

                    // Не удалось проверить версию или дату создания файла
                    if(empty($this->m_version) || empty($this->m_createDate)) {
                        $this->m_error = static::FormatError;
                    }
                    // Проверяем, поддержиается ли версия данного файла
                    else if($this->checkSupportVersion($this->m_version)) {

                        if(isset($this->m_xml->Каталог))          $this->m_hasProduct = true;
                        if(isset($this->m_xml->ПакетПредложений)) $this->m_hasOffer   = true;

                        if($this->m_hasOffer) {
                            $this->m_hasStock     = isset($this->m_xml->ПакетПредложений[0]->Склады[0]);
                            $this->m_hasPriceType = isset($this->m_xml->ПакетПредложений[0]->ТипыЦен[0]);
                        }

                        if($this->m_hasProduct) {
                            $this->m_importOnlyNew = $this->detectImportOnlyNew();
                        }

                        $this->m_load = true;
                    }
                    // Данная версия файла не поддерживается
                    else {
                        $this->m_error = static::SupportError;
                    }
                }
                else {
                    $this->m_error      = static::ParseError;
                    $this->m_parseError = libxml_get_errors();
                    libxml_clear_errors();
                }
            }
            // Возвращаем заданное значение
            finally { libxml_use_internal_errors($prev); }
        }
        // Не удалось открыть файл или файл не существует
        else { $this->m_error = static::OpenError; }
    }
    
    /**
     * Определяет, что данный файл содержит только изменения каталога
     * @return bool
     */
    protected function detectImportOnlyNew()
    {
        $importOnlyNew = false;
        
        if(isset($this->m_xml->Каталог)) {
            foreach ($this->m_xml->Каталог[0]->attributes() as $key => $val) {
                if ($key == 'СодержитТолькоИзменения' && $val == 'true') {
                    $importOnlyNew = true;
                    break;
                }
            }
        }
        
        return $importOnlyNew;
    }
    
    /**
     * Выполняет обход всех элементов из списка
     * @param object   $xml           -> Селектор XML файла
     * @param callable $parseHandler  -> Функция (обработчик), которая принимает 2 аргумента:<br>
     *                                   (SimpleXMLElement $xmlElement, int $currectPosition), где<br>
     *                                   $xmlElement - Элемент объекта чтения XML<br>
     *                                   $currectPosition - Корректная позиция данного элемента в "древе" элементов<br>
     *                                   <b>при передаче null будут использоваться стандартный парсер</b>
     * 
     * @param callable $dataHandler  -> Функция (обработчик), которая принимает 2 аргумента:<br>
     *                                  (object $data, int $currectPosition), где<br>
     *                                  $data - Данные которые вернул первый обработчик<br>
     *                                  $currectPosition - Корректная позиция данного элемента в "древе" элементов
     * @param int      $offset       -> Смещение относительно первого элемента
     * @param callable $defaultParse -> Базовый парсер
     */
    protected function each($xml, $parseHandler, $dataHandler, int $offset = 0, callable $defaultParse)
    {
        $callParseHandler = !empty($parseHandler) && is_callable($parseHandler);
        $callDataHandler  = !empty($dataHandler) && is_callable($dataHandler);

        if(!$callParseHandler) {
            $callParseHandler = true;
            $parseHandler = $defaultParse;
        }

        $currentPosition = 0;
        
        if(isset($xml)) {
            
            foreach ($xml as $item) {

                $currentPosition++;
                if ($currentPosition <= $offset) {
                    continue;
                }

                if($callParseHandler) {
                    $data = $parseHandler($item, $currentPosition);
                    if($callDataHandler)
                        $dataHandler($data, $currentPosition);
                }
            }
        }
        
        return $currentPosition;
    }
    
    /**
     * Выполняет обход всех тип цен из списка<br>
     * <b>Укажите смещение относительно 1 элемента при необходимости</b>
     * @param callable $parseHandler -> Функция (обработчик), которая принимает 2 аргумента:<br>
     *                                 (SimpleXMLElement $xmlElement, int $currectPosition), где<br>
     *                                  $xmlElement - Элемент объекта чтения XML<br>
     *                                  $currectPosition - Корректная позиция данного элемента в "древе" элементов<br>
     *                                  <b>при передаче null будут использоваться стандартный парсер</b>
     * 
     * @param callable $dataHandler -> Функция (обработчик), которая принимает 2 аргумента:<br>
     *                                 (object $data, int $currectPosition), где<br>
     *                                  $data - Данные которые вернул первый обработчик<br>
     *                                  $currectPosition - Корректная позиция данного элемента в "древе" элементов
     * 
     */
    public function eachPriceType($parseHandler = null, $dataHandler = null)
    {
        if($this->m_load && $this->m_hasPriceType)
        {
            return $this->each(
                $this->m_xml->ПакетПредложений[0]->ТипыЦен[0],
                $parseHandler,
                $dataHandler,
                $this->m_importPriceTypeOffset,
                static::class.'::parsePriceTypeHandler'    
            );
        }
        
        return false;
    }
    
    /**
     * Выполняет обход всех складов из списка<br>
     * <b>Укажите смещение относительно 1 элемента при необходимости</b>
     * @param callable $parseHandler -> Функция (обработчик), которая принимает 2 аргумента:<br>
     *                                 (SimpleXMLElement $xmlElement, int $currectPosition), где<br>
     *                                  $xmlElement - Элемент объекта чтения XML<br>
     *                                  $currectPosition - Корректная позиция данного элемента в "древе" элементов<br>
     *                                  <b>при передаче null будут использоваться стандартный парсер</b>
     * 
     * @param callable $dataHandler -> Функция (обработчик), которая принимает 2 аргумента:<br>
     *                                 (object $data, int $currectPosition), где<br>
     *                                  $data - Данные которые вернул первый обработчик<br>
     *                                  $currectPosition - Корректная позиция данного элемента в "древе" элементов
     * 
     */
    public function eachStock($parseHandler = null, $dataHandler = null)
    {
        if($this->m_load && $this->m_hasStock)
        {
            return $this->each(
                $this->m_xml->ПакетПредложений[0]->Склады[0],
                $parseHandler,
                $dataHandler,
                $this->m_importStockOffset,
                static::class.'::parseStockHandler'    
            );
        }
        
        return false;
    }
    
    /**
     * Выполняет обход всех продуктов из списка<br>
     * <b>Укажите смещение относительно 1 элемента при необходимости</b>
     * @param callable $parseHandler -> Функция (обработчик), которая принимает 2 аргумента:<br>
     *                                 (SimpleXMLElement $xmlElement, int $currectPosition), где<br>
     *                                  $xmlElement - Элемент объекта чтения XML<br>
     *                                  $currectPosition - Корректная позиция данного элемента в "древе" элементов<br>
     *                                  <b>при передаче null будут использоваться стандартный парсер</b>
     * 
     * @param callable $dataHandler -> Функция (обработчик), которая принимает 2 аргумента:<br>
     *                                 (object $data, int $currectPosition), где<br>
     *                                  $data - Данные которые вернул первый обработчик<br>
     *                                  $currectPosition - Корректная позиция данного элемента в "древе" элементов
     * 
     */
    public function eachProduct($parseHandler = null, $dataHandler = null)
    {
        if($this->m_load && $this->m_hasProduct)
        {
            return $this->each(
                $this->m_xml->Каталог->Товары[0],
                $parseHandler,
                $dataHandler,
                $this->m_importProductOffset,
                static::class.'::parseProductHandler'
            );
        }
        
        return false;
    }
    
    /**
     * Выполняет обход всех предложений из списка<br>
     * <b>Укажите смещение относительно 1 элемента при необходимости</b>
     * @param callable $parseHandler -> Функция (обработчик), которая принимает 2 аргумента:<br>
     *                                 (SimpleXMLElement $xmlElement, int $currectPosition), где<br>
     *                                  $xmlElement - Элемент объекта чтения XML<br>
     *                                  $currectPosition - Корректная позиция данного элемента в "древе" элементов<br>
     *                                  <b>при передаче null будут использоваться стандартный парсер</b>
     * 
     * @param callable $dataHandler -> Функция (обработчик), которая принимает 2 аргумента:<br>
     *                                 (object $data, int $currectPosition), где<br>
     *                                  $data - Данные которые вернул первый обработчик<br>
     *                                  $currectPosition - Корректная позиция данного элемента в "древе" элементов
     */
    public function eachOffer($parseHandler = null, $dataHandler = null)
    {
        if($this->m_load && $this->m_hasOffer)
        {
            return $this->each(
                $this->m_xml->ПакетПредложений[0]->Предложения[0],
                $parseHandler,
                $dataHandler,
                $this->m_importOfferOffset,
                static::class.'::parseOfferHandler'
            );
        }
        
        return false;
    }
    
    /**
     * Стандартный обработчик информации<br>
     * Отвечает за парсинг тип цен
     * @param SimpleXMLElement $xmlElement -> XML reader
     * @param int $currectPosition         -> Индекс данного склада в списке
     * @return array
     */
    public static function parsePriceTypeHandler(SimpleXMLElement $xmlElement, int $currectPosition)
    {
        $id       = (string) $xmlElement->Ид[0];
        $name     = (string) $xmlElement->Наименование[0];
        $currency = (string) $xmlElement->Валюта[0];
        $vat      = false;
        $tax      = false;
        
        if(isset($xmlElement->Налог)) {
            if($xmlElement->Налог[0]->Наименование[0] == 'НДС') {
                $vat = (string) $xmlElement->Налог[0]->УчтеноВСумме[0] == 'true';
                $tax = (string) $xmlElement->Налог[0]->Акциз[0] == 'true';
            }
        }
        
        return [
            'id'       => $id,
            'name'     => $name,
            'currency' => $currency,
            'vat'      => $vat,
            'tax'      => $tax
        ];
    }
    
    /**
     * Стандартный обработчик информации<br>
     * Отвечает за парсинг данных склада
     * @param SimpleXMLElement $xmlElement -> XML reader
     * @param int $currectPosition         -> Индекс данного склада в списке
     * @return array
     */
    public static function parseStockHandler(SimpleXMLElement $xmlElement, int $currectPosition)
    {
        $id   = (string) $xmlElement->Ид[0];
        $name = (string) $xmlElement->Наименование[0];
        
        return [
            'id'   => $id,
            'name' => $name
        ];
    }
    
    /**
     * Стандартный обработчик информации<br>
     * Отвечает за парсинг товара
     * @param SimpleXMLElement $xmlElement -> XML reader
     * @param int $currectPosition         -> Индекс данного продукта в списке
     * @return array
     */
    public static function parseProductHandler(SimpleXMLElement $xmlElement, int $currectPosition)
    {
        $id            = (string) $xmlElement->Ид[0];
        $name          = (string) $xmlElement->Наименование[0];
        $fullName      = '';
        $weight        = 0;
        $description   = isset($xmlElement->Описание) ? (string) $xmlElement->Описание[0] : '';
        $article       = isset($xmlElement->Артикул)  ? (string) $xmlElement->Артикул[0]  : '';
        $barcode       = isset($xmlElement->ШтрихКод) ? (string) $xmlElement->ШтрихКод[0] : '';
        $categoryId    = (string) $xmlElement->Группы->Ид[0];
        $subcategoryId = [];

        foreach($xmlElement->Группы->Ид as $group) {
            $subcategoryId[] = (string) $group;
        }

        foreach ($xmlElement->ЗначенияРеквизитов->ЗначениеРеквизита as $row) {
            if($row->Наименование[0] == 'Полное наименование') { $fullName    = (string) $row->Значение[0]; continue; }
            if($row->Наименование[0] == 'Описание')            { $description = (string) $row->Значение[0]; continue; }
            if($row->Наименование[0] == 'Вес')                 { $weight      = (string) $row->Значение[0]; continue; }
        }
        
        return [
            'id'             => $id,
            'article'        => $article,
            'barcode'        => $barcode,
            'name'           => $name,
            'fullname'       => $fullName,
            'description'    => $description,
            'category_id'    => $categoryId,
            'subcategory_id' => $subcategoryId,
            'weight'         => $weight
        ];
    }
    
    /**
     * Стандартный обработчик информации<br>
     * Отвечает за парсинг предложения
     * @param SimpleXMLElement $xmlElement -> XML reader
     * @param int $currectPosition         -> Индекс данного продукта в списке
     * @return array
     */
    public static function parseOfferHandler(SimpleXMLElement $xmlElement, int $currectPosition)
    {
        $id       = (string) $xmlElement->Ид[0];
        $name     = (string) $xmlElement->Наименование[0];
        $count    = (string) $xmlElement->Количество[0];
        $article  = isset($xmlElement->Артикул)  ? (string) $xmlElement->Артикул[0]  : '';
        $barcode  = isset($xmlElement->ШтрихКод) ? (string) $xmlElement->ШтрихКод[0] : '';
        $unit     = isset($xmlElement->БазоваяЕдиница) ? (string) $xmlElement->БазоваяЕдиница[0] : 'шт';
        $unitType = [];
        
        if(isset($xmlElement->БазоваяЕдиница)) {
            foreach ($xmlElement->БазоваяЕдиница->attributes() as $key => $val) {
                if ($key == 'Код')                     { $unitType['code']  = (string) $val; continue; }
                if ($key == 'НаименованиеПолное')      { $unitType['name']  = (string) $val; continue; }
                if ($key == 'МеждународноеСокращение') { $unitType['short'] = (string) $val; continue; }
            }
        }
        
        $isProductVariant = strpos($id, '#') !== false;
        $extract1CIds = explode('#', $id);
        $product1CId  = $extract1CIds[0];
        $variant1CId  = isset($extract1CIds[1]) ? $extract1CIds[1] : '';
        
        $prices          = static::parseOfferPriceHandler($xmlElement, $currectPosition);
        $stocksCount     = static::parseOfferStockCountHandler($xmlElement, $currectPosition);
        $characteristics = static::parseOfferCharacteristicsHandler($xmlElement, $currectPosition);

        return [
            'id'              => $id,
            'product_id'      => $product1CId,
            'variant_id'      => $variant1CId,
            'is_variant'      => $isProductVariant,
            'unit'            => $unit,
            'unit_type'       => $unitType,
            'article'         => $article,
            'barcode'         => $barcode,
            'name'            => $name,
            'count'           => $count,
            'prices'          => $prices,
            'characteristics' => $characteristics,
            'stocks_count'    => $stocksCount
        ];
    }
       	   
    /**
     * Стандартный обработчик информации<br>
     * Отвечает за парсинг тип цен предложения
     * @param SimpleXMLElement $xmlElement -> XML reader
     * @param int $currectPosition         -> Индекс данного продукта в списке
     * @return array Возвращает данные в формате
     */
    public static function parseOfferPriceHandler(SimpleXMLElement $xmlElement, int $currectPosition)
    {
        $data = [];

        if(isset($xmlElement->Цены) && isset($xmlElement->Цены->Цена)) {

            foreach($xmlElement->Цены->Цена as $priceXmlElement) {

                $priceId     = (string) $priceXmlElement->ИдТипаЦены[0];
                $title       = (string) $priceXmlElement->Представление[0];
                $value       = (string) $priceXmlElement->ЦенаЗаЕдиницу[0];
                $currency    = (string) $priceXmlElement->Валюта[0];
                $minCount    = (string) $priceXmlElement->МинКоличество[0];
                $unit        = (string) $priceXmlElement->Единица[0];
                $coefficient = (string) $priceXmlElement->Коэффициент[0];

                $data[$priceId] = new OfferPrice(
                    $priceId,
                    $title,
                    $value,
                    $currency,
                    $minCount,
                    $unit,
                    $coefficient
                );
            }
        }
        
        return $data;
    }
    
    /**
     * Стандартный обработчик информации<br>
     * Отвечает за парсинг остатко предложения на складах
     * @param SimpleXMLElement $xmlElement -> XML reader
     * @param int $currectPosition         -> Индекс данного продукта в списке
     * @return array Возвращает данные в формате
     */
    public static function parseOfferStockCountHandler(SimpleXMLElement $xmlElement, int $currectPosition)
    {
        $data = [];
        
        if(isset($xmlElement->Склад)) {
            
            // Обновляем количество для отдельных складов
            foreach($xmlElement->Склад as $stock) {

                $stock1CId  = 0;
                $stockCount = 0;

                foreach ($stock->attributes() as $key => $val) {
                    if ($key == 'ИдСклада')           { $stock1CId  = (string) $val; continue; }
                    if ($key == 'КоличествоНаСкладе') { $stockCount = (string) $val; continue; }
                }

                $data[$stock1CId] = $stockCount;
            }
        }
        
        return $data;
    }
    
    /**
     * Стандартный обработчик информации<br>
     * Отвечает за парсинг характеристик предложения
     * @param SimpleXMLElement $xmlElement -> XML reader
     * @param int $currectPosition         -> Индекс данного продукта в списке
     * @return array Возвращает данные в формате
     */
    public static function parseOfferCharacteristicsHandler(SimpleXMLElement $xmlElement, int $currectPosition)
    {
        $characteristics = [];
        
        if(isset($xmlElement->ХарактеристикиТовара)) {
            
            foreach($xmlElement->ХарактеристикиТовара->ХарактеристикаТовара as $characteristic) {
                
                $id    = (string) $characteristic->Ид[0];
                $name  = (string) $characteristic->Наименование[0];
                $value = (string) $characteristic->Значение[0];
                
                $characteristic[$id] = new Characteristic($id, $name, $value);
            }
        }

        return $characteristics;
    }
}

