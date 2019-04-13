<?php

namespace LookPhp\Token;

use LookPhp\Token\Token;

/**
 * Токен, связанный с доступом к авторизации
 */
class AuthTokenAccess extends Token
{
    protected $necessaryPermits = ['auth'];
}