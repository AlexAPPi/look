import {getXhr, getCurrectTimeStamp} from '../Util';
import {QueryMethod as RequestMethod} from '../Use/QueryData';
import ErrorRequestObject from '../Use/ErrorQueryObject';
import QueryData from '../Use/QueryData';
import Query from '../Use/Query';
import Token from './Token';

export { RequestMethod, ErrorRequestObject }

/**
 * @see PHP API Controller file
 */

/** Название аргумента, по каторому передается токен */
export const accessTokenArgName    = 'access_token';

/** Название заголовка, через который передается токен */
export const accessTokenHeaderName = 'X-Look-Access-Token';
    
/** Название аргумента по которому передается зашифрованное сообщение */
export const protectedDataArgName = 'protected_data';

/** Название заголока, через который передается зашифрованное сообщение */
export const protectedDataHeaderName = 'X-Look-Protected-Data';

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
    
    /** @inheritdoc */
    protected initXHR() : XMLHttpRequest
    {
        var self    : this           = this,
            headers : any[any]       = {},
            xhr     : XMLHttpRequest = getXhr();
        
        if(!xhr) {
            throw new Error('не удалось инициализировать XHR');
        }
        
        var queryData : RequestData = self.queryData;
        var params    : any         = queryData.getData();
                
        // Запрос с использованием технологии тунеля
        if(queryData.tunnel) {
            
            // Не поддерживает отправку зашифрованный данных
            if(!queryData.token || !queryData.token.supportSendProtectedData()) {
                throw new Error('Session token can\'t send protected data');
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
                throw new Error('Can\'t encrypt data');
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
                    self.callbackError(new ErrorRequestObject(xhr.status, xhr.responseText, params));
                }
            }
        };
        
        xhr.onerror = function() {
            if(!callFromChange) {
                self.callbackError(new ErrorRequestObject(xhr.status, xhr.responseText, params));
            }
        };
        
        self.onAbort(function() {
            abortFromWaiter = true;
            xhr.abort();
        });

        xhr.onabort = function() {
            if(abortFromWaiter) {
                if(self.calledErrorIfNotSuccess()) {
                    self.callbackError(new ErrorRequestObject(-300, 'query abort', params));
                }
                else {
                    self.callbackAbort();
                }
            }
        };

        xhr.ontimeout = function() {
            
            var error = new ErrorRequestObject(-300, 'query timeout', params);

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
        var ans : any[any], error : ErrorRequestObject;

        // Парсим полученне данные
        // Если данные вернулись не в JSON формате
        // возвращаем ошибку 500
        try { ans = JSON.parse(xhr.responseText); }
        catch (e) {
            error = new ErrorRequestObject(500, xhr.responseText, data);
        }
        
        // Сервер возвращает ответ в объекте response
        if (!error && typeof ans.response !== 'undefined') {
            this.callbackSuccess(ans.response);
            return true;
        }
        
        // Сервер вернул ошибку
        if(!error && ans.error) {
            error = new ErrorRequestObject(
                ans.error.error_code,
                ans.error.error_msg,
                ans.error.request_params
            );
        }
        
        // Неизестная ошибка
        if(!error) {
            error = new ErrorRequestObject(500, xhr.responseText, data);
        }

        this.callbackError(error);
        return false;
    }

    /** @inheritdoc */
    protected checkBeforeInit() : boolean
    {
        var params = this.queryData.getData();

        // Жизнь токена истекла
        if(this.queryData.token && this.queryData.token.isExpired()) {
            throw Token.getExpiredTokenError(params);
        }

        // Используется тунель с плохим токеном или токен не задан
        if(this.queryData.tunnel && (!this.queryData.token || !this.queryData.token.supportSendProtectedData())) {
            throw Token.getBadTokenError(params);
        }

        // В имени метода или функции присудствуют запрещенные символы
        if(!NameChecker.test(this.queryData.apiClass) || !NameChecker.test(this.queryData.apiMethod)) {
            throw new ErrorRequestObject(500, 'в имени метода или функции присудствуют запрещенные символы', params);
        }

        return true;
    }
}