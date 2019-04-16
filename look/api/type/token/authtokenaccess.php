<?php

namespace Look\API\Type\Token;

use Look\API\Type\Token\Token;

/**
 * Токен, связанный с доступом к авторизации
 */
class AuthTokenAccess extends Token
{
    protected $necessaryPermits = ['auth'];
}