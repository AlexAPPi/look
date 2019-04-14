<?php

namespace Look\Token\Exceptions;

use Look\Token\Exceptions\TokenException;

/**
 * Исключение связянное с истекшим токеном
 */
class AccessTokenException extends TokenException
{
    /**
     * Исключение связанное с истекшим токеном
     */
    public function __construct()
    {
        parent::__construct('token does not have an access signature for this request', parent::NoAccessToken);
    }
}