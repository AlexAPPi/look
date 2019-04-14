<?php

namespace Look\Token;

use Look\Token\Token;

/**
 * Токен, связанный с доступом к авторизации
 */
class AuthTokenAccess extends Token
{
    protected $necessaryPermits = ['auth'];
}