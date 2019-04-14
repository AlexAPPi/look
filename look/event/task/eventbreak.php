<?php

namespace Look\Event\Task;

use Look\Event\IEventBreak;

/**
 * Аналог break для for
 * Прерывает цепочку обработки события
 * 
 * @author Alexandr Shamarin <alexsandrshamarin@yandex.ru>
 */
class EventBreak extends EventTask implements IEventBreak {}