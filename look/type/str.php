<?php

namespace Look\Type;

use Look\Type\Converter;
use Look\Type\Traits\Macroable;
use Look\Type\Interfaces\IValue;

class Str implements IValue
{
    /** @var string EvalType */
    const EvalType = Converter::TString;

    use Macroable;
    
    /**
     * @var string Значение 
     */
    protected $value;

    /**
     * Конструктор строки
     * @param string $str
     */
    public function __construct(string $str)
    {
        $this->value = $str;
    }
    
    /**
     * Преобразует строку в bool
     * 
     * @return boolean значение или <b>NULL</b> если возникла ошибка преобразования
     */
    public function toBool()
    {
        return Converter::strToBool($this->value);
    }
    
    /**
     * Преобразует строку в integer
     * 
     * @return integer значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public function toInt()
    {
        return Converter::strToInt($this->value);
    }
    
    /**
     * Преобразует строку в unsigned integer
     * 
     * @return integer значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public function toUnsignedInt()
    {
        return Converter::strToUnsignedInt($this->value);
    }
    
    /**
     * Преобразует строку в double
     * 
     * @return double значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public function toDouble()
    {
        return Converter::strToDouble($this->value);
    }
    
    /**
     * Преобразует строку в unsigned double
     * 
     * @return double значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public function toUnsignedDouble()
    {
        return Converter::strToUnsignedDouble($this->value);
    }
    
    /**
     * Преобразует строку в integer или double
     * 
     * @param string $type  -> преобразованный тип
     * @return mixed значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public function toNumeric(&$type = null)
    {
        return Converter::strToNumeric($this->value, $type);
    }
    
    /**
     * Преобразует строку в unsigned integer или unsigned double
     * 
     * @param string $type  -> преобразованный тип
     * @return mixed значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public function toUnsignedNumeric(&$type = null)
    {
        return Converter::strToUnsignedNumeric($this->value, $type);
    }
    
    /**
     * Преобразует строку в array, строка может быть массивом,
     * если содержит более 2 ячеек
     * 
     * @param string $type     -> преобразованный тип
     * @param string $delimetr -> разделитель строки
     * @return array значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public function toArray(string $delimetr = ',')
    {
        return Converter::strToArray($this->value, $delimetr);
    }
    
    /**
     * Преобразует строку в array или int
     * 
     * @param string $type     -> преобразованный тип
     * @param string $delimetr -> разделитель строки
     * @return array значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public function toArrayOrInt(&$type = null, string $delimetr = ',')
    {
        return Converter::strToArrayOrInt($this->value, $type, $delimetr);
    }
    
    /**
     * Преобразует строку в array или double
     * 
     * @param string $type     -> преобразованный тип
     * @param string $delimetr -> разделитель строки
     * @return array значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public function toArrayOrDouble(&$type = null, string $delimetr = ',')
    {
        return Converter::strToArrayOrDouble($this->value, $type, $delimetr);
    }
    
    /**
     * Преобразует строку в array или numeric
     * 
     * @param string $type     -> преобразованный тип
     * @param string $delimetr -> разделитель строки
     * @return array значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public function toArrayOrNumeric(&$type = null, string $delimetr = ',')
    {
        return Converter::strToArrayOrNumeric($this->value, $type, $delimetr);
    }
    
    /**
     * Преобразует строку в array или unsigned integer
     * 
     * @param string $type     -> преобразованный тип
     * @param string $delimetr -> разделитель строки
     * @return array значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public function toArrayOrUnsignedInt(&$type = null, string $delimetr = ',')
    {
        return Converter::strToArrayOrUnsignedInt($this->value, $type, $delimetr);
    }
    
    /**
     * Преобразует строку в array или unsigned double
     * 
     * @param string $type     -> преобразованный тип
     * @param string $delimetr -> разделитель строки
     * @return array значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public function toArrayOrUnsignedDouble(&$type = null, string $delimetr = ',')
    {
        return Converter::strToArrayOrUnsignedDouble($this->value, $type, $delimetr);
    }
    
    /**
     * Преобразует строку в array или unsigned numeric
     * 
     * @param string $type     -> преобразованный тип
     * @param string $delimetr -> разделитель строки
     * @return array значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public function toArrayOrUnsignedNumeric(&$type = null, string $delimetr = ',')
    {
        return Converter::strToArrayOrUnsignedNumeric($this->value, $type, $delimetr);
    }
    
    /**
     * Преобразует строку в array со значениями integer
     * 
     * @param string $delimetr -> разделитель строки
     * @return array значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public function toIntegerArray(string $delimetr = ',')
    {
        return Converter::strToIntegerArray($this->value, $delimetr);
    }
    
    /**
     * Преобразует строку в array со значениями unsigned integer
     * 
     * @param string $delimetr -> разделитель строки
     * @return array значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public function toUnsignedIntegerArray(string $delimetr = ',')
    {
        return Converter::strToUnsignedIntegerArray($this->value, $delimetr);
    }
    
    /**
     * Преобразует строку в array со значениями double
     * 
     * @param string $delimetr -> разделитель строки
     * @return array значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public function toDoubleArray(string $delimetr = ',')
    {
        return Converter::strToDoubleArray($this->value, $delimetr);
    }
    
    /**
     * Преобразует строку в array со значениями unsigned double
     * 
     * @param string $delimetr -> разделитель строки
     * @return array значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public function toUnsignedDoubleArray(string $delimetr = ',')
    {
        return Converter::strToUnsignedDoubleArray($this->value, $delimetr);
    }
            
    /**
     * Преобразует строку в array со значениями integer или double
     * 
     * @param string $delimetr -> разделитель строки
     * @return array значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public function toNumericArray(string $delimetr)
    {
        return Converter::strToNumericArray($this->value, $delimetr);
    }
    
    /**
     * Преобразует строку в array со значениями unsigned integer или unsigned double
     * 
     * @param string $delimetr -> разделитель строки
     * @return array значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public function toUnsignedNumericArray(string $delimetr)
    {
        return Converter::strToUnsignedNumericArray($this->value, $delimetr);
    }
    
    /**
     * Преобразует строку в нижний геристр
     * @return type
     */
    public function toLowerCase() : String
    {
        return new String(mb_strtolower($this->value));
    }
    
    /**
     * Преобразует строку в нижний геристр
     * @return type
     */
    public function toUpperCase() : String
    {
        return new String(mb_strtoupper($this->value));
    }
    
    /**
     * Возвращает длинну строки
     * @return int
     */
    public function lenght()
    {
        return mb_strlen($this->value);
    }
    
    /** {@inheritdoc} */
    public static function __getEvalType(): string
    {
        return static::EvalType;
    }

    /** {@inheritdoc} */
    public function setValue($value) : void
    {
        $this->value = $value;
    }

    /** {@inheritdoc} */
    public function __toString() : string
    {
        return $this->value;
    }
}
