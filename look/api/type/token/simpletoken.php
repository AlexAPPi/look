<?php

namespace Look\API\Type\Token;

use Look\API\Type\Token\Container\TokenContainer;

/**
 * Служит для подтерждения полномочий, которыми наделен пользователь
 */
class SimpleToken extends TokenContainer
{
    /** @see TokenContainer::checkPermissions */
    const necessaryPermits = [];
}