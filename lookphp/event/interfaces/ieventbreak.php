<?php

namespace LookPhp\Event\Interfaces;

/**
 * Исключение связанное с прерыванием цепочки событий
 * делающее данный обработчик крайним
 */
interface IEventBreak extends IEventTask {}