<?php

namespace LookPhp\Event\Task;

use LogicException;
use LookPhp\Event\Interfaces\IEventTask;

/**
 * Исключение связанное с поставкой задачи при выполнении обработки данного события
 */
class EventTask extends LogicException implements IEventTask
{
    /**
     * Запускает процесс прерывания обработки цепочки события
     * @throws self
     */
    public static function go()
    {
        throw new self;
    }
    
    /**
     * Запускает процесс прерывания обработки цепочки события
     * @throws $this
     */
    public function __invoke()
    {
        throw $this;
    }
}