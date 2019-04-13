import {ErrorRequestObject} from './request';

/**
 * Исключение связанное с неправильным запросом
 */
export class BadRequestException extends ErrorRequestObject
{
    constructor(params : any)
    {
        super(404, 'API request not currect', params);
    }
}

/**
 * Исключение связанное с неправильной передачей названия класса или метода
 */
export class BadClassOrMethodException extends ErrorRequestObject
{
    constructor(params : any)
    {
        super(404, 'bad class or method', params);
    }
}