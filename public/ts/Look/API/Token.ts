import {JSEncryptRSAKey} from '../../Lib/jsencrypt/JSEncryptRSAKey';
import {hex2a, getCurrectTimeStamp} from '../Util';

import ErrorQueryObject from '../Use/ErrorQueryObject';

/** Не верный формат токена */
export const BadTokenCode  = 70001;

/** Истек срок действия токена */
export const TokenExpired  = 70002;

/** Ошибка доступа для данного токена */
export const NoAccessToken = 70003;

/**
 * Формат токена в виде JSON при получении с сервера
 * @see \Look\Token\Container\TokenContainer.php {TokenContainer::jsonSerialize}
 */
export interface TokenJsonFormat
{
    /** Ключ токена */
    access_token : string;
    
    /** До какого времени токен считается действующим (0 - всегда) */
    expires_in   : number;
    
    /** Публичный ключ */
    public_key  ?: string;
    
    /** Приватный ключ */
    private_key ?: string;
}

/**
 * Базовый класс токена для работы с REST API движка LooK
 */
export class Token
{
    /**
     * Токен доступа
     */
    public accessToken: string;

    /**
     * Публичный ключ шифрования
     */
    public publicKey : JSEncryptRSAKey;

    /**
     * Приватный ключ шифрования
     */
    public privateKey : JSEncryptRSAKey;

    /**
     * Время, когда токен станет устаревшим
     */
    public expiresIn : number;

    /**
     * @param accessToken Ключ доступа
     * @param expiresIn   Время обновления токена
     * @param publicKey   Публичный ключ шифрования
     * @param privateKey  Приватный ключ шифрования
     */
    constructor(accessToken : string, expiresIn : number, publicKey ?: string, privateKey ?: string)
    {
        if(publicKey) {
            publicKey = hex2a(publicKey);
            this.publicKey = new JSEncryptRSAKey(publicKey);
        }

        if(privateKey) {
            privateKey = hex2a(privateKey);
            this.privateKey = new JSEncryptRSAKey(privateKey);
        }
        
        this.accessToken = accessToken;
        this.expiresIn = expiresIn;
    }

    /**
     * Проверяет, данный токен еще не устарел
     */
    public isExpired() : boolean
    {
        // бессмертный
        if(this.expiresIn === 0) {
            return false;
        }
        
        return getCurrectTimeStamp() > this.expiresIn;
    }

    /**
     * Проверяет, данный токен еще не устарел
     */
    public supportSendProtectedData() : boolean
    {
        return typeof this.publicKey !== 'undefined';
    }

    /**
     * Проверяет, поддерживает ли данный токен возможность получать зашифрованные данные
     */
    public supportGetProtectedData() : boolean
    {
        return typeof this.privateKey !== 'undefined';
    }

    /**
     * Возвращает объект ошибки токена связанной с неверным форматом
     * @param params Передаваемые параметры
     */
    public static getBadTokenError(params : any)
    {
        return new ErrorQueryObject(BadTokenCode, 'token is not valid', params);
    }

    /**
     * Возвращает объект ошибки токена связанной с устареванием токена
     * @param params Передаваемые параметры
     */
    public static getExpiredTokenError(params : any)
    {
        return new ErrorQueryObject(TokenExpired, 'expired token', params);
    }
    
    /**
     * Возвращает объект ошибки токена связанной с нехваткой полномочий представляемых данным токеном
     * @param params Передаваемые параметры
     */
    public static getAccessTokenError(params : any)
    {
        return  new ErrorQueryObject(NoAccessToken, 'token does not have an access signature for this request', params);
    }
}

export default Token;