import XHRable from '../Waiter/XHRable';
import QueryData from './QueryData';

/**
 * Формирует запросы
 */
export default abstract class Query extends XHRable
{
    /** Объект запроса */
    protected xhr : XMLHttpRequest;

    /**
     * @param data    Объект запроса
     * @param expires Время жизни сессии (в секундах) 0 - бессметрная
     * @param delay   Задержка перед открытием сессии
     */
    constructor(protected queryData : QueryData, delay ?: number, protected timeout  ?: number)
    {
        super(function(this : Query) {
            try {
                this.checkBeforeInit();
                this.xhr = this.initXHR();
            }
            catch (e) {
                
                // При инициализации запроса произошла ошибка
                // Прекращаем запрос

                // Успел создаться объект запроса и запрос уже отправлен
                var callback = this.xhr
                            && this.xhr.readyState != 0
                            && this.xhr.readyState != 4;
                
                this.abort(e, callback);
                this.callbackError(e);
            }
        }, delay);
    }

    /**
     * Инициализиурет запрос
     * 
     * (Данная функция может бросать исключения)
     */
    protected abstract initXHR() : XMLHttpRequest;

    /**
     * Обработчик ответа 200
     * 
     * @param xhr  Объект XMLHttpRequest|ActiveX
     * @param data Дополнительные данные
     */
    protected abstract responceHandler(xhr : XMLHttpRequest, data ?: any) : boolean;

    /**
     * Выполняет проверку перед инициализацией
     * 
     * (Данная функция может бросать исключения)
     */
    protected abstract checkBeforeInit() : boolean;
}