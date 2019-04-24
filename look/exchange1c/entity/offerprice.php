<?php

namespace Look\Exchange1C\Entity;

use SimpleXMLElement;
use Look\Exchange1C\ExportableTo1C;

/**
 * Цена торгового предложения
 */
class OfferPrice implements ExportableTo1C
{
    /** @var string ИдТипаЦены */
    protected $priceId;
    /** @var string Представление */
    protected $title;
    /** @var string ЦенаЗаЕдиницу */
    protected $value;
    /** @var string Валюта */
    protected $currency;
    /** @var string Минимальное количество, от которого действует данная цена */
    protected $minCount;
    /** @var string Единица */
    protected $unit;
    /** @var string Коэффициент */
    protected $coefficient;
    
    /**
     * Создает цену торгового предложения
     * 
     * @param string|null $priceId     -> ИдТипаЦены
     * @param string|null $title       -> Представление
     * @param string|null $value       -> ЦенаЗаЕдиницу
     * @param string|null $currency    -> Валюта
     * @param string|null $minCount    -> МинКоличество
     * @param string|null $unit        -> Единица
     * @param string|null $coefficient -> Коэффициент
     */
    public function __construct(?string $priceId, ?string $title, ?string $value, ?string $currency, ?string $minCount, ?string $unit, ?string $coefficient)
    {
        $this->priceId     = $priceId;
        $this->title       = $title;
        $this->value       = $value;
        $this->currency    = $currency;
        $this->minCount    = $minCount;
        $this->unit        = $unit;
        $this->coefficient = $coefficient;
    }
    
    /**
     * Возвращает ИдТипаЦены
     * @return string|null
     */
    public function getPriceId() : ?string { return $this->priceId; }
    
    /**
     * Возвращает Представление
     * @return string|null
     */
    public function getTitle() : ?string { return $this->title; }
    
    /**
     * Возвращает ЦенаЗаЕдиницу
     * @return string|null
     */
    public function getValue() : ?string { return $this->value; }
    
    /**
     * Возвращает Назание валюты
     * @return string|null
     */
    public function getCurrency() : ?string { return $this->currency; }
    
    /**
     * Возвращает Минимальное количество, от которого действует данная цена
     * @return string|null
     */
    public function getMinCount() : ?string { return $this->minCount; }
    
    /**
     * Возвращает единицу измерения
     * @return string|null
     */
    public function getUnit() : ?string { return $this->unit; }
    
    /**
     * Возвращает Коэффициент
     * @return string|null
     */
    public function getCoefficient() : ?string { return $this->coefficient; }
    
    /** {@inheritdoc} */
    public function toCommerceML2(SimpleXMLElement &$parentElement, string $versionCode) : SimpleXMLElement
    {
        $thisElement = $parentElement->addChild('Цена');
        $thisElement->addChild('Представление', $this->title);
        $thisElement->addChild('ИдТипаЦены', $this->priceId);
        $thisElement->addChild('ЦенаЗаЕдиницу', $this->value);
        $thisElement->addChild('Валюта', $this->currency);
        $thisElement->addChild('Единица', $this->unit);
        $thisElement->addChild('МинКоличество', $this->minCount);
        $thisElement->addChild('Коэффициент', $this->coefficient);
        
        return $thisElement;
    }
}
