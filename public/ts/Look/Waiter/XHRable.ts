import Queryable from './Queryable';

/**
 * События из xhr объекта
 */
export default class XHRable extends Queryable
{
    /**
     * Устанавливает обработчик события при успешной загрузке
     * @param callback Обработчик
     */
    public onLoad(callback : Function)
    {
        return this.on("load", callback);
    }

    /**
     * Инициализирует событие при успешной загрузке
     * @param data    Данные для передачи
     * @param newThis Указатель объекта (this) для обработчика
     */
    public callbackLoad(data ?: any, newThis ?: any)
    {
        return this.callback("load", data, newThis);
    }

    /**
     * Устанавливает обработчик события при начале загрузке
     * @param callback Обработчик
     */
    public onLoadStart(callback : Function)
    {
        return this.on("loadstart", callback);
    }

    /**
     * Инициализирует событие при начале загрузке
     * @param data    Данные для передачи
     * @param newThis Указатель объекта (this) для обработчика
     */
    public callbackLoadStart(data ?: any, newThis ?: any)
    {
        return this.callback("loadstart", data, newThis);
    }

    /**
     * Устанавливает обработчик события при завершении загрузке
     * @param callback Обработчик
     */
    public onLoadEnd(callback : Function)
    {
        return this.on("loadend", callback);
    }

    /**
     * Инициализирует событие при завершении загрузке
     * @param data    Данные для передачи
     * @param newThis Указатель объекта (this) для обработчика
     */
    public callbackLoadEnd(data ?: any, newThis ?: any)
    {
        return this.callback("loadend", data, newThis);
    }

    /**
     * Устанавливает обработчик события при изменении свойства XHR.readyState
     * @param callback Обработчик
     */
    public onReadyStateChange(callback : Function)
    {
        return this.on("readystatechange", callback);
    }

    /**
     * Инициализирует событие readystatechange связанное с изменением свойства XHR.readyState
     * @param data    Данные для передачи
     * @param newThis Указатель объекта (this) для обработчика
     */
    public callbackReadyStateChange(data ?: any, newThis ?: any)
    {
        return this.callback("readystatechange", data, newThis);
    }
}