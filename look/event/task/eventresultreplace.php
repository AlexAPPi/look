<?php

namespace Look\Event\Task;

use Look\Event\Interfaces\IEventResultReplace;

/**
 * Позволяет выполнять операции подмены аргументов
 * 
 * @author Alexandr Shamarin <alexsandrshamarin@yandex.ru>
 */
class EventResultReplace extends EventTask implements IEventResultReplace
{
    protected $originalResult = [];
    protected $replaceResult  = [];
    
    /**
     * @param mixed $result -> Результат
     */
    public function __construct($result = null)
    {
        $this->originalResult = $result;
        $this->replaceResult  = $result;
        
        parent::__construct('', 0, null);
    }
    
    /** {@inheritdoc} */
    public function getOriginalResult()
    {
        return $this->originalResult;
    }
    
    /** {@inheritdoc} */
    public function setOriginalResult($result) : void
    {
        $this->originalResult = $result;
    }
    
    /** {@inheritdoc} */
    public function getReplaceResult()
    {
        return $this->replaceResult;
    }
    
    /** {@inheritdoc} */
    public function setReplaceResult($result) : void
    {
        $this->replaceResult = $result;
    }
}