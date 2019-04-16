<?php

namespace Look\API\Type\Token\Exceptions;

use Look\API\Type\Token\Exceptions\TokenException;

/**
 * Исключение связянное с истекшим токеном
 */
class ExpiredTokenException extends TokenException
{
    /**
     * Исключение связанное с истекшим токеном
     */
    public function __construct()
    {
        parent::__construct('expired token', parent::TokenExpired);
    }
}