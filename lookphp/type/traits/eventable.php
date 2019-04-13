<?php

namespace LookPhp\Type\Traits;

trait Eventable
{    

    
    /**
     * 
     * @param \LookPhp\Type\Traits\ExceptionEvent $event
     * @return bool
     */
    public static function exec(ExceptionEvent $event) : bool
    {
        $eventName = $event->getName();
        if(isset(static::getInstance()->events[$eventName])) {
            foreach(static::getInstance()->events[$eventName] as $selectorAndHandler) {
                $selectorOk = false;
                foreach($selectorAndHandler[0] as $selector) {
                    if($selector == get_class($event->getException()) || is_subclass_of($event->getException(), $selector)) {
                        
                    }
                }
                try {
                    $selectorAndHandler[1](...$arguments);
                }
                catch (EventBreak $ex) {
                    return false;
                }
                catch (Throwable $ex) {
                    return static::exec($event);
                }
            }
        }
        
        return true;
    }
}