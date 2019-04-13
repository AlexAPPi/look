/**
 * @version 0.1.0b
 * @author Alexandr Shamarin [alexsandrshamarin@yandex.ru]
 * @copyright Alexandr Shamarin [2019]
 * @license MIT
 * @package Look.Waiter
 */

import Errorable from "./Errorable";

/**
 * Объект ожидания ответа
 */
export default class Queryable extends Errorable
{
    /** Всегда вызывать ошибку если ответ не получен */
    protected vCallErrorIfNotSuccess : boolean = false;

    /**
     * Всегда вызывает ошибку, вместо других событий (onabort, ontimeout, ...),
     * если ответ не получен
     */
    public callErrorIfNotSuccess(value : boolean = true)
    {
        this.vCallErrorIfNotSuccess = value;
        return this;
    }

    /**
     * Проверяет, вызывается ли ошибка, вместо других событий (onabort, ontimeout, ...)
     */
    public calledErrorIfNotSuccess() : boolean
    {
        return this.vCallErrorIfNotSuccess;
    }

    /**
     * Инициализирует событие запуска
     * @param data    Данные для передачи
     * @param newThis Указатель объекта (this) для обработчика
     */
    public callbackStart(data ?: any, newThis ?: any)
    {
        return this.callback("start", data, newThis);
    }

    /**
     * Инициализирует событие прекращения ожидания запроса
     * @param data    Данные для передачи
     * @param newThis Указатель объекта (this) для обработчика
     */
    public callbackTimeout(data ?: any, newThis ?: any)
    {
        return this.callback("timeout", data, newThis);
    }

    /**
     * Инициализирует событие процесса
     * @param data    Данные для передачи
     * @param newThis Указатель объекта (this) для обработчика
     */
    public callbackProcess(data ?: any, newThis ?: any)
    {
        return this.callback("process", data, newThis);
    }

    /**
     * Устанавливает обработчик события при старте запроса
     * @param callback Обработчик
     */
    public onStart(callback : Function)
    {
        return this.on("start", callback);
    }

    /**
     * Устанавливает обработчик события при прекращении ожидания запроса
     * @param callback Обработчик
     */
    public onTimeout(callback : Function)
    {
        return this.on("timeout", callback);
    }

    /**
     * Устанавливает обработчик события при выполнении процесса
     * @param callback Обработчик
     */
    public onProcess(callback : Function)
    {
        return this.on("process", callback);
    }
}