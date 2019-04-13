/**
 * @version 0.1.0b
 * @author Alexandr Shamarin [alexsandrshamarin@yandex.ru]
 * @copyright Alexandr Shamarin [2019]
 * @license MIT
 * @package Look.Waiter
 */

import { isArray, isFunction } from '../Util';

/**
 * Объект ожидания (Официант данных)
 */
export default class Simple
{
    /** Время задержки в милисекундах */
    protected delay : number;

    /** Указатель на объект this в callback */
    protected newThis : any;

    /** Массив с функциями обработки событий */
    protected callbacks : any[any] = {};

    /** Индекс таймера запуска */
    protected timerBegin : number|null;

    /** Буфер обмена */
    protected buffer : any;

    /** Флаг запуска */
    protected running : boolean = false;

    /** Конструктор запроса */
    private initCallback : Function;
    
    /**
     * Создает новый объект ожидания (Официант данных)
     * @param initCallback Конструктор запроса
     * @param delay        Задержка перед инициализацией
     */
    constructor(initCallback : Function, delay : number = 100)
    {
        this.newThis      = this;
        this.delay        = delay;
        this.initCallback = initCallback;
        this.sleep();
    }

    /**
     * Возвращает время ожидания
     */
    public getDelay()
    {
        return this.delay;
    };

    /**
     * Возвращает массив из обработчиков
     */
    public getCallbacks()
    {
        return this.callbacks;
    }

    /**
     * Очищает массив с обработчиками
     * @param eventName Название события
     */
    public clearCallbacks(eventName ?: string)
    {
        if(eventName) {
            if (typeof this.callbacks[eventName] === 'undefined') {
                this.callbacks[eventName] = null; // clear memory
                this.callbacks[eventName] = [];
            }
        }
        else {
            this.callbacks = null; // clear memory
            this.callbacks = {};
        }
        return this;
    }

    /**
     * Устанавливает значение в буфер
     * @param data значение для буфера
     */
    public setBuffer(data : any)
    {
        this.buffer = data;
        return this;
    }

    /**
     * Устанавливает указатель this для callback
     * @param nthis Указатель this
     */
    public setThis(nthis : object) {
        this.newThis = nthis;
        return this;
    }

    /**
     * Обнуляет указатель this в обработчиках событий
     */
    public unsetThis() {
        this.newThis = this;
        return this;
    };

    /**
     * Функция присвоения событию callback
     * 
     * @param eventName Название события
     * @param callback  Функция обработчика
     */
    public on(eventName : string, callback : Function)
    {
        // Еще нет обработчиков
        if (typeof this.callbacks[eventName] === 'undefined' || this.callbacks[eventName] == null) {
            this.callbacks[eventName] = [];
            //console.log(callbacks);
        }

        this.callbacks[eventName].push(callback);
        return this;
    };

    /**
     * Функция вызова функции для указанного события
     * @param eventName Название события
     * @param data      Данные для передачи
     * @param newThis   Указатель объекта (this) для обработчика
     */
    public callback(eventName : string, data ?: any, newThis ?: any) : boolean
    {
        var flag  = false,
            calls = this.callbacks[eventName], i = 0;

        // Список обраток пустой
        if (isArray(calls) && calls.length > 0) {

            // Вызываем функции
            for (; i < calls.length; i++) {

                // Вызываем функции с конца
                //var callback = calls[count - i - 1];
                var callback : Function = calls[i];

                if (isFunction(callback)) {
                    
                    if (!newThis) {
                        newThis = this.newThis;
                    }

                    callback.call(newThis, data, this);
                    flag = true;
                }
            }
        }

        return flag;
    }
    
    /**
     * Устанавливает обработчик события при инициализации
     * @param callback
     */
    public onInit(callback : Function)
    {
        return this.on("init", callback);
    }

    /**
     * Устанавливает обработчик события при вызове отмены запроса
     * @param callback
     */
    public onAbort(callback : Function)
    {
        return this.on("abort", callback);
    }

    /**
     * Инициализирует инициализацию работы
     */
    public callbackInit(data : any = null)
    {
        this.callback("init", data);
    }

    /**
     * Инициализирует прекращение работы
     */
    public callbackAbort(data : any = null) : void
    {
        this.callback("abort", data);
    };

    /**
     * Останавливает ожидание
     */
    public abort(data : any, callback : boolean = true)
    {
        if(this.timerBegin) {
            clearTimeout(this.timerBegin);
        }
        
        // Процесс был уже запущен, прерываем его
        if (this.running && callback) {
            this.callbackAbort(data);
        }
        
        this.running = false;
        return this;
    };

    /**
     * Прерывает все ожидания и немедленно запускает инициализацию процесса
     */
    public init()
    {
        if(!this.running) {
            
            if(this.timerBegin) {
                clearTimeout(this.timerBegin);
            }
            
            this.running = true;
            this.initCallback.call(this);
            this.callbackInit();
        }
        
        return this;
    };

    /**
     * Откладывает ожидание
     */
    public sleep(time : number = null)
    {
        var self = this;

        this.abort(null, false);
        this.delay = time || this.delay;

        this.timerBegin = setTimeout(function () {
            self.init();
        }, this.delay);

        return this;
    }

    /**
     * Функция перезапускает процесс
     */
    public repeat(delay : number)
    {
        this.sleep(delay);
        return this;
    }
    
    /**
     * Проверяет существование обработчиков для указанного события
     * @param eventName 
     */
    public callbackExists(eventName : string)
    {
        return (typeof this.callbacks[eventName] !== 'undefined'
                && this.callbacks[eventName]
                && this.callbacks[eventName].length > 0)
    }
}