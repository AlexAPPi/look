<?php

namespace Look\Client\IP;

interface IDetector
{
    /**
     * Запускает процесс поиска
     * @return $this
     */
    function detect() : bool;
    
    /**
     * Возращает IP адрес
     * @return $this
     */
    function get();
}
