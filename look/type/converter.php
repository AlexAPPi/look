<?php

namespace Look\Type;

use DateTime;
use Iterator;

/**
 * Класс предназначения для работы с типами данных:
 * 
 * - Преобразовывание одних типов в другие
 * - Сравнение типов и автоматическое преобразование
 *
 * @author Alexandr Shamarin <alexsandrshamarin@yandex.ru>
 */
class Converter
{    
    /** Неопределенный тип */
    const TNULL = 'NULL';
    
    /** Тип объекта */
    const TObject = 'object';
    
    /** Тип функции */
    const TCallable = 'callable';
    
    /** Логичесткое */
    const TBool = 'bool';
    
    /** Логичесткое */
    const TBool2 = 'boolean';
    
    /** Тип int */
    const TInteger = 'int';
    
    /** Тип int */
    const TInteger2 = 'integer';
    
    /** Тип unsigned int */
    const TUnsignedInteger = 'unsigned ' . self::TInteger;

    /** Тип float */
    const TFloat = 'float';
    
    /** Тип double */
    const TDouble = self::TFloat;

    /** Тип double */
    const TDouble2 = 'double';
    
    /** Тип unsigned double */
    const TUnsignedDouble = 'unsigned ' . self::TDouble;

    /** Тип int|double */
    const TNumeric = 'numeric';
    
    /** Тип unsigned int|double */
    const TUnsignedNumeric = 'unsigned ' . self::TNumeric;
    
    /** Тип string */
    const TString = 'string';

    /** Тип array */
    const TArray = 'array';

    /** Пустой массив */
    const TEmptyArray = 'empty ' . self::TArray;

    /** Массив из разных типов */
    const TMultiArray = self::TArray;

    /** Массив из чисел */
    const TNumericArray = 'numeric ' . self::TArray;

    /** Массив только из целых чисел */
    const TIntegerArray = self::TInteger . ' ' . self::TArray;
    
    /** Массив только из дробных чисел */
    const TDoubleArray = self::TDouble . ' ' . self::TArray;
    
    /** Массив только из положительных чисел */
    const TUnsignedNumericArray = 'unsigned ' . self::TNumericArray;
    
    /** Массив только из целых положительных чисел */
    const TUnsignedIntegerArray = self::TUnsignedInteger . ' ' . self::TArray;
    
    /** Массив только из дробных положительных чисел */
    const TUnsignedDoubleArray = self::TUnsignedDouble . ' ' . self::TArray;
    
    /** Массив только из true|false */
    const TBoolArray = self::TBool . ' ' . self::TArray;
    
    /** Не определенный тип данных */
    const TMixed = 'mixed';
    
    /** Enum тип */
    const TEnum = 'enum';
    
    /** @var array Преобразование bool */
    private static $boolVals = [
        'y'     => true,
        'n'     => false,
        'yes'   => true,
        'no'    => false,
        'true'  => true,
        'false' => false
    ];
    
    /** @var array Одинаковые типы */
    private static $compareType = [

        'arr'     => self::TArray,
        'array'   => self::TArray,
        
        'str'     => self::TString,
        'string'  => self::TString,
        
        'boolean' => self::TBool,
        'bool'    => self::TBool,
        
        'integer' => self::TInteger,
        'int'     => self::TInteger,
        
        'float'   => self::TFloat,
        'double'  => self::TFloat,
    ];
    
    /** @var array Список скалярных значений */
    private static $scalarTypes = [
        self::TBool,
        self::TBool2,
        self::TInteger,
        self::TInteger2,
        self::TFloat,
        self::TDouble,
        self::TDouble2,
        self::TString,
        self::TNumeric
    ];
    
