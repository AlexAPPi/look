import {ErrorObject} from './ErrorObject';

/**
 * Объект ошибки запроса
 */
export default class ErrorQueryObject extends ErrorObject
{
    /**
     * Параметры запроса
     */
    public requestParams : any;

    /**
     * @param code   Код ошибки
     * @param msg    Сообщение
     * @param params Параметры запроса
     */
    constructor(code : number, msg : string, params : any)
    {
        super(code, msg);

        this.requestParams = params;
    }

    /**
     * Возвращает объект ошибки
     * 
     * @param code    Код ошибки
     * @param msg     Сообщение
     * @param data    Параметры запроса
     * @param extract Вернуть как объект
     */
    public static simulationObject(code : number, msg : string, data : any, extract : boolean = false) : ErrorQueryObject|object
    {
        var obj = new ErrorQueryObject(code, msg, data);

        if(extract) {
            return obj;
        }
        
        return {error: obj};
    }
};