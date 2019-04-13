import {Request, RequestData, RequestMethod} from './Request';
import Queryable from '../Waiter/Queryable';
import Md5 from '../../Lib/Md5';
import {Token, TokenJsonFormat} from './Token';

/** API Класс авторизации */
export const apiAuthClass  = 'Auth';

/** API Метод авторизации */
export const apiAuthMethod = 'getTunnelToken';

/**
 * Объект сессии
 */
export default class Session
{
    /** Токен сессии */
    public    token : Token = null;
    
    /** Логин пользователя */
    protected login : string;
    
    /** Сигнатура логина и пароля */
    protected signature : string;
    
    /** URL к api */
    protected apiUrl : string = '/api/';

    /**
     * Создает новую сессию для запроса к API
     * 
     * При успешном создании новой сессии
     * onSuccess будет передан новый объект API,
     * с новым токеном и секретным ключом
     * 
     * @param login   Логин
     * @param pass    Пароль
     * @param expires Время жизни сессии (в секундах) 0 - бессметрная
     * @param delay   Задержка перед открытием сессии
     */
    constructor(login : string, pass : string)
    {
        this.login     = login;
        this.signature = Md5.hashStr(pass + login).toString();
    }

    /**
     * Возвращает url API
     */
    public getApiServerUrl()
    {
        return this.apiUrl;
    }

    /**
     * Устанавливает базовый url
     * @param url Url
     */
    public setApiServerUrl(url : string)
    {
        this.apiUrl = url;
        return this;
    }

    /**
     * Проверяет, истекло ли время жизни сессии
     */
    public isExpired()
    {
        return this.token && this.token.isExpired();
    }

    /**
     * Открывает сессию
     * @param expires Время жизни сессии (в секундах) 0 - бессметрная
     * @param delay   Задержка перед открытием сессии
     * @param timeout Максимальное время ожидание запроса
     */
    public open(expires : number = 0, delay ?: number, timeout ?: number)
    {
        var self : this = this;
        var data : any[any] = {
            login:     self.login,
            signature: self.signature,
            expires:   expires
        };

        var requestData = new RequestData(
            self.apiUrl,
            apiAuthClass,
            apiAuthMethod,
            self.token,
            data,
            false,
            RequestMethod.GET
        );
        
        return new Queryable(function(this : Queryable) {

            var requestWaiter : Queryable = this;    
            var request       : Request   = new Request(requestData, delay, timeout);

            request
            .onAbort(function(data : any)   { requestWaiter.callbackAbort(data); })
            .onStart(function(data : any)   { requestWaiter.callbackStart(data); })
            .onTimeout(function(data : any) { requestWaiter.callbackError(data); })
            .onError(function(err : any)    { requestWaiter.callbackError(err); })
            .onSuccess(function(data : TokenJsonFormat) {

                self.token = new Token(
                    data.access_token,
                    data.expires_in,
                    data.public_key,
                    data.private_key,
                );

                requestWaiter.callbackSuccess(self);
            });

        }, delay);
    }
    
    
    
    /**
     * Выполняет запрос к API
     * @param method Метод запроса имеющий вид 
     * @param data 
     */
    public get(method : string, data ?: any, tunnel : boolean = false, requestMethod : RequestMethod = RequestMethod.GET)
    {
        var classAndMethod = method.split('.');
        if(classAndMethod.length != 2) {
            throw new Error('bad class or method name');
        }

        return new Request(
            new RequestData(
                this.apiUrl,
                classAndMethod[0],
                classAndMethod[1],
                this.token,
                data,
                tunnel,
                requestMethod
        ));
    }
}