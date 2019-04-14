<?php

namespace Look\Event;

use Look\Event\Event;
use Look\Event\EventSelector;
use Look\Event\Exceptions\EventManagerException;

use Look\Event\Task\EventTask;

use Look\Event\Interfaces\IEventBreak;
use Look\Event\Interfaces\IEventArgReplace;
use Look\Event\Interfaces\IEventResultReplace;
use Look\Event\Interfaces\IEventHandlerDisable;

/**
 * Базовый класс менеджера событий в системе
 * 
 * @author Alexandr Shamarin <alexsandrshamarin@yandex.ru>
 */
class EventManager
{
    use \Look\Type\Traits\Singleton;
    
    private static $buffer = [];
    
    /** Селектор всех классов */
    const SelectorAll      = '*';
    
    /** Селектор конкретного класса */
    const SelectorThis     = '=';
    
    /** Селектор всех дочерних */
    const SelectorChildren = '~';
    
    /** @var array Содержит список связанных событий */
    private $indexer  = [];
    
    /** @var array Список обработчиков */
    private $handlers = [];
    
    /** EventManager */
    private function __construct() {}
    
    /**
     * Регистрирует обработчик события
     * 
     * @param array                              $selectorType -> Тип селектора
     * @param \Look\Event\EventSelector|array    $selector     -> Селектор
     * @param string                             $eventName    -> Назание события (регистронезависимое)
     * @param callable                           $handler      -> Обработчик
     * @param int                                $priority     -> Приоритет
     * 
     * @throws \Look\Event\Exceptions\EventManagerException
     */
    private static function reg(string $selectorType, $selector, string $eventName, callable $handler, int $priority = 0) : array
    {
        $eventName = strtolower($eventName);
        
        if(is_string($selector)) {
            if($selector == '*') {
                $selector = new EventSelector($eventName);
            } else {
                $tmp = explode('::', $selector);
                if(count($tmp) == 2) {
                    if(method_exists($tmp[0], $tmp[1])) {
                        $selector = new EventSelector($eventName, $tmp[0], $tmp[1]);
                    }
                } else if(class_exists($tmp[0])) {
                    $selector = new EventSelector($eventName, $tmp[0]);
                }
            }
        }
        else if(is_array($selector) && count($selector) == 2) {
            if(method_exists($selector[0], $selector[1])) {
                $selector = new EventSelector($eventName, $selector[0], $selector[1]);
            }
        }
        
        if(!$selector instanceof EventSelector) {
            throw new EventManagerException('Неверный формат селектора');
        }
        
        // Склеиваем селектор
        $selectorRef = "$selectorType@$selector";
        $handlers    = &static::getInstance()->handlers;
        
        if(!isset($handlers[$selectorRef])) {
            $handlers[$selectorRef]  = [];
        }
        
        if(!isset($handlers[$selectorRef][$eventName])) {
            $handlers[$selectorRef][$eventName]  = [];
        }
        
        $unicalId = count($handlers[$selectorRef][$eventName]);
        $handlers[$selectorRef][$eventName][$unicalId]  = [true, $handler];
        
        return [
            'selectorType' => $selectorType,
            'selectorRef'  => $selectorRef,
            'selector'     => $selector,
            'eventName'    => $eventName,
            'priority'     => $priority,
            'id'           => $unicalId
        ];
    }
    
    /**
     * Проводит сверку классов
     * @param string        $type
     * @param EventSelector $handlerSelector
     * @param EventSelector $eventSelector
     * @return bool
     */
    private static function checkClass(string $type, EventSelector $handlerSelector, EventSelector $eventSelector) : bool
    {
        $eventSelectorIsClass     = false;
        $eventSelectorIsInterface = false;
        
        if($eventSelector->hasClass()) {
            $eventSelectorIsClass = class_exists($eventSelector->getClass());
            if(!$eventSelectorIsClass) {
                $eventSelectorIsInterface = interface_exists($eventSelector->getClass());
                if(!$eventSelectorIsInterface) {
                    //throw new EventManagerException('Неверный формат селектора события: ' . $eventSelector->getName());
                    return false;
                }
            }
        }
        
        // Селектор задан по конкретному классу
        // и соотетствует классу события
        if($type != static::SelectorChildren) {
            
            if($handlerSelector->hasClass()) {
                
                return $handlerSelector->getClass() == $eventSelector->getClass();
            }

            if($type == static::SelectorThis) {
                return false;
            }
        }
        
        $handlerSelectorIsClass     = false;
        $handlerSelectorIsInterface = false;
        
        if($handlerSelector->hasClass()) {
            $handlerSelectorIsClass = class_exists($handlerSelector->getClass());
            if(!$handlerSelectorIsClass) {
                $handlerSelectorIsInterface = interface_exists($handlerSelector->getClass());
                if(!$handlerSelectorIsInterface) {
                    // Фильтр (Селектор) обработчика пустой
                    return true;
                }
            }
        }
        
        // Проверка для all прошла выше
        return is_subclass_of($eventSelector->getClass(), $handlerSelector->getClass());
    }
    
