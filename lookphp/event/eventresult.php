<?php

namespace LookPhp\Event;

/**
 * Класс содержит информацию с результатами выполнения цепочки события
 */
class EventResult
{
    protected $arguments;
    protected $result;
    
    /**
     * @param array $arguments -> Аргументы
     * @param mixed $result    -> Результат
     */
    public function __construct(?array $arguments = null, $result = null)
    {
        if($arguments === null) {
            $arguments = [];
        }
        
        $this->arguments = $arguments;
        $this->result    = $result;
    }
    
    /**
     * Возращает аргументы
     * @return array
     */
    public function getArguments() : array
    {
        return $this->arguments;
    }
    
    /**
     * Возращает результат выполнения
     * @return type
     */
    public function getResult()
    {
        return $this->result;
    }
    
    public function replaceArgument(array &$args) : void
    {
        $in = &$args;
        $c = count($in);
        for($i = 0; $i < $c; $i++) {
            $link = &$in[$i];
            $link = $this->arguments[$i];
        }
    }
}