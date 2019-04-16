<?php

namespace Look\API\Type\Exceptions;

use Throwable;
use Look\API\Type\Interfaces\IType;

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
     * @return InvalidArgumentException
     */
    public static function of(string $name, string $type, int $code = 0, Throwable $previous = null) : Throwable
    {
        // Пользовательское исключение
        if(isset(static::$specTypes[$type])) {
            return new static::$specTypes[$type]($name, $code, $previous);
        }
        
        switch($type)
        {
            case IType::TInteger:    return new IntegerException($name, $code, $previous);
            case IType::TDouble:     return new DoubleException($name, $code, $previous);
            case IType::TString:     return new StringException($name, $code, $previous);
            case IType::TBool:       return new BooleanException($name, $code, $previous);
            case IType::TNumeric:    return new NumericException($name, $code, $previous);
            case IType::TArray:      return new ArrayException($name, $code, $previous);
            case IType::TMultiArray: return new ArrayException($name, $code, $previous);
            case IType::TObject:     return new ObjectException($name, $code, $previous);
            case IType::TCallable:   return new CallableException($name, $code, $previous);
            
            case IType::TBoolArray:    return new BooleanArrayException($name, $code, $previous);
            case IType::TNumericArray: return new NumericArrayException($name, $code, $previous);
            case IType::TIntegerArray: return new IntegerArrayException($name, $code, $previous);
            case IType::TDoubleArray:  return new DoubleArrayException($name, $code, $previous);
            
            case IType::TUnsignedNumeric:      return new UnsignedNumericException($name, $code, $previous);
            case IType::TUnsignedDouble:       return new UnsignedDoubleException($name, $code, $previous);
            case IType::TUnsignedInteger:      return new UnsignedIntegerException($name, $code, $previous);
            
            case IType::TUnsignedNumericArray: return new UnsignedNumericArrayException($name, $code, $previous);
            case IType::TUnsignedDoubleArray:  return new UnsignedDoubleArrayException($name, $code, $previous);
            case IType::TUnsignedIntegerArray: return new UnsignedIntegerArrayException($name, $code, $previous);
            
            case IType::TIterable: return new IterableException($name, $code, $previous);
            
            case IType::TEnum: return new EnumException($name, $code, $previous);
            
            default: break;
        }
                
        return new MyInvalidException($name, 'not ' . $type, $code, $previous);
    }
}