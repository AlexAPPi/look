<?php

namespace Look\API\Type;

use Look\Type\Traits\Macroable;
use Look\API\Type\Interfaces\IScalar;

class Str implements IScalar
{
    use Macroable;
    
    /**
     * @var string Значение 
     */
    protected $value;
    
    /** {@inheritdoc} */
    public static function __extendsSystemType(): bool { return self::TString; }
    
    /** {@inheritdoc} */
    public static function __getSystemEvalType(): string { return self::TString; }
    
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
        return TypeManager::strToBool($this->value);
    }
    
    /**
     * Преобразует строку в integer
     * 
     * @return integer значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public function toInt()
    {
        return TypeManager::strToInt($this->value);
    }
    
    /**
     * Преобразует строку в unsigned integer
     * 
     * @return integer значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public function toUnsignedInt()
    {
        return TypeManager::strToUnsignedInt($this->value);
    }
    
    /**
     * Преобразует строку в double
     * 
     * @return double значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public function toDouble()
    {
        return TypeManager::strToDouble($this->value);
    }
    
    /**
     * Преобразует строку в unsigned double
     * 
     * @return double значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public function toUnsignedDouble()
    {
        return TypeManager::strToUnsignedDouble($this->value);
    }
    
    /**
     * Преобразует строку в integer или double
     * 
     * @param string $type  -> преобразованный тип
     * @return mixed значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public function toNumeric(&$type = null)
    {
        return TypeManager::strToNumeric($this->value, $type);
    }
    
    /**
     * Преобразует строку в unsigned integer или unsigned double
     * 
     * @param string $type  -> преобразованный тип
     * @return mixed значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public function toUnsignedNumeric(&$type = null)
    {
        return TypeManager::strToUnsignedNumeric($this->value, $type);
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
        return TypeManager::strToArray($this->value, $delimetr);
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
        return TypeManager::strToArrayOrInt($this->value, $type, $delimetr);
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
        return TypeManager::strToArrayOrDouble($this->value, $type, $delimetr);
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
        return TypeManager::strToArrayOrNumeric($this->value, $type, $delimetr);
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
        return TypeManager::strToArrayOrUnsignedInt($this->value, $type, $delimetr);
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
        return TypeManager::strToArrayOrUnsignedDouble($this->value, $type, $delimetr);
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
        return TypeManager::strToArrayOrUnsignedNumeric($this->value, $type, $delimetr);
    }
    
    /**
     * Преобразует строку в array со значениями integer
     * 
     * @param string $delimetr -> разделитель строки
     * @return array значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public function toIntegerArray(string $delimetr = ',')
    {
        return TypeManager::strToIntegerArray($this->value, $delimetr);
    }
    
    /**
     * Преобразует строку в array со значениями unsigned integer
     * 
     * @param string $delimetr -> разделитель строки
     * @return array значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public function toUnsignedIntegerArray(string $delimetr = ',')
    {
        return TypeManager::strToUnsignedIntegerArray($this->value, $delimetr);
    }
    
    /**
     * Преобразует строку в array со значениями double
     * 
     * @param string $delimetr -> разделитель строки
     * @return array значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public function toDoubleArray(string $delimetr = ',')
    {
        return TypeManager::strToDoubleArray($this->value, $delimetr);
    }
    
    /**
     * Преобразует строку в array со значениями unsigned double
     * 
     * @param string $delimetr -> разделитель строки
     * @return array значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public function toUnsignedDoubleArray(string $delimetr = ',')
    {
        return TypeManager::strToUnsignedDoubleArray($this->value, $delimetr);
    }
            
    /**
     * Преобразует строку в array со значениями integer или double
     * 
     * @param string $delimetr -> разделитель строки
     * @return array значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public function toNumericArray(string $delimetr)
    {
        return TypeManager::strToNumericArray($this->value, $delimetr);
    }
    
    /**
     * Преобразует строку в array со значениями unsigned integer или unsigned double
     * 
     * @param string $delimetr -> разделитель строки
     * @return array значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public function toUnsignedNumericArray(string $delimetr)
    {
        return TypeManager::strToUnsignedNumericArray($this->value, $delimetr);
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
        return new Str(mb_strtoupper($this->value));
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
        return self::TString;
    }

    /** {@inheritdoc} */
    public function setValue($value) : void
    {
        $this->value = $value;
    }
    
    /** {@inheritdoc} */
    public function getValue()
    {
        return $this->value;
    }
    
    /** {@inheritdoc} */
    public function __toString() : string
    {
        return $this->value;
    }
}
