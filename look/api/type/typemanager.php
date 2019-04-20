<?php

namespace Look\API\Type;

use ReflectionParameter;

use Look\API\Type\Interfaces\IType;
use Look\API\Type\Interfaces\IScalar;
use Look\API\Type\Interfaces\IScalarArray;

use Look\API\Exceptions\APIStandartException;

/**
 * Класс предназначения для работы со скалярными типами данных:
 * 
 * - Преобразовывание одних скалярных типов в другие
 * - Сравнение типов и автоматическое преобразование
 *
 * @author Alexandr Shamarin <alexsandrshamarin@yandex.ru>
 */
final class TypeManager
{
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
        
        'arr'     => IType::TArray,
        'array'   => IType::TArray,
        
        'str'     => IType::TString,
        'string'  => IType::TString,
        
        'boolean' => IType::TBool,
        'bool'    => IType::TBool,
        
        'integer' => IType::TInteger,
        'int'     => IType::TInteger,
        
        'float'   => IType::TDouble,
        'double'  => IType::TDouble,
    ];
    
    /** @var array Список скалярных значений */
    private static $scalarTypes = [
        IType::TBool,
        IType::TInteger,
        IType::TDouble,
        IType::TString,
        IType::TNumeric,
        IType::TUnsignedDouble,
        IType::TUnsignedInteger,
        IType::TUnsignedNumeric,
        IType::TScalar
    ];
        
    /**
     * Преобразует тип параметра к типу стандарта IType
     * @param ReflectionParameter $param -> Объект параметра
     * @return string
     */
    public static function argTypeToITypeStandart(ReflectionParameter $param) : string
    {
        if($param->hasType()) {
            
            $type         = (string)$param->getType();
            $paramBuiltin = $param->getType()->isBuiltin();
            $isClass      = !$paramBuiltin && class_exists($type);
            
            // Т.к в систему типизации заложены такие понятия,
            // как обертка для скалярного типа,
            // обертка массива со скалярными типами
            // После обработки значений создаем экземпляры данных классов
            
            if($paramBuiltin) {
                
                if($param->isVariadic()) {
                    
                    switch($type) {

                        case 'int' :    return IType::TIntegerArray;
                        case 'float' :  return IType::TDoubleArray;
                        case 'bool'  :  return IType::TBoolArray;
                        case 'string' : return IType::TStringArray;
                        default: break;
                    }

                    throw new APIStandartException("Стандарт API обработки не позволяет использовать тип [$type] c variadic методом передачи параметра");
                }
                
                switch($type) {
                    case 'int'   :    return IType::TInteger;
                    case 'float' :    return IType::TDouble;
                    case 'bool'  :    return IType::TBool;
                    case 'array':     return IType::TArray;
                    case 'string' :   return IType::TString;
                    case 'object' :   return IType::TObject;
                    case 'iterable' : return IType::TIterable;
                    case 'callable' : return IType::TCallable;
                    default : break;
                }
            }
            
            if($isClass) {
                
                // Класс наследует типизацию стандарта IType
                if(is_subclass_of($type, IType::class)) {
                    
                    $fn         = "$type::__getEvalType";
                    $scalarType = $fn();
                    
                    // Значение является скалярным
                    if(is_subclass_of($type, IScalar::class)) {
                        
                        if($param->isVariadic()) {
                            return static::getArrayTypeFor($scalarType);
                        }
                        
                        return $scalarType;
                    }
                    
                    // Передан скалярный массив
                    if(!$param->isVariadic()
                    && is_subclass_of($isClass, IScalarArray::class)) {
                        return $scalarType;
                    }
                }
                
                return $param->isVariadic() ? IType::TClassArray : IType::TClass;
            }
            
            throw new APIStandartException("Стандарт API обработки не позволяет использовать тип [$type]");
        }
        
        return IType::TMixed;
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
        return $type . ' ' . IType::TArray;
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
        if($type1 == $type2) {
            return true;
        }
        
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
        if($type === IType::TObject && class_exists($funcType, true)) {
            return true;
        }

        // Любой тип можно конвертировать в строку
        if($funcType == IType::TString && $type !== IType::TObject) {
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
     * @param string|object $sub     -> Дочерний тип или объект
     * @param string        $base    -> Базовый тип
     * @param string        $getType -> Определить тип автоматически
     * @return bool
     */
    public static function instanteOf($sub, string $base, bool $getType = false) : bool
    {
        if($getType) {
            $subType = gettype($sub);
            if($subType == IType::TObject) {
                $subType = get_class($sub);
                return is_subclass_of($sub, $base);
            }
            $sub = $subType;
        }
        
        // Т.к все типы собираются из под строк
        // например тип unsigned double array
        // складывается из TUnsignedDouble + TArray
        // Если вхождение строки присудствует, значит тип определен верно
        
        if($sub == $base) {
            return true;
        }
        
        $subIsUnsigned = substr($sub, 0, 8) == 'unsigned';
        $subIsArray    = substr($sub, -5) == 'array';
        $subTypesStr   = substr($sub, ($subIsUnsigned ? 9 : 0), ($subIsArray ? -6 : null));
        $subTypes      = $subTypesStr !== false ? explode('|', $subTypesStr) : [];
        
        $baseIsUnsigned = substr($base, 0, 8) == 'unsigned';
        $baseIsArray    = substr($base, -5) == 'array';
        $baseTypesStr   = substr($base, ($baseIsUnsigned ? 9 : 0), ($baseIsArray ? -6 : null));
        $baseTypes      = $baseTypesStr !== false ? explode('|', $baseTypesStr) : [];
        
        $baseTypesCount = count($baseTypes);
        $subTypesCount  = count($subTypes);
        
        // Наследуется не массив
        // Наследуется не положительное значение
        // Наследуется не точный тип
        if(($baseIsArray && !$subIsArray)
        || (!$baseIsArray && $subIsArray)
        || ($baseIsUnsigned && !$subIsUnsigned)
        || ($subTypesCount == 0 && $baseTypesCount > 0)) {
            return false;
        }
        
        // Базовый класс не имеет перебора
        if($baseTypesCount == 0
        || ($subTypesStr == $baseTypesStr)) {
            return true;
        }
        
        for($i = 0; $i < $subTypesCount; $i++) {
            if(!in_array($subTypes[$i], $baseTypes)) {
                return false;
            }
        }
                
        return true;
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
        return TypeChecker::typeExists($str) ? $str : null;
    }
    
    /**
     * Извлечает значение типа элемента массива
     * 
     * @param string $arrayType -> Тип массива
     * @return string|null NULL если ошибка извлечения
     */
    public static function extractArrayTypeItem(string $arrayType) : ?string
    {
        $str = str_replace(' ' . IType::TArray, '', $arrayType);
        return TypeChecker::typeExists($str) ? $str : null;
    }
            
    /**
     * Преобразует любые значения в bool
     * 
     * @param mixed $var -> Значение
     * @return boolean значение или <b>NULL</b> если возникла ошибка преобразования
     */
    public static function anyToBool($var)
    {
        if(is_bool($var)) {
            return $var;
        }
        
        if($var === 0 || $var === 0.0 || $var === '0') return false;
        if($var === 1 || $var === 1.0 || $var === '1') return true;
        
        if(is_string($var)) {
            return self::strToBool($var);
        }
        
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

            $type = IType::TInteger;
            return $isInt;
        }

        $isDouble = static::strToDouble($str);

        if ($isDouble !== false) {

            $type = IType::TDouble;
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

            $type = IType::TInteger;
            return $isInt;
        }

        $isDouble = self::strToUnsignedDouble($str);

        if ($isDouble !== false) {

            $type = IType::TDouble;
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
     * @return array значение или <b>NULL</b> если возникла ошибка преобразования
     */
    public static function strToArray(string $str, string $delimetr = ',') : ?array
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

        return null;
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
            $type = IType::TArray;
            return $arr;
        }

        $tmp = self::strToInt($str);

        if ($tmp !== false) {
            $type = IType::TInteger;
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
            $type = IType::TArray;
            return $arr;
        }

        $tmp = self::strToDouble($str);

        if ($tmp !== false) {
            $type = IType::TDouble;
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
            $type = IType::TArray;
            return $arr;
        }

        $tmp = self::strToNumeric($str, $type);

        if ($tmp !== false) {
            return $tmp;
        }
        
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
            $type = IType::TArray;
            return $arr;
        }

        $tmp = self::strToUnsignedInt($str);

        if ($tmp !== false) {
            $type = IType::TUnsignedInteger;
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
            $type = IType::TArray;
            return $arr;
        }

        $tmp = self::strToUnsignedDouble($str);

        if ($tmp !== false) {
            $type = IType::TUnsignedDouble;
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
            $type = IType::TArray;
            return $arr;
        }

        $tmp = self::strToUnsignedNumeric($str, $type);

        if ($tmp !== false) {
            return $tmp;
        }
        
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
            $type = IType::TBool;
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
            $type = IType::TArray;
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
            $type = IType::TBool;
            return true;
        }
        
        if(is_int($value)) {
            $out  = $value;
            $type = IType::TInteger;
            return true;
        }
        
        if(is_double($value)) {
            $out  = $value;
            $type = IType::TDouble;
            return true;
        }
        
        if(is_array($value)) {
            $out  = $value;
            $type = IType::TArray;
            return true;
        }
        
        if(is_object($value)) {
            $out  = $value;
            $type = IType::TObject;
            return true;
        }
        
        if(is_string($value)) {
            
            if($convert) {
                
                $tmp = self::autoStrConvert($value, $type);
                
                if($type == IType::TBool || $tmp !== false) {
                    $out = $tmp;
                    return true;
                }
            }
            
            $out  = $value;
            $type = IType::TString;
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
     * Определяет точный тип массива и преобразует его в типовой
     * 
     * - Может определить только одномерные массивы
     * 
     * Функция определяет тип с учетом Unsigned приставки,
     * используйте 2 сравнение или воспользуйтесь substr($ARR_TYPE)
     * 
     * @param array $arr -> Массив для проверки
     * @param array $out -> Преобразованный массив
     * @return string|null Вернет NULL если массив не числовой, EMPTY если массив пустой
     */
    public static function detectArrType(array $arr, array &$out = null) : ?string
    {
        if (count($arr) == 0) {

            return IType::TEmptyArray;
        }
            
        $tmp      = [];
        $unsigned = true;
        $int      = true;
        $double   = true;
        $bool     = true;
 
        foreach($arr as $key => $value) {

            $outVal  = null;
            $outType = null;

            if(!self::detectBaseType($value, $outVal, $outType)) {
                
                return null;
            }
            
            switch ($outType) {
                
                case IType::TBool:
                    
                    $int      = false;
                    $double   = false;
                    $unsigned = false;
                                        
                break;
                
                case IType::TInteger:
                case IType::TDouble:
                    
                    if($outType == IType::TDouble)  $int    = false;
                    if($outType == IType::TInteger) $double = false;
                    
                    $bool = false;
                    
                    if($outVal < 0) {
                        $unsigned = false;
                    }
                    
                break;

                // Не будем распозновать остальное
                default: return null;
            }

            $tmp[$key] = $outVal; 
        }
        
        // Обновляем массив
        $out = $tmp;

        if($unsigned) {

            if($int)    return IType::TUnsignedIntegerArray;
            if($double) return IType::TUnsignedDoubleArray;

            return IType::TUnsignedNumericArray;
        }
        
        if($bool)   return IType::TBoolArray;
        if($int)    return IType::TIntegerArray;
        if($double) return IType::TDoubleArray;

        return IType::TNumericArray;
    }
}