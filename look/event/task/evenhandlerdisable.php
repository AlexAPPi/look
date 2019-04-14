<?php

namespace Look\Event\Task;

use Look\Event\Interfaces\IEventHandlerDisable;

/**
 * Отключает указанный обработчик
 * 
 * @author Alexandr Shamarin <alexsandrshamarin@yandex.ru>
 */
class EventHandlerDisable extends EventTask implements IEventHandlerDisable {}