<?php

namespace LookPhp\Type\Exceptions;

use Throwable;
use LookPhp\Type\Converter;
use LookPhp\Exceptions\InvalidArgumentException;

/**
 * Возвращает исключение для указанного типа
 */
final class AutoArgumentException
{
    /**
     * Обработчик дополнительных исключений для типа
     * @var array 
     */
    private static $specTypes = [];
    
    /**
     * Добавляет исключение для типа
     * @param string $type      -> Строковое представление типа
     * @param string $exception -> Класс исключения
     * @return void
     * @throws MyInvalidException
     */
    public static function addTypeException(string $type, string $exception) : void
    {
        // Разрешен только наследник InvalidArgumentException
        if(class_exists($exception)
        && ($exception == InvalidArgumentException::class
        || is_subclass_of($exception, InvalidArgumentException::class))) {
            static::$specTypes[$type] = $exception;
            return;
        }
        
        throw new MyInvalidException('exception', 'not '.InvalidArgumentException::class);
    }
    
    /**
     * Автоматически создает исключение для указанного типа
     * 
     * @param string     $name     -> Назание аргумента
     * @param string     $type     -> Тип аргумента
     * @param string     $code     -> Код исключения
     * @param \Throwable $previous -> Предыдущие исключения
     * @return \LookPhp\Type\Exceptions\UnsignedIntegerException|
     *         \LookPhp\Type\Exceptions\UnsignedNumericException|
     *         \LookPhp\Type\Exceptions\ParametrException|
     *         \LookPhp\Type\Exceptions\ObjectException|
     *         \LookPhp\Type\Exceptions\ArrayException|
     *         \LookPhp\Type\Exceptions\CallableException|
     *         \LookPhp\Type\Exceptions\IntegerException|
     *         \LookPhp\Type\Exceptions\DoubleException|
     *         \LookPhp\Type\Exceptions\StringException|
     *         \LookPhp\Type\Exceptions\NumericException|
     *         \LookPhp\Type\Exceptions\UnsignedNumericArrayException|
     *         \LookPhp\Type\Exceptions\UnsignedDoubleArrayException|
     *         \LookPhp\Type\Exceptions\UnsignedIntegerArrayException|
     *         \LookPhp\Type\Exceptions\BooleanException|
     *         \LookPhp\Type\Exceptions\UnsignedDoubleException
     */
    public static function of(string $name, string $type, int $code = 0, Throwable $previous = null) : Throwable
    {
        $typeOfClass = Converter::getTypeForClass($type);
        $type = $typeOfClass === null ? $type : $typeOfClass;
        
        // Пользовательское исключение
        if(isset(static::$specTypes[$type])) {
            return new static::$specTypes[$type]($name, $code, $previous);
        }
        
        switch($type)
        {
            case Converter::TInteger:    return new IntegerException($name, $code, $previous);
            case Converter::TInteger2:   return new IntegerException($name, $code, $previous);
            case Converter::TDouble:     return new DoubleException($name, $code, $previous);
            case Converter::TDouble2:    return new DoubleException($name, $code, $previous);
            case Converter::TFloat:      return new DoubleException($name, $code, $previous);
            case Converter::TString:     return new StringException($name, $code, $previous);
            case Converter::TBool:       return new BooleanException($name, $code, $previous);
            case Converter::TBool2:      return new BooleanException($name, $code, $previous);
            case Converter::TNumeric:    return new NumericException($name, $code, $previous);
            case Converter::TArray:      return new ArrayException($name, $code, $previous);
            case Converter::TMultiArray: return new ArrayException($name, $code, $previous);
            case Converter::TObject:     return new ObjectException($name, $code, $previous);
            case Converter::TCallable:   return new CallableException($name, $code, $previous);
            
            case Converter::TBoolArray:    return new BooleanArrayException($name, $code, $previous);
            case Converter::TNumericArray: return new NumericArrayException($name, $code, $previous);
            case Converter::TIntegerArray: return new IntegerArrayException($name, $code, $previous);
            case Converter::TDoubleArray:  return new DoubleArrayException($name, $code, $previous);
            
            case Converter::TUnsignedNumeric:      return new UnsignedNumericException($name, $code, $previous);
            case Converter::TUnsignedDouble:       return new UnsignedDoubleException($name, $code, $previous);
            case Converter::TUnsignedInteger:      return new UnsignedIntegerException($name, $code, $previous);
            
            case Converter::TUnsignedNumericArray: return new UnsignedNumericArrayException($name, $code, $previous);
            case Converter::TUnsignedDoubleArray:  return new UnsignedDoubleArrayException($name, $code, $previous);
            case Converter::TUnsignedIntegerArray: return new UnsignedIntegerArrayException($name, $code, $previous);
            
            default: break;
        }
                
        return new MyInvalidException($name, 'not ' . $type, $code, $previous);
    }
}