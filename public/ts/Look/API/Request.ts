import {getXhr, getCurrectTimeStamp} from '../Util';
import {QueryMethod as RequestMethod} from '../Use/QueryData';
import ErrorRequestObject from '../Use/ErrorQueryObject';
import QueryData from '../Use/QueryData';
import Query from '../Use/Query';
import Token from './Token';

export { RequestMethod, ErrorRequestObject }

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/**
 * @see PHP API Controller file
 */

/** Название аргумента, по каторому передается токен */
export const accessTokenArgName    = 'accessToken';

/** Название заголовка, через который передается токен */
export const accessTokenHeaderName = 'X-Look-Access-Token';
    
/** Название аргумента по которому передается зашифрованное сообщение */
export const protectedDataArgName = 'protectedData';

/** Название заголока, через который передается зашифрованное сообщение */
export const protectedDataHeaderName = 'X-Look-Protected-Data';

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/**
 * Содержит список ошибок
 */
export enum ErrorRequestCode
{
    /** Неизвестная ошибка */
    Unknow = 800000,
    
    /** Не удалось инициализировать XHR */
    XHRFail = 800001,
    
    /** Превышен лимит ожидания */
    Timeout = 800002,

    /** Запрос был прерван */
    Abort   = 800003,

    /** Ответ пришел не в json формате */
    NotJson = 800004,
    
    /** Токен не поддерживает технологию тунеля */
    TokenNotTunnel = 800005,
    
    /** Не удалось зашифровать данные */
    ProtectedDataFail = 800005,
    
    /** Метод или функция укзаны с ошибкой */
    MethodOrFunctionFail = 800006
}

/**
 * Объект запроса
 */
export class RequestData extends QueryData
{
    constructor(
        readonly apiUrl    : string,
        readonly apiClass  : string,
        readonly apiMethod : string,
        readonly token    ?: Token,
        readonly data      : any = {},
        readonly tunnel    : boolean = false,

        method ?: RequestMethod
    ) {
        // url/class.method
        super(apiUrl + apiClass + '.' + apiMethod, data, method);
    }
}

/**
 * Регулярка для проверки имен
 */
export const NameChecker = /^[a-z]+$/i;

/**
 * Значение аргумента
 */
export class RequestAnsParam
{
    /**
     * @param key   -> Ключ
     * @param value -> Значение
     */
    constructor(readonly key : string, readonly value : any) {}
}

/**
 * Список передаваемых аргументов
 */
export class RequestAnsParamList extends Array<RequestAnsParam> {}

/**
 * Объект ошибки
 */
export interface RequestAnsError
{
    /** Код ошибки */
    error_code ?: number,
    
    /** Описание ошибки */
    error_msg  ?: string,
    
    /** Параметры запроса */
    request_params ?: Array<RequestAnsParam>
}

/**
 * Объект ответа
 */
export interface RequestAns
{
    /** Возвращается при успешном запросе */
    response ?: any,
    
    /** Возвращается при возникновении некой ошибки */
    error ?: RequestAnsError
}

/**
 * Класс запроса к API
 */
export class Request extends Query
{
    /**
     * @param queryData Объект запроса
     * @param delay     Задержка перед запросом
     * @param timeout   Максимальное время ожидание запроса
     */
    constructor(protected queryData : RequestData, delay ?: number, timeout ?: number)
    {
        super(queryData, delay, timeout);
    }
    
    /**
     * Преобразует список параметров в объект ответа
     * @param params Список параметров
     */
    protected convertParamsToAns(params : any[]) : RequestAnsParamList
    {
        var list : RequestAnsParamList = new RequestAnsParamList(), i : any;
        for(i in params) {
            if (params.hasOwnProperty(i)) {
                var item = new RequestAnsParam(i, params[i]);
                list.push(item);
            }
        }
        return list;
    }
    
