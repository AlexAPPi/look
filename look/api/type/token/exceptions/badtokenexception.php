<?php

namespace Look\API\Type\Token\Exceptions;

use Look\API\Type\Token\Exceptions\TokenException;

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