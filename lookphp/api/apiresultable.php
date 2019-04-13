<?php

namespace LookPhp\API;

interface APIResultable
{
    /**
     * Результат может быть исключительно форматов
     * array, int, float, string, null
     * @return array|int|float|string|null
     */
    public function toAPIResult();
}