    /** @var array Стандартные типы и подмена */
    public static $standartSpecTypes = [
        
        // Enum
        self::TEnum => \Look\Type\Enum::class,
        
        // Array
        self::TIntegerArray => \Look\Type\IntegerArray::class,
        self::TDoubleArray  => \Look\Type\DoubleArray::class,
        self::TNumericArray => \Look\Type\NumericArray::class,

        // Unsigned array
        self::TUnsignedIntegerArray => \Look\Type\UnsignedIntegerArray::class,
        self::TUnsignedDoubleArray  => \Look\Type\UnsignedDoubleArray::class,
        self::TUnsignedNumericArray => \Look\Type\UnsignedNumericArray::class,

        // Unsigned scalar
        self::TUnsignedInteger => \Look\Type\UnsignedInteger::class,
        self::TUnsignedDouble  => \Look\Type\UnsignedDouble::class,
        self::TUnsignedNumeric => \Look\Type\UnsignedNumeric::class,
    ];
    
    /**
     * Добавялет новый тип данных
     * 
     * @param string $type  -> Тип
     * @param string $class -> Класс
     */
    public static function addTypeForClass(string $type, string $class) : void
    {
        static::$standartSpecTypes[$type] = $class;
    }

    /**
     * Возвращает класс для указанного типа
     * 
     * @param string $type -> Тип
     * @return string|null название класса или NULL если класса не существует
     */
    public static function getClassForType(string $type) : ?string
    {
        if(isset(static::$standartSpecTypes[$type])) {
            return static::$standartSpecTypes[$type];
        }
        
        return null;
    }
    
    /**
     * Возвращает класс для указанного типа
     * 
     * @param string $class -> Типизированный класс
     * @return  string|null название типа или NULL если класса не существует
     */
    public static function getTypeForClass(string $class) : ?string
    {
        foreach(static::$standartSpecTypes as $type => $className) {
            if($className == $class) {
                return $type;
            }
        }
        
        return null;
    }
    
    /**
     * Проверяет является ли данный тип скалярным
     * @param string $type -> Тип
     * @return bool
     */
    public static function isScalarType(string $type) : bool
    {
        return strpos($type, '\\') === false && in_array($type, static::$scalarTypes);
    }
    
    /**
     * Возвращает тип массива для указанного типа
     * @return string
     */
    public static function getArrayTypeFor(string $type) : string
    {
        return $type . ' ' . static::TArray;
    }
    
    /**
     * Сравнивает 2 типа
     * 
     * Пригодится, когда значение базового типа (boolean, integer, float)
     * нужно сопоставить с сокращениями (bool, int, double) и т.п
     * 
     * @param string $type1 -> Тип 1
     * @param string $type2 -> Тип 2
     * @return boolean
     */
    public static function compareTypes(string $type1, string $type2) : bool
    {        
        if($type1 == $type2)
            return true;
        
        return self::$compareType[$type1] == self::$compareType[$type2];
    }
    
    /**
     * Сравнивает 2 типа, причем 1 тип базовый, если он строковый,
     * то считается что параметр может быть любого скалярного типа
     * 
     * Пригодится, когда значение базового типа (boolean, integer, float)
     * нужно сопоставить с сокращениями (bool, int, double) и т.п
     * 
     * а тип string сопоставить с другими
     * 
     * @param string $funcType -> Тип который принимает функция
     * @param string $type     -> Тип который был определен
     * @return boolean
     */
    public static function compareTypesFunc(string $funcType, string $type) : bool
    {
        if($funcType == $type) {
            return true;
        }
        
        // Передан объект
        if($type === self::TObject && class_exists($funcType, true)) {
            return true;
        }

        // Любой тип можно конвертировать в строку
        if($funcType == self::TString && $type !== self::TObject) {
            return true;
        }
                
        return self::$compareType[$funcType] == self::$compareType[$type];
    }

    /**
     * Определяет, является ли этот тип дочерним
     * 
     * Например тип unsigned int вытикает из типа int,<br>
     * bool array из array и т.п<br>
     * 
     * <b>Если типы совпадают функция вернет TRUE</b>
     * 
     * @param string $sub  -> Дочерний тип
     * @param string $base -> Базовый тип
     * @return bool
     */
    public static function compareSubTypes(string $sub, string $base) : bool
    {
        // Т.к все типы собираются из под строк
        // например тип unsigned double array
        // складывается из TUnsignedDouble + TArray
        // Если вхождение строки присудствует, значит тип определен верно
        
        return strpos($sub, $base) !== false;
    }
    
