import {param, isUndefined} from '../Util';

/**
 * Метод запроса
 */
export enum QueryMethod {
    GET    = 'GET',
    PUT    = 'PUT',
    POST   = 'POST',
    DELETE = 'DELETE'
}

/**
 * Данные запроса
 */
export default class QueryData
{
    /**
     * @param url    Url запроса
     * @param data   Параметры запроса
     * @param method Метод запроса
     */
    constructor(protected url : string, protected data ?: any, protected method : QueryMethod = QueryMethod.GET)
    {
        this.data = data || {};
    }
    
    /**
     * Возвращает метод запроса
     */
    public getMethod() : QueryMethod
    {
        return this.method;
    }

    /** Метод GET */
    public isGet() : boolean { return this.getMethod() === QueryMethod.GET; }

    /** Метод PUT */
    public isPut() : boolean { return this.getMethod() === QueryMethod.PUT; }

    /** Метод POST */
    public isPost() : boolean { return this.getMethod() === QueryMethod.POST; }
    
    /** Метод DELETE */
    public isDelete() : boolean { return this.getMethod() === QueryMethod.DELETE; }

    /**
     * Возвращает параметры
     */
    public getData() : any
    {
        return this.data;
    }

    /**
     * Возвращает данные которе нужно отправить
     */
    public getSendData() : any
    {
        return param(this.data);
    }

    /**
     * Выполняет подмену параметов
     * @param data 
     */
    public setData(data : object) : void
    {
        this.data = data;
    }

    /**
     * Возвращает значение параметра
     * @param name  Назание параметра
     */
    public getDataByName(name: string) : any
    {
        return this.data[name];
    }

    /**
     * Устанавливает занчение для определенного параметра
     * @param name  Назание параметра
     * @param value Значние
     */
    public setDataByName(name: string, value : any) : void
    {
        this.data[name] = value;
    }

    /**
     * Удаляет параметр
     * @param name Назание параметра
     */
    public unsetDataByName(name : string)
    {
        delete this.data[name];
    }

    /**
     * Проверяет параметр
     * @param name Назание параметра
     */
    public hasDataByName(name : string)
    {
        return !isUndefined(this.data[name]);
    }

    /**
     * Возвращает url для запроса без параметров
     */
    public getClearUrl()
    {
        return this.url;
    }

    /**
     * Возвращает url для запроса (если метод GET, то вернется url с параметрами)
     */
    public getUrl() : string
    {
        if(this.method === QueryMethod.GET) {

            var params = this.getSendData();

            if(params.length > 0) {

                return this.url + '?' + params;
            }
        }

        return this.url;
    }
}