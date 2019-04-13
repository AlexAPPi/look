//import {serialize} from './Look/Util';
import Session from './Look/Api/Session';
import { QueryMethod } from './Look/Use/Query';

//var a;
var session = new Session('user', 'pass');

session.open(10)
.onError(function(data : any) {
    console.log(data);
})
.onSuccess(function() {
    
    var value1 = 'hello test';
    var value2 = 'hello is 2';

    session.get('auth.checkTunnel', {data1: value1, data2: value2}, true, QueryMethod.POST)
    .onSuccess(function(data : any) {
        console.log(data);
    }).onError(function(data : any) {
        console.log(data);
    });

    session.get('auth.getProtectedMessage')
    .onSuccess((data : string) => {
        console.log(session.token.privateKey.decrypt(data));
    });

    setTimeout(function() {
        session.get('auth.checkTunnel', {data: value1}, true, QueryMethod.POST)
        .onSuccess(function(data : any) {
            console.log(data);
        }).onError(function(data : any) {
            console.log(data);
        });
    }, 11000);
});

/*
document.querySelector('button').onclick = function() {
    console.log(serialize(document.querySelector('form')));
    return false;
};
*/