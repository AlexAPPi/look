/**
 * @version 0.1.0b
 * @author Alexandr Shamarin [alexsandrshamarin@yandex.ru]
 * @copyright Alexandr Shamarin [2019]
 * @license MIT
 * @package Look.Waiter
 */

import Simple from "./Simple";

/**
 * Объект ожидания с ошибкой
 */
export default class Errorable extends Simple
{
    /**
     * Устанавливает обработчик события при успешном ожидании
     * @param callback Обработчик
     */
    public onSuccess(callback : Function)
    {
        return this.on("success", callback);
    }

    /**
     * Устанавливает обработчик события при ожидании с ошибкой
     * @param callback Обработчик
     */
    public onError(callback : Function)
    {
        return this.on("error", callback);
    }

    /**
     * Инициализирует событие успеха
     * @param data    Данные для передачи
     * @param newThis Указатель объекта (this) для обработчика
     */
    public callbackSuccess(data ?: any, newThis ?: any)
    {
        return this.callback("success", data, newThis);
    }

    /**
     * Инициализирует событие ошибки
     * @param data    Данные для передачи
     * @param newThis Указатель объекта (this) для обработчика
     */
    public callbackError(data ?: any, newThis ?: any)
    {
        return this.callback("error", data, newThis);
    }
}