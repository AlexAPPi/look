<?php

namespace Look\Token;

use Look\Token\Container\TokenContainer;

/**
 * Служит для подтерждения полномочий, которыми наделен пользователь
 */
class Token extends TokenContainer
{
    /** @see TokenContainer::checkPermissions */
    const necessaryPermits = [];
}