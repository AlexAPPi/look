<?php

namespace Look\API\Type\Token;

use Look\API\Type\Token\Container\TunnelTokenContainer;

/**
 * Служит для безопасной передачи данных от клиента к сереру и наоборот
 */
class TunnelToken extends TunnelTokenContainer
{
    /** @see TokenContainer::checkPermissions */
    const necessaryPermits = [];
}