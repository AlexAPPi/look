<?php

namespace Look;

use Look\Url\Builder;
use Look\Url\Currect;

/**
 * Класс для работы с URL
 */
class Url extends Builder
{
    /**
     * Возвращает корректный Url запроса
     * @return \Look\Url\Currect
     */
    public static function currect()
    {
        return Currect::getInstance();
    }
}