    /**
     * Проверяет селекторы
     * @param array         $handler       -> Обработчик события
     * @param EventSelector $eventSelector -> Селектор события
     * @return bool
     * @throws EventManagerException
     */
    private static function checkSelector(array $handler, EventSelector $eventSelector) : bool
    {
        $handlerSelector = $handler['selector'];
        if($handlerSelector->getName() != $eventSelector->getName()) {
            return false;
        }
        
        // Селектор обработчика заточен, только под назания
        if(!$handlerSelector->hasClass() && !$handlerSelector->hasFunction()) {
            return true;
        }
        
        $methodCheck = true;
        if($eventSelector->hasFunction()) {
            if($handlerSelector->hasFunction()) {
                $methodCheck = $eventSelector->getFunction() == $handlerSelector->getFunction();
            }
        }
        
        $handlerSelectorType = $handler['selectorType'];
        switch($handlerSelectorType) {
            case static::SelectorAll: break;
            case static::SelectorThis: break;
            case static::SelectorChildren: break;
            default: throw new EventManagerException("Указанный тип селектора [$handlerSelectorType] не поддерживается");
        }
        
        return static::checkClass($handlerSelectorType, $handlerSelector, $eventSelector) && $methodCheck;
    }
    
    public static function __callStatic($name, $arguments)
    {
        if(substr($name, 0, 2) == 'on') {
            
            if(count($arguments) > 3) {
                throw new \ArgumentCountError();
            }
            
            $event = substr($name, 2);
            $arguments[2] = $arguments[2] == null ? 0 : $arguments[2];
            static::on($arguments[0], $event, $arguments[1], $arguments[2]);
        }
        
        throw new \BadMethodCallException();
    }
    
    /**
     * Регистрирует обработчик события
     * 
     * <b>Назания событий регистронезависимы</b><br>
     * 
     * <b>По умолчанию селектор не используется</b><br>
     * <b>Ссылка селектора: </b><br>
     * <b>~</b> - Указывает на наследников класса</li><br>
     * <b>=</b> - Указывает на конкретный класс</li><br>
     * <b>*</b> - Указывает на конкретный класс и его наследников</li><br>
     * 
     * Пример использования селектора:<br>
     * ['*' => human, '~' => human, '=' => 'human']
     * 
     * @param array|null $selector -> Селекторы (доступные форматы смотрите в функции checkSelector)
     * @param string     $event    -> Назание события (регистронезаисимое)
     * @param callable   $handler  -> Обработчик
     * @param int        $priority -> Приоритет
     * 
     * @return int Уникальный индекс обработчика
     */
    public static function on(?array $selector, string $event, callable $handler, int $priority = 0) : int
    {
        $event = strtolower($event);
        
        if($selector === null) {
            $selector = ['*' => '*'];
        }
        
        $ids = [];
        foreach($selector as $selectorKey => $selectorPreg) {
            
            if (!is_array($selectorPreg)) {
                $selectorPreg = [$selectorKey => $selectorPreg];
            }
            
            foreach($selectorPreg as $selectorPregItem) {
                $ids[] = static::reg($selectorKey, $selectorPregItem, $event, $handler, $priority);
            }
        }
        
        $indexer   = &static::getInstance()->indexer;
        $unicalID  = count($indexer);
        $indexer[] = $ids;
        
        return $unicalID;
    }
    
