<?php

namespace Look\Exchange1C\Entity;

/**
 * Класс торгового предложения
 */
class Offer
{
    /** @var string Ид */
    protected $id;
    /** @var string Ид Продукта */
    protected $product1CId;
    /** @var string Ид Варианта */
    protected $variant1CId;
    /** @var bool Является ли данное торговое предложение вариантом товара */
    protected $isProductVariant;
    /** @var string БазоваяЕдиница */
    protected $unit;
    /** @var string ТипБазовойЕдиницы */
    protected $unitType;
    /** @var string Артикул */
    protected $article;
    /** @var string ШтрихКод */
    protected $barcode;
    /** @var string Наименование */
    protected $name;
    /** @var string Количество */
    protected $count;
    /** @var array Цены */
    protected $prices;
    /** @var array Характеристики */
    protected $characteristics;
    /** @var array Количество на складе */
    protected $stocksCount;
    
    public function __construct
    (
        ?string $id,
        ?string $product1CId,
        ?string $variant1CId,
        ?bool   $isProductVariant,
        ?string $unit,
        ?array  $unitType,
        ?string $article,
        ?string $barcode,
        ?string $name,
        ?string $count,
        ?array  $prices,
        ?array  $characteristics,
        ?array  $stocksCount
    )
    {
        
    }
}