    /** @inheritdoc */
    protected initXHR() : XMLHttpRequest
    {
        var self      : this           = this,
            headers   : any[any]       = {},
            xhr       : XMLHttpRequest = getXhr(),
            queryData : RequestData    = self.queryData,
            params    : any            = queryData.getData();
        
        if(!xhr) {
            throw new ErrorRequestObject(
                ErrorRequestCode.XHRFail,
                'Failed to initialize xhr',
                params
            );
        }
                        
        // Запрос с использованием технологии тунеля
        if(queryData.tunnel) {
            
            // Не поддерживает отправку зашифрованный данных
            if(!queryData.token
            || !queryData.token.supportSendProtectedData()) {
                throw new ErrorRequestObject(
                    ErrorRequestCode.TokenNotTunnel,
                    'Token does not support tunnel technology',
                    params
                );
            }
            
            // Получаем токен из данных запроса и удаляем его
            // Предварительно поместив значение в заголовок запроса
            // Если токен не передан в параметрах, берем его из тела объекта запроса
            if(queryData.hasDataByName(accessTokenArgName)) {
                headers[accessTokenHeaderName] = queryData.getDataByName(accessTokenArgName);
                queryData.unsetDataByName(accessTokenArgName);
            } else {
                headers[accessTokenHeaderName] = queryData.token.accessToken;
            }
            
            // Данные конвертируем в json и шифруем
            var encryptData   : string = JSON.stringify(queryData.getData());
            var protectedData : any    = queryData.token.publicKey.encrypt(encryptData);
            
            // Не удалось зашифровать данные
            if(protectedData == null) {
                throw new ErrorRequestObject(
                    ErrorRequestCode.TokenNotTunnel,
                    'Could not encrypt data',
                    params
                );
            }

            // Шифруем данные и помещаем в заголовок запроса
            headers[protectedDataHeaderName] = protectedData;
            
            // Выполняем подмену данных на токен и время
            queryData.setData({});
        }
        // добавляем токен, если он существует
        else if(!params[accessTokenArgName] && queryData.token) {
            queryData.setDataByName(accessTokenArgName, queryData.token.accessToken);
        }
        
        // no-cache
        queryData.setDataByName('__ts', getCurrectTimeStamp());

        // Обновляем данные
        params = queryData.getData();

        xhr.open(queryData.getMethod(), queryData.getUrl(), true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        var accept = false;
        for(var name in headers) {
            if(headers.hasOwnProperty(name)) {
                xhr.setRequestHeader(name, headers[name]);
                accept = true;
            }
        }

        if(accept) {
            xhr.setRequestHeader("Accept", "text/xml");
        }

        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.setRequestHeader("Cache-Control", "no-cache");

        /////////////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////////////
        
        // Для избежания дублей при вызове
        // из событий onreadystatechange и self.onAbort
        // вводим 2 переменные, которые хранят флаг вызова
        var callFromChange  = false;
        var abortFromWaiter = false;

        xhr.onreadystatechange = function() {

            self.callbackReadyStateChange(xhr);

            if (xhr.readyState === 4) {

                if(xhr.status === 200) {
                    var result = self.responceHandler(xhr, params);
                    if(!result) {
                        callFromChange = true;
                    }
                }
                else {
                    callFromChange = true;
                    self.callbackError(
                        new ErrorRequestObject(
                            xhr.status,
                            xhr.responseText,
                            params
                        )
                    );
                }
            }
        };
        
        xhr.onerror = function() {
            if(!callFromChange) {
                self.callbackError(
                    new ErrorRequestObject(
                        xhr.status,
                        xhr.responseText,
                        params
                    )
                );
            }
        };
        
        self.onAbort(function() {
            abortFromWaiter = true;
            xhr.abort();
        });
        
        xhr.onabort = function() {
            if(abortFromWaiter) {
                if(self.calledErrorIfNotSuccess()) {
                    self.callbackError(
                        new ErrorRequestObject(
                            ErrorRequestCode.Abort,
                            'Abort',
                            params
                        )
                    );
                }
                else {
                    self.callbackAbort();
                }
            }
        };

        xhr.ontimeout = function() {
            
            var error = new ErrorRequestObject(
                ErrorRequestCode.Timeout,
                'Timeout',
                params
            );

            if(self.calledErrorIfNotSuccess()) self.callbackError(error);
            else                               self.callbackTimeout(error);
        };

        xhr.onprogress = function(event : any) { self.callbackProcess(event); };

        // Активируем ограничение времени ожидания запроса
        if (self.timeout) {
            xhr.timeout = self.timeout;
        }

        // Get or set
        if(queryData.isGet()) { xhr.send(); }
        else                  { xhr.send(queryData.getSendData()); }
        
        self.callbackStart();
        return xhr;
    }

    /** @inheritdoc */
    protected responceHandler(xhr : XMLHttpRequest, data ?: any)
    {
        var ans : RequestAns, error : ErrorRequestObject;

        // Парсим полученне данные
        // Если данные вернулись не в JSON формате
        // возвращаем ошибку NotJson
        try { ans = JSON.parse(xhr.responseText); }
        catch (e) {
            error = new ErrorRequestObject(
                ErrorRequestCode.NotJson,
                xhr.responseText,
                data
            );
            this.callbackError(error);
            return false;
        }
        
        // Сервер возвращает ответ в объекте response
        if (typeof ans.response !== 'undefined') {
            this.callbackSuccess(ans.response);
            return true;
        }
        
        // Сервер вернул ошибку
        if(ans.error) {
            error = new ErrorRequestObject(
                ans.error.error_code,
                ans.error.error_msg,
                ans.error.request_params
            );
        }
        
        // Неизестная ошибка
        if(!error) {
            error = new ErrorRequestObject(
                ErrorRequestCode.Unknow,
                xhr.responseText,
                data
            );
        }

        this.callbackError(error);
        return false;
    }

    /** @inheritdoc */
    protected checkBeforeInit() : boolean
    {
        var origin = this.queryData.getData();
        var params = this.convertParamsToAns(origin);

        // Жизнь токена истекла
        if(this.queryData.token && this.queryData.token.isExpired()) {
            throw Token.getExpiredTokenError(params);
        }

        // Используется тунель с плохим токеном или токен не задан
        if(this.queryData.tunnel && (!this.queryData.token || !this.queryData.token.supportSendProtectedData())) {
            throw Token.getBadTokenError(params);
        }

        // В имени метода или функции присудствуют запрещенные символы
        if(!NameChecker.test(this.queryData.apiClass)
        || !NameChecker.test(this.queryData.apiMethod)) {
            throw new ErrorRequestObject(500, 'в имени метода или функции присудствуют запрещенные символы', params);
        }

        return true;
    }
}

export default Request;