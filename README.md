Look Session system, you can open session for make auth request

Your PHP API Handler (APP/API/Auth)

For start you need make Token type: (for example)

go make file: {APP_NAME}/{API_FOLDER}/Token/MyTokenType.php

    <?php

    namespace App\API\Token;

    use Look\API\Type\Token\SimpleToken;
    
    class MyTokenType extends SimpleToken
    {
        const necessaryPermits = ['simple']; // -> here premission list
    }

go make API Session handler class:

    <?php

    namespace App\API;
    
    use Token\MyTokenType;
    
    class Session
    
        public static function open(string $login, string $signature, int $expires = 0)
        {
            // get user id
            $userId = User::get($login, $signature);
            return MyTokenType::create($userId, $signature, $expires);
        }

Your Type Script hanler:

    import Request from './Look/API/Request';
    import Session from './Look/API/Session';

    var session = new Session('user', 'pass');

    session.open(10)
    .onError(function(this : Request, data : any) {

        // session open error
        ...
    
        // you can repeat query
        this.repeat();
    })
    .onSuccess(function() {

        // session open
        ...
    
    });
