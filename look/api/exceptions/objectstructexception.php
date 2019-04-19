<?php

namespace Look\API\Exceptions;

use Throwable;
use Look\API\Type\Exceptions\InvalidArgumentException;

/**
 * Исключение связанное с нарушением структуры объекта
 */
class ObjectStructException extends InvalidArgumentException
{
    const argumentErrMessage = 'must be an object';
    
    const format = '(typeof %type)%name->';
    
    protected $objectType;

    /**
     * Конструктор исключения связанного с неправильной передачей значения аргумента
     * @param string    $name     Назание аргумента
     * @param string    $type     Тип аргумента
     * @param int       $code     Код исключения
     * @param Throwable $previous Стек исключений
     * @return self
     */
    public function __construct(string $name, string $type, int $code = 0, Throwable $previous = null)
    {
        $this->objectType = $type;
        parent::__construct($name, $code, $previous);
    }
    
    /** {@inheritdoc} */
    public function getArgumentErrMessage() : string
    {
        return $this->objectType;
    }
    
    /** {@inheritdoc} */
    public function __toString() : string
    {
        return $this->buildMessage();
    }
    
    /**
     * Формирует сообщение с полной цепочкой ошибки
     * @return string
     */
    public function buildMessage() : string
    {
        $names = [];
        $types = [];
        $count = 0;
        $el = $this;
        while(true) {
            if($el instanceof InvalidArgumentException) {
                $count++;
                $names[] = $el->getArgumentName();
                $types[] = $el->getArgumentErrMessage();
                $prev    = $el->getPrevious();             
                $el      = $prev;
            } else {
                break;
            }
        }
        
        $struct = '';
        for($i = 0; $i < $count - 1; $i++) {
            $typeRe  = str_replace('%type', $types[$i], static::format);
            $struct .= str_replace('%name', $names[$i], $typeRe);
        }
        $struct .= $names[$count-1];
        
        return "One of the parameters specified was missing or invalid: $struct is {$types[$count-1]}";
    }
}