    /**
     * Проверяет, является ли данный тип с приставкой unsigned
     * 
     * @param string $type -> Тип 
     * @return bool
     */
    public static function isUnsignedType(string $type) : bool
    {
        return strpos('unsigned', $type) !== false;
    }
    
    /**
     * Извлечает значение типа убирая unsigned
     * 
     * @param string $type -> Тип
     * @return string|null NULL если ошибка извлечения
     */
    public static function extractUnsignedType(string $type) : ?string
    {
        $str = str_replace('unsigned ', '', $type);
        return Checker::typeExists($str) ? $str : null;
    }
    
    /**
     * Извлечает значение типа элемента массива
     * 
     * @param string $arrayType -> Тип массива
     * @return string|null NULL если ошибка извлечения
     */
    public static function extractArrayTypeItem(string $arrayType) : ?string
    {
        $str = str_replace(' ' . self::TArray, '', $arrayType);
        return Checker::typeExists($str) ? $str : null;
    }
    
    /**
     * Возвращает название согласованного типа или типа который подставляется вместо указанного
     * 
     * @param string $type
     * @return string
     */
    public static function getFixType(string $type) : string
    {
        if(isset(self::$compareType[$type])) {
            return self::$compareType[$type];
        }
        
        return $type;
    }
        
    /**
     * Преобразует любые значения в bool
     * 
     * @param mixed $var -> Значение
     * @return boolean значение или <b>NULL</b> если возникла ошибка преобразования
     */
    public static function anyToBool($var)
    {
        if(is_bool($var))
            return $var;
        
        if($var === 0 || $var === 0.0 || $var === '0') return false;
        if($var === 1 || $var === 1.0 || $var === '1') return true;
        
        if(is_string($var))
            return self::strToBool($var);
        
        return null;
    }

    /**
     * Преобразует строку в bool
     * 
     * @param string $str   -> строка
     * @return boolean значение или <b>NULL</b> если возникла ошибка преобразования
     */
    public static function strToBool(string $str)
    {        
        $v = strtolower($str);
        
        // bool
        if (isset(self::$boolVals[$v])) {

            return self::$boolVals[$v];
        }

        return null;
    }

