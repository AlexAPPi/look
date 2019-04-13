/**
 * Объект ошибки
 */
export class ErrorObject extends Error
{
    public code : number;
    public msg  : string;

    constructor(code: number, msg: string)
    {
        super(msg);
        this.code = code;
        this.msg  = msg;
    }
}