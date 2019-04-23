<?php

namespace Look\API\Type\Interfaces;

/**
 * Объект, поддерживающий реализацию API ответа
 */
interface APIResultable
{
    /**
     * Результат может быть исключительно форматов
     * array, int, float, string, null
     * @return array|int|float|string|null
     */
    public function toAPIResult();
}