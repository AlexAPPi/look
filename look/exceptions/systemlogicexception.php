<?php

namespace Look\Exceptions;

use Throwable;
use LogicException;
use Look\Exceptions\ILookException;

/**
 * Базовый класс для всех исключений в системе.
 */
class      SystemLoginException
extends    LogicException
implements ILookException
{
    use SystemExceptionTrait;
    
    /**
     * @param null|string|Throwable $message  Сообщение исключения
     * @param null|int|Throwable    $code     Код исключения
     * @param null|Throwable        $previous Предыдущие исключения
     */
    public function __construct($message = '', $code = 500, $previous = null)
    {
        $this->__initException($message, $code, $previous);
        parent::__construct($message, $code, $previous);
    }
}