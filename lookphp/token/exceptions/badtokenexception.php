<?php

namespace LookPhp\Token\Exceptions;

use LookPhp\Token\Exceptions\TokenException;

/**
 * Исключение связянное с плохим токеном
 */
class BadTokenException extends TokenException
{    
    /**
     * Исключение связанное с плохим форматом токена
     */
    public function __construct()
    {
        parent::__construct('token is not valid', parent::BadTokenCode);
    }
}