    /**
     * Выполняет выборку обработчиков по селектору
     * @param EventSelector $selector -> Селектор
     * @param bool          $remove   -> Удалить выбранное
     * @return array
     * @throws EventManagerException
     */
    private static function &selectHandlers(EventSelector $selector) : array
    {        
        $eventName = $selector->getName();
        $indexer   = &static::getInstance()->indexer;
        $handlers  = &static::getInstance()->handlers;
        
        $resultHandlers         = [];
        $resultHandlersPriority = [];

        foreach($indexer as $eventHandlers) {

            // Защита от вызова удаленных обработчиков
            if($eventHandlers === null) {
                continue;
            }
            
            foreach($eventHandlers as $eventHandler) {
                
                // Защита от вызова удаленных обработчиков
                if($eventHandler === null) {
                    continue;
                }
                
                // Индексер хранит назание события
                // для каждого обработчика отдельно
                if($eventName == $eventHandler['eventName']) {
                    
                    if(static::checkSelector($eventHandler, $selector)) {

                        if(isset($handlers[$eventHandler['selectorRef']])) {
                            
                            $tmp = &$handlers[$eventHandler['selectorRef']];
                            if(isset($tmp[$eventName])) {
                                
                                $tmp = &$tmp[$eventName];
                                if(isset($tmp[$eventHandler['id']])) {
                                    $resultHandlers[]         = &$tmp[$eventHandler['id']];
                                    $resultHandlersPriority[] = $eventHandler['priority'];
                                }
                            }
                        }
                    }
                }
                else break;
            }
        }

        arsort($resultHandlersPriority);
        $result = [
            array_keys($resultHandlersPriority),
            &$resultHandlers
        ];
        return $result;
    }
    
    /**
     * Устаналивает флаг активности для обработчиков подходящих к данному селектору
     * @param EventSelector $selector
     * @param bool          $flag
     * @return int -> Количесто обработчиков
     */
    private static function setFlag(EventSelector $selector, bool $flag) : int
    {
        //var_dump(static::getInstance()->handlers);
        $res  = 0;
        $data = &static::selectHandlers($selector);
        $c    = count($data[1]);
        for($i = 0; $i < $c; $i++) {
            $data[1][$i][0] = $flag;
            $res++;
        }
        //var_dump(static::getInstance()->handlers);
        return $res;
    }
    
    /**
     * Отвязывает события по указанному селектору
     * @param EventSelector $selector
     * @return int -> Количесто обработчиков
     */
    public static function disable(EventSelector $selector) : int
    {
        return static::setFlag($selector, false);
    }
    
    /**
     * Активирует события по указанному селектору
     * @param EventSelector $selector
     * @return int -> Количесто обработчиков
     */
    public static function enable(EventSelector $selector) : int
    {
        return static::setFlag($selector, true);
    }
    
    /**
     * Выполняет вызов обработчиков указанного события
     * 
     * Обработчик принимает 2 аргумента, результат выполнения и индекс обработчика
     * 
     * @param \Look\ExceptionEvent $event    -> Событие
     * @param mixed                $argument -> Переданные аргументы
     * @param mixed                $result   -> Результаты функции
     * @param callable|null        $handler  -> Обработчик результатов события
     * @return \Look\Event\EventResult
     */
    public static function exec(Event $event, array &$argument, &$result, ?callable $handler = null)
    {
        $refRes = &$result;
        $refArg = &$argument;
        $data   = &static::selectHandlers($event->getSelector());
        $name   = $event->getSelector()->getName();
        foreach($data[0] as $index) {
            // 0 - флаг активности
            // 1 - функция обработчика
            if($data[1][$index][0] === true) {
                try {
                    switch($name)
                    {
                        case 'return':
                            $tmp = $data[1][$index][1]($refRes, $refArg);
                            if($handler) {
                                $handler($tmp, $refArg, $refRes);
                            }
                        break;
                        
                        default:
                            $tmp =  $data[1][$index][1](...$refArg);
                            if($handler) {
                                $handler($tmp, ...$refArg);
                            }
                        break;
                    }
                }
                catch(EventTask $ex) {
                    
                    // Подмена аргументов на уровне вызова
                    if($ex instanceof IEventArgReplace) {
                        $c = count($refArg);
                        for($i = 0; $i < $c; $i++) {
                            $link = &$refArg[$i];
                            $link = $ex->getReplaceArgumentByIndex($i);
                        }
                    }
                    
                    if($ex instanceof IEventResultReplace) {
                        $refRes = $ex->getReplaceResult();
                    }
                    
                    if($ex instanceof IEventHandlerDisable) {
                        $data[1][$index][0] = false;
                    }
                    
                    if($ex instanceof IEventBreak) {
                        break;
                    }
                }
            }
        }
        
        unset($link);
        unset($refArg);
        unset($argument);
        
        return $refRes;
    }
}