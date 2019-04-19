<?php

namespace Look\Exceptions;

use Throwable;
use InvalidArgumentException as PHPInvalidArgumentException;

/**
 * Исключение связанное с неправильной передачей аргумента
 */
class      InvalidArgumentException
extends    PHPInvalidArgumentException
implements ILookException
{
    use SystemExceptionTrait;
    
    /**
     * Информация по типу исключения
     * @var string
     */
    const argumentErrMessage = 'invalid';
    
    /**
     * Назание переменной
     * @var string
     */
    protected $argumentName = null;
    
    /**
     * Конструктор исключения связанного с неправильной передачей значения аргумента
     * @param string    $name     Назание аргумента
     * @param int       $code     Код исключения
     * @param Throwable $previous Стек исключений
     * @return self
     */
    public function __construct(string $name, int $code = 0, Throwable $previous = null)
    {
        $this->argumentName = $name;
        $message = "One of the parameters specified was missing or invalid: $this->argumentName is {$this->getArgumentErrMessage()}";
        $this->__initException($message, $code, $previous);
        parent::__construct($message, $code, $previous);
    }
    
    /**
     * Возвращает название параметра
     * @return string
     */
    public function getArgumentName() : string
    {
        return $this->argumentName;
    }
    
    /**
     * Возвращает код ошибки параметра
     * @return string
     */
    public function getArgumentErrMessage() : string
    {
        return static::argumentErrMessage;
    }
    
    /**
     * Возвращает сообщение
     * @return string
     */
    public function __toString(): string
    {
        return parent::__toString();
    }
}

