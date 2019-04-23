<?php

namespace Look\API\Type;

use JsonSerializable;
use Look\API\Type\Interfaces\APIResultable;

/**
 * Возращает объект результата
 */
class ApiResult implements JsonSerializable, APIResultable
{
    /**
     * @param array $array -> Результат
     */
    public $result;
    
    /**
     * @param int $cacheMaxAge  -> Максимальное время кеша в секунду
     */
    public $cacheMaxAge = 1;
    
    /**
     * @param bool $clearHeaders -> Удалять заголовки отправленые ранее
     */
    public $clearHeaders = true;
    
    /**
     * @param mixed $result       -> Результат
     * @param int   $cacheMaxAge  -> Максимальное время кеша в секунду
     * @param bool  $clearHeaders -> Удалять заголовки отправленые ранее
     */
    public function __construct($result, int $cacheMaxAge = 0, bool $clearHeaders = true)
    {
        $this->result       = $result;
        $this->cacheMaxAge  = $cacheMaxAge;
        $this->clearHeaders = $clearHeaders;
    }

    /**
     * Возвращает данные объекта
     * @return array
     */
    public function jsonSerialize()
    {
        $result = $this->result;
        
        while($result instanceof APIResultable) {
            $result = $result->toAPIResult();
        }
        
        return [
            '@type'  => static::class,
            '@cache' => $this->cacheMaxAge,
            'result' => $result
        ];
    }
    
    public function toAPIResult()
    {
        
    }
}
