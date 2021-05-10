WEBDOCK.component().register(function(exports){
    var scope;

    function login(username, password){
        var handler = exports.getComponent("login-handler");
        handler.services.login({email: username,password:password,domain:window.location.hostname})
        .then(function(result){
            if (result.result)
                result = result.result;
            if (!result.error){
                localStorage.loginData = JSON.stringify(result);
                location.href="#/home";
            }else {
                toastr.error('Invalid Username or Password', 'Login error');    
            }

        }).error(function(){
            toastr.error('Error logging into raha.lk.', 'Login error');
        });
    }

    var vueData =  {
        methods:{
            navigatePage: function(){

            },
            saveUser:function(){
                 var handler = exports.getComponent ("login-handler");
                 handler.transformers.createUser(scope.user).then(function(){
                     login(scope.user.email, scope.user.password);
                 }).error(function(){
                    toastr.error('Error creating user.', 'User Creation Error');
                 });
            }
        },
        data :{
            items : [],
            user: {"name":"","email":"","userid":"","password":"", "confirmpassword":""},
            profile : {"contactno1":"", "email":"","name":""}
        },
        onReady: function(s){
            scope = s;
            $("#idMobileLogin").css("visibility","visible");
        }
    } 

    exports.vue = vueData;
    exports.onReady = function(){

        if (localStorage.loginData){
            $("#idHeader").css("visibility","visible");
            $("#idFooter").css("visibility","visible");
            location.href="#/home";
        }else{
            $("#idHeader").css("visibility","collapse");
            $("#idFooter").css("visibility","collapse");
        }
    }
});
