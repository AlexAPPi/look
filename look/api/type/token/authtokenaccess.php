<?php

namespace Look\API\Type\Token;

use Look\API\Type\Token\SimpleToken;

/**
 * Токен, связанный с доступом к авторизации
 */
class AuthTokenAccess extends SimpleToken
{
    protected $necessaryPermits = ['auth'];
}