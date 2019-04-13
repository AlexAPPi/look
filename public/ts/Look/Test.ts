
namespace Look.Crypt.RSA
{
    /**
     * Структура защищенный данных
     */
    export interface ProtectedStringData
    {
        /**
         * Данные, которые нужно зашифровать или расшифровать
         */
        data: string;
    }
}
namespace App.Entity
{
    /**
     * Тестовый комментарий
     */
    export interface User
    {
        name: string;
    }

    export interface UserList
    {
        items: App.Entity.User;
    }
}
namespace App.API
{
    /**
     * Класс авторизации
     */
    export class Auth
    {
        /**
         * Возвращает токен
         * @param login Логин
         * @param signature Хеш логина и пароля
         * @param expires Время жизни токена
         */
        public static getToken(login: string, signature: string, expires: number = 0) {}

        /**
         * Возвращает тунель токена
         * @param signature Хеш логина и пароля
         * @param expires Время жизни токена
         */
        public static getTunnelToken(signature: string, expires: number = 0) {}

        public static checkTunnel(accessToken: string, protectedData: Look.Crypt.RSA.ProtectedStringData) {}

        public static getProtectedMessage(accessToken: string) {}
    }


    export class User
    {
        public static a() {}

        public static e(token: string) {}

        public static b(token: string, data: Look.Crypt.RSA.ProtectedStringData) {}

        public static test(token: string, user: App.Entity.UserList) {}
    }

}
