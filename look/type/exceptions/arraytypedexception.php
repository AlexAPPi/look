<?php

namespace Look\Type\Exceptions;

use Look\Type\Converter;
use Look\Type\Exceptions\ArrayErrorException;

/**
 * Исключение, которое происходит при добавлении в массив объекта, тип который не совпадает с указанным
 */
class ArrayTypedException extends ArrayErrorException
{
    /** @var mixed Смещение */
    protected $m_arrayItemOffest;
    
    /** @var mixed Значение */
    protected $m_arrayItemValue;
    
    /** @var mixed Тип элементов массива */
    protected $m_arrayItemType;
    
    /**
     * Создает исключение связанное с добавлением элемента иного типа
     * 
     * @param mixed  $offset   -> Смещение
     * @param mixed  $value    -> Значение
     * @param string $type     -> Допустимый тип
     * @param mixed  $previous -> Дополнения
     */
    public function __construct($offset, $value, $type, $previous = null)
    {
        $valType = gettype($value);
        
        if($valType == Converter::TObject) {
            $valType = get_class($value);
        }
        
        $this->m_arrayItemOffest = $offset;
        $this->m_arrayItemValue  = $value;
        $this->m_arrayItemType   = $type;
        
        parent::__construct('Попытка добавить в массив с типом элементов: ['.$this->m_arrayItemType.'] объект типа: ['.$valType.'] с ключом: ['.$this->m_arrayItemOffest.'] значение: ['.var_export($this->m_arrayItemValue, true).']', 500, $previous);
    }
    
    /**
     * Возвращает смещение на котором произошло исключение
     * 
     * @return mixed
     */
    public function getItemOffset()
    {
        return $this->m_arrayItemOffest;
    }
    
    /**
     * Возвращает значение на котором произошло исключение
     * 
     * @return mixed
     */
    public function getItemValue()
    {
        return $this->m_arrayItemValue;
    }
    
    /**
     * Возращает тип данных которое дожно иметь значение массива
     * 
     * @return string
     */
    public function getItemType()
    {
        return $this->m_arrayItemType;
    }
}
