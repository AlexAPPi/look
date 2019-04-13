<?php

namespace LookPhp\Event\Task;

use LookPhp\Event\Interfaces\IEventHandlerDisable;

/**
 * Отключает указанный обработчик
 * 
 * @author Alexandr Shamarin <alexsandrshamarin@yandex.ru>
 */
class EventHandlerDisable extends EventTask implements IEventHandlerDisable {}