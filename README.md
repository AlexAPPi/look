import Request from './Look/API/Request';
import Session from './Look/API/Session';

var session = new Session('user', 'pass');

session.open(10)
.onError(function(this : Request, data : any) {

    // session open error
    ...
    
    this.repeat();
})
.onSuccess(function() {

    // session open
    ...
    
});