    /**
     * Преобразует строку в integer
     * 
     * @param string $str   -> строка
     * @return integer значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public static function strToInt(string $str)
    {
        $fix    = str_replace(' ', '', trim($str));
        $status = preg_match('/^-?([0-9])+$/', $str);

        if ($status == 0) {
            return false;
        }

        return (int) $fix;
    }

    /**
     * Преобразует строку в unsigned integer
     * 
     * @param string $str   -> строка
     * @return integer значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public static function strToUnsignedInt(string $str)
    {
        $fix    = str_replace(' ', '', trim($str));
        $status = preg_match('/^([0-9])+$/', $str);

        if ($status == 0) {
            return false;
        }

        return (int) $fix;
    }

    /**
     * Преобразует строку в double
     * 
     * @param string $str   -> строка
     * @return double значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public static function strToDouble(string $str)
    {
        // Возможность сокращать первый ноль
        $str    = $str[0] == '.' ? '0' . $str : $str;
        $fix    = str_replace(' ', '', trim($str));
        $status = preg_match('/^-?([0-9])+([\.]([0-9])*)?$/', $str);

        if ($status == 0) {
            return false;
        }

        return (double) $fix;
    }

    /**
     * Преобразует строку в unsigned double
     * 
     * @param string $str   -> строка
     * @return double значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public static function strToUnsignedDouble(string $str)
    {
        $fix    = str_replace(' ', '', trim($str));
        $status = preg_match('/^([0-9])+([\.|,]([0-9])*)?$/', $str);

        if ($status == 0) {
            return false;
        }

        return (double) $fix;
    }

    /**
     * Преобразует строку в integer или double
     * 
     * @param string $str   -> строка
     * @param string $type  -> преобразованный тип
     * @return mixed значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public static function strToNumeric(string $str, &$type = null)
    {
        $isInt = self::strToInt($str);

        if ($isInt !== false) {

            $type = self::TInteger;
            return $isInt;
        }

        $isDouble = self::strToDouble($str);

        if ($isDouble !== false) {

            $type = self::TDouble;
            return $isDouble;
        }

        return false;
    }

    /**
     * Преобразует строку в unsigned integer или unsigned double
     * 
     * @param string $str   -> строка
     * @param string $type  -> преобразованный тип
     * @return mixed значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public static function strToUnsignedNumeric(string $str, &$type = null)
    {
        $isInt = self::strToUnsignedInt($str);

        if ($isInt !== false) {

            $type = self::TInteger;
            return $isInt;
        }

        $isDouble = self::strToUnsignedDouble($str);

        if ($isDouble !== false) {

            $type = self::TDouble;
            return $isDouble;
        }

        return false;
    }

    /**
     * Преобразует строку в array, строка может быть массивом,
     * если содержит более 2 ячеек
     * 
     * @param string $str      -> строка
     * @param string $type     -> преобразованный тип
     * @param string $delimetr -> разделитель строки
     * @return array значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public static function strToArray(string $str, string $delimetr = ',')
    {
        $convert = explode($delimetr, $str);

        // Преобразование для 2 и выше ячеек
        if (count($convert) > 1) {

            $check = implode($delimetr, $convert);

            // Проверка обратного совпадения
            if ($str === $check) {

                return $convert;
            }
        }

        return false;
    }

    /**
     * Преобразует строку в array или integer
     * 
     * @param string $str      -> строка
     * @param string $type     -> преобразованный тип
     * @param string $delimetr -> разделитель строки
     * @return array значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public static function strToArrayOrInt(string $str, &$type = null, string $delimetr = ',')
    {
        $arr = self::strToArray($str, $delimetr);

        if ($arr !== false) {
            $type = self::TArray;
            return $arr;
        }

        $tmp = self::strToInt($str);

        if ($tmp !== false) {
            $type = self::TInteger;
            return $tmp;
        }

        return false;
    }

    /**
     * Преобразует строку в array или double
     * 
     * @param string $str      -> строка
     * @param string $type     -> преобразованный тип
     * @param string $delimetr -> разделитель строки
     * @return array значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public static function strToArrayOrDouble(string $str, &$type = null, string $delimetr = ',')
    {
        $arr = self::strToArray($str, $delimetr);

        if ($arr !== false) {
            $type = self::TArray;
            return $arr;
        }

        $tmp = self::strToDouble($str);

        if ($tmp !== false) {
            $type = self::TDouble;
            return $tmp;
        }

        return false;
    }

    /**
     * Преобразует строку в array или numeric
     * 
     * @param string $str      -> строка
     * @param string $type     -> преобразованный тип
     * @param string $delimetr -> разделитель строки
     * @return array значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public static function strToArrayOrNumeric(string $str, &$type = null, string $delimetr = ',')
    {
        $arr = self::strToArray($str, $delimetr);

        if ($arr !== false) {
            $type = self::TArray;
            return $arr;
        }

        $tmp = self::strToNumeric($str, $type);

        if ($tmp !== false)
            return $tmp;

        $type = null;
        return false;
    }
    
    /**
     * Преобразует строку в array или unsigned integer
     * 
     * @param string $str      -> строка
     * @param string $type     -> преобразованный тип
     * @param string $delimetr -> разделитель строки
     * @return array значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public static function strToArrayOrUnsignedInt(string $str, &$type = null, string $delimetr = ',')
    {
        $arr = self::strToArray($str, $delimetr);

        if ($arr !== false) {
            $type = self::TArray;
            return $arr;
        }

        $tmp = self::strToUnsignedInt($str);

        if ($tmp !== false) {
            $type = self::TUnsignedInteger;
            return $tmp;
        }

        return false;
    }

    /**
     * Преобразует строку в array или unsigned double
     * 
     * @param string $str      -> строка
     * @param string $type     -> преобразованный тип
     * @param string $delimetr -> разделитель строки
     * @return array значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public static function strToArrayOrUnsignedDouble(string $str, &$type = null, string $delimetr = ',')
    {
        $arr = self::strToArray($str, $delimetr);

        if ($arr !== false) {
            $type = self::TArray;
            return $arr;
        }

        $tmp = self::strToUnsignedDouble($str);

        if ($tmp !== false) {
            $type = self::TUnsignedDouble;
            return $tmp;
        }

        return false;
    }

    /**
     * Преобразует строку в array или unsigned numeric
     * 
     * @param string $str      -> строка
     * @param string $type     -> преобразованный тип
     * @param string $delimetr -> разделитель строки
     * @return array значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public static function strToArrayOrUnsignedNumeric(string $str, &$type = null, string $delimetr = ',')
    {
        $arr = self::strToArray($str, $delimetr);

        if ($arr !== false) {
            $type = self::TArray;
            return $arr;
        }

        $tmp = self::strToUnsignedNumeric($str, $type);

        if ($tmp !== false)
            return $tmp;

        $type = null;
        return false;
    }
    
    /**
     * Преобразует строку в array или с возможностью обратного вызова
     * 
     * @param string $str      -> строка
     * @param string $callback -> функция преобразования
     * @param string $delimetr -> разделитель строки
     */
    private static function strToArrayWidthCallBack(string $str, callable $callback, string $delimetr = ',')
    {
        $arr = self::strToArray($str, $delimetr);

        if ($arr !== false) {

            $res   = [];
            $count = count($arr);

            for ($i = 0; $i < $count; $i++) {

                //$value =  callback($arr[$i]);
                $value = forward_static_call($callback, $value);

                if ($value === false) {

                    return false;
                }

                $res[] = $value;
            }

            return $res;
        }

        return false;
    }

