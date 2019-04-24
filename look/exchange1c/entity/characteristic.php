<?php

namespace Look\Lib1C\Entity;

use SimpleXMLElement;
use Look\Exchange1C\ExportableTo1C;

/**
 * Класс характеристики
 *
 * @author Alexandr
 */
class Characteristic implements ExportableTo1C
{
    /** @var string Уникальный индификатор */
    protected $id;
    
    /** @var string Название характеристики */
    protected $name;
    
    /** @var string Значение характеристики */
    protected $value;
    
    /**
     * Создает новый объект характеристики
     * 
     * @param string $id    -> Уникальный индификатор
     * @param string $name  -> Название характеристики
     * @param string $value -> Значение характеристики
     */
    public function __construct(?string $id, ?string $name, ?string $value)
    {
        $this->id    = $id;
        $this->name  = $name;
        $this->value = $value;
    }

    /**
     * Возвращает Уникальный индификатор
     * @return string|null
     */
    public function getId() : ?string    { return $this->id; }
    
    /**
     * Возвращает Название характеристики
     * @return string|null
     */
    public function getName() : ?string  { return $this->name; }
    
    /**
     * Возвращает Значение характеристики
     * @return string|null
     */
    public function getValue() : ?string { return $this->value; }
    
    /** {@inheritdoc} */
    public function toCommerceML2(SimpleXMLElement &$parentElement, string $versionCode) : SimpleXMLElement
    {
        $thisElement = $parentElement->addChild('ХарактеристикаТовара');
        $thisElement->addChild('Ид', $this->id);
        $thisElement->addChild('Наименование', $this->name);
        $thisElement->addChild('Значение', (string) $this->value);
        
        return $thisElement;
    }
}
