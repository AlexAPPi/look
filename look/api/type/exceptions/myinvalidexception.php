<?php

namespace Look\API\Type\Exceptions;

/**
 *  Класс исключения, связанного с неправильной передачей параметра
 */
class MyInvalidException extends InvalidArgumentException
{
    const argumentErrMessage = 'my';
    
    /**
     * @var string Описание ошибки 
     */
    protected $myErrorMsg;
    
    /**
     * Конструктор исключения связанного с неправильной передачей значения аргумента
     * @param string    $name     Назание аргумента
     * @param string    $errMsg   Описание
     * @param int       $code     Код исключения
     * @param Throwable $previous Стек исключений
     * @return self
     */
    public function __construct(string $name, string $errMsg, int $code = 0, Throwable $previous = null)
    {
        $this->myErrorMsg = $errMsg;
        parent::__construct($name, $code, $previous);
    }
    
    /**
     * Возвращает код ошибки параметра
     * @return string
     */
    public function getArgumentErrMessage() : string
    {
        return $this->myErrorMsg;
    }
}