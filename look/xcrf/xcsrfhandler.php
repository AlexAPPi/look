<?php

namespace Look\Page;

/**
 * Обработчик запросов с использованием технологии xcsrf
 *
 * Данная технология позволяет пользователю отправлять запросы,
 * защищенные от многократной отправки
 * 
 * @author Alexandr Shamarin <alexsandrshamarin@yandex.ru>
 */
class XCSRFHandler extends HTMLWrap
{
    protected $id;
    protected $method;
    protected $token;
    
    /**
     * Создает обработчик запросов с использованием метода защиты xcsrf
     * 
     * @param string $id     -> Уникальный индификатор запроса в системе
     * @param string $method -> Доступный тип запроса GET|POST|PUT|DELETE
     * @param string $token  -> Уникальный ключ доступа
     */
    public function __construct(string $id, string $method, string $token)
    {
        $this->id      = $id;
        $this->method  = $method;
        $this->token   = $token;
    }
    
    /**
     * Уникальный индификатор запроса в системе
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }
    
    /**
     * Доступный тип запроса GET|POST|PUT|DELETE
     * @return string
     */
    public function getMethod() : string
    {
        return $this->method;
    }
    
    /**
     * Уникальный ключ доступа
     * @return string
     */
    public function getToken() : string
    {
        return $this->token;
    }
    
    /** {@inheritdoc} */
    public function buildHTML(int $offset, int $tabSize, string $mainTabStr, string $tabStr): ?string
    {
        return $mainTabStr . '<input type="text" name="xcsrf_id" value="'.$this->getId().'" hidden>' . PHP_EOL .
               $mainTabStr . '<input type="text" name="xcsrf_method" value="'.$this->getMethod().'" hidden>' . PHP_EOL .
               $mainTabStr . '<input type="text" name="xcsrf_token" value="'.$this->getToken().'" hidden>' . PHP_EOL;
    }
}
