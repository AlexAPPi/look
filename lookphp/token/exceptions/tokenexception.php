<?php

namespace LookPhp\Token\Exceptions;

use LookPhp\Exceptions\SystemException;

/**
 * Исключение связанное с ошибкой токена
 */
class TokenException extends SystemException
{   
    /** Не верный формат токена */
    const BadTokenCode  = 70001;
    
    /** Истек срок действия токена */
    const TokenExpired  = 70002;
    
    /** Ошибка доступа для данного токена */
    const NoAccessToken = 70003;
};