    /**
     * Преобразует строку в array со значениями integer
     * 
     * @param string $str      -> строка
     * @param string $delimetr -> разделитель строки
     * @return array значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public static function strToIntegerArray(string $str, string $delimetr = ',')
    {
        return self::strToArrayWidthCallBack($str, 'self::strToInt', $delimetr);
    }

    /**
     * Преобразует строку в array со значениями unsigned integer
     * 
     * @param string $str      -> строка
     * @param string $delimetr -> разделитель строки
     * @return array значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public static function strToUnsignedIntegerArray(string $str, string $delimetr = ',')
    {
        return self::strToArrayWidthCallBack($str, 'self::strToUnsignedInt', $delimetr);
    }

    /**
     * Преобразует строку в array со значениями double
     * 
     * @param string $str      -> строка
     * @param string $delimetr -> разделитель строки
     * @return array значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public static function strToDoubleArray(string $str, string $delimetr = ',')
    {
        return self::strToArrayWidthCallBack($str, 'self::strToDouble', $delimetr);
    }

    /**
     * Преобразует строку в array со значениями unsigned double
     * 
     * @param string $str      -> строка
     * @param string $delimetr -> разделитель строки
     * @return array значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public static function strToUnsignedDoubleArray(string $str, string $delimetr = ',')
    {
        return self::strToArrayWidthCallBack($str, 'self::strToUnsignedDouble', $delimetr);
    }

    /**
     * Преобразует строку в array со значениями integer или double
     * 
     * @param string $str      -> строка
     * @param string $delimetr -> разделитель строки
     * @return array значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public static function strToNumericArray(string $str, string $delimetr = ',')
    {
        return self::strToArrayWidthCallBack($str, 'self::strToNumeric', $delimetr);
    }

    /**
     * Преобразует строку в array со значениями unsigned integer или unsigned double
     * 
     * @param string $str      -> строка
     * @param string $delimetr -> разделитель строки
     * @return array значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public static function strToUnsignedNumericArray(string $str, string $delimetr = ',')
    {
        return self::strToArrayWidthCallBack($str, 'self::strToUnsignedNumeric', $delimetr);
    }

    /**
     * Преобразует значение для sql запроса
     * 
     * + Защита от инъекций
     * 
     * @param string   $value   -> значение
     * @param callable $dbQuote -> функция экранирования строки
     * @param bool     $quote   -> помещает строку в ковычки (если тип string), если передан массив, помещает его в круглые скобки (по умолчанию true)
     * 
     * @return array значение или <b>FALSE</b> если возникла ошибка преобразования
     */
    public static function toSql($value, callable $dbQuote = null, $quote = true)
    {        
        if($value instanceof DateTime) {

            //if ($value->format('H') === '0') { }

            return '"' . $value->format('Y-m-d H:i:s') . '"';
        }

        if(is_string($value)) {

            $type = null;
            $tmp  = self::strToNumeric($value, $type);

            if ($tmp !== false) {
                
                if ($type == self::TDouble) {
                    return str_replace(',', '.', (string) $value);
                }

                return $value;
            }

            $tmp = $value;
            
            if($dbQuote) $tmp = call_user_func($dbQuote, $value);
            if($quote)   return '"' . $tmp . '"';
            
            return $tmp;
        }

        if (is_array($value)) {

            $count = count($value);
            $array = [];

            for ($index = 0; $index < $count; $index++) {
                $array[] = self::toSql($value[$index], $dbQuote, $quote);
            }

            if($quote) {
                return '(' . implode(',', $array) . ')';
            }
            
            return implode(',', $array);
        }

        if (is_int($value))   return $value;
        if (is_float($value)) return str_replace(',', '.', (string) $value);
        if (is_bool($value))  return $value ? 1 : 0;

        return false;
    }
    
    /**
     * Проверяет, можно ли приобразовать строку в другой тип
     * 
     * --> Распознает только одномерные массивы
     * 
     * Добавлена возможность преобразования специальных типов
     * к примеру если слова или числа написаны в строке через запятую,
     * то такая строка будет являться массивом
     * 
     * 1) числа или слова через запятую:
     *      один, два, три, ... => array('один', 'два', 'три', ...)
     *      1, 2, 3, ...  => array(1, 2, 3, ...)
     * 
     * @param string $str  -> Строка преобразоания
     * @param mixed  $type -> Тип преобразованного значения
     * @return boolean
     */
    public static function autoStrConvert(string $str, &$type = null)
    {
        // bool
        $tmp = self::strToBool($str);
        if($tmp !== null) {
            $type = self::TBool;
            return $tmp;
        } 
            
        // int|double|float
        $tmp = self::strToNumeric($str, $type);
        if($tmp !== false) {
            return $tmp;
        }
        
        // array
        $tmp = self::strToArray($str);
        if($tmp !== false) {
            $type = self::TArray;
            return $tmp;
        }
        
        return false;
    }
    
    /**
     * Функция автоматического определения типа
     * 
     * --> Распознает только одномерные массивы
     * 
     * Добавлена возможность преобразования специальных типов
     * к примеру если слова или числа написаны в строке через запятую,
     * то такая строка будет являться массивом
     * 
     * 1) числа или слова через запятую:
     *      один, два, три, ... => array('один', 'два', 'три', ...)
     *      1, 2, 3, ...        => array(1, 2, 3, ...)
     * 
     * @param string|int|double $value   -> Скалярное значение
     * @param mixed             $out     -> Выходное значение
     * @param string            $type    -> Выходной тип
     * @param bool              $convert -> Выполнить преобразование строки в другой тип
     * @return boolean
     */
    public static function detectBaseType($value, &$out = null, &$type = null, $convert = true)
    {
        if(is_bool($value)) {
            $out  = $value;
            $type = self::TBool;
            return true;
        }
        
        if(is_int($value)) {
            $out  = $value;
            $type = self::TInteger;
            return true;
        }
        
        if(is_double($value)) {
            $out  = $value;
            $type = self::TDouble;
            return true;
        }
        
        if(is_array($value)) {
            $out  = $value;
            $type = self::TArray;
            return true;
        }
        
        if(is_object($value)) {
            $out  = $value;
            $type = self::TObject;
            return true;
        }
        
        if(is_string($value)) {
            
            if($convert) {
                
                $tmp = self::autoStrConvert($value, $type);
                
                if($type == self::TBool || $tmp !== false) {
                    $out = $tmp;
                    return true;
                }
            }
            
            $out  = $value;
            $type = self::TString;
            return true;
        }
        
        return false;
    }
    
    /**
     * Проверка на ассоциативный массив
     * 
     * @param array $arr
     * @return boolean
     */
    public static function arrIsAssoc(array $arr)
    {
        return array_keys($arr) === range(0, count($arr) - 1);
    }
    
    /**
     * Проверяет является ли этот массив строго целочисленным
     * 
     * - Может определить только одномерные массивы
     * 
     * @param array $arr -> Массив
     * @return boolean
     */
    public static function arrIsIntArr(array $arr)
    {
        foreach($arr as $val) {
            
            if(is_int($val) === false) {
                
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Проверяет является ли этот массив строго числовым с плавующей точкой
     * 
     * - Может определить только одномерные массивы
     * 
     * @param array $arr -> Массив
     * @return boolean
     */
    public static function arrIsDoubleArr(array $arr)
    {
        foreach($arr as $val) {
            
            if(is_double($val) === false) {
                
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Проверяет является ли этот массив численным
     * 
     * - Может определить только одномерные массивы
     * 
     * @param array $arr -> Массив
     * @return boolean
     */
    public static function arrIsNumericArr(array $arr)
    {
        foreach($arr as $val) {
            
            if(is_double($val) === false && is_int($val) === false) {
                
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Проверяем массив на соответствие типу
     * 
     * @param array|Traversable $array -> Массив
     * @param string            $class -> Класс
     * @return boolean
     */
    public static function arrayItemInstantOf($array, string $class)
    {
        if(is_array($array) || $array instanceof Iterator) {
            
            foreach($array as $item) {
                
                if(!$item instanceof $class) {
                    
                    return false;
                }
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Определяет точный тип массива и преобразует его в типовой
     * 
     * - Может определить только одномерные массивы
     * 
     * Функция определяет тип с учетом Unsigned приставки,
     * используйте 2 сравнение или воспользуйтесь substr($ARR_TYPE)
     * 
     * @param array $arr -> Массив для проверки
     * @param array $out -> Преобразованный массив
     * @return bool|string Вернет FALSE если массив не числовой, EMPTY если массив пустой
     */
    public static function detectArrNumeric(array $arr, array &$out = null)
    {
        if (count($arr) == 0) {

            return self::TEmptyArray;
        }
            
        $tmp      = array();
        $unsigned = true;
        $int      = true;
        $double   = true;
        $bool     = true;
 
        foreach($arr as $key => $value) {

            $outVal  = null;
            $outType = null;

            if(!self::detectBaseType($value, $outVal, $outType)) {

                return false;
            }

            switch ($outType) {

                case self::TBool:
                    
                    $int      = false;
                    $double   = false;
                    $unsigned = false;
                    
                break;
            
                case self::TDouble:  $int    = false; 
                case self::TInteger: $double = false;

                case self::TInteger:
                case self::TDouble:
                    
                    $bool = false;
                    
                    if($outVal < 0) 
                        $unsigned = false;
                    
                break;

                // Не будем распозновать остальное
                default: return false;
            }

            $tmp[$key] = $outVal; 
        }

        // Обновляем массив
        $out = $tmp;

        if($unsigned) {

            if($int)    return self::TUnsignedIntegerArray;
            if($double) return self::TUnsignedDoubleArray;

            return self::TUnsignedNumericArray;
        }
        
        if($bool)   return self::TBoolArray;
        if($int)    return self::TIntegerArray;
        if($double) return self::TDoubleArray;

        return self::TNumericArray;
    }
}