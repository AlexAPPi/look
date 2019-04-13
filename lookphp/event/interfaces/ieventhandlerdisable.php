<?php

namespace LookPhp\Event\Interfaces;

/**
 * Исключение связанное с отключением данного обработчика
 * при следующих срабатываниях данного события
 */
interface IEventHandlerDisable extends IEventTask {}