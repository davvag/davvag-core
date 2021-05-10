WEBDOCK.component().register(function(exports){
    var scope;
    var validatorInstance;
    var validator;

    var vueData =  {
        methods:{
            navigatePage: function(){

            },
            saveUser:function(){
                 scope.submitErrors = validator.validate(); 

                 if (!scope.submitErrors){
                    var su = scope.user;
                    if (su.password !== su.confirmpassword) {
                        scope.submitErrors = ["Passwords should match"]
                        return;
                    }
                    var newUser = {"name":su.name,"email":su.email,"userid":su.email,"password":su.password, "confirmpassword":su.confirmpassword}

                    newUser.userid = newUser.userid.replace(" ","_");

                    var handler = exports.getComponent ("login-handler");
                    handler.transformers.createUser(newUser).then(function(){
                        location.href="#/regsuccess"
                    }).error(function(err){
                        var message = err.responseJSON.message;
                        toastr.error(message);
                    });
                 } else {
                      
                 }

            }
        },
        data :{
            items : [],
            user:{"name":"","email":"","userid":"","password":"", "confirmpassword":""},
            submitErrors: undefined
        },
        onReady: function(s){
            scope = s;
            validatorInstance = exports.getComponent ("soss-validator");

            validator = validatorInstance.newValidator (scope);
            validator.map ("user.name",true, "Name field cannot be empty");
            validator.map ("user.email",true, "Email field cannot be empty");
            // validator.map ("user.userid",true, "UserId field cannot be empty");
            validator.map ("user.password",true, "Password field cannot be empty");
            validator.map ("user.confirmpassword",true, "Confirm password field cannot be empty");
        }
    } 

    exports.vue = vueData;
    exports.onReady = function(){
    }
});
