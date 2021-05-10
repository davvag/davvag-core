WEBDOCK.component().register(function(exports){
    var scope;
   
    function getParameterByName(name, url) {
        if (!url) url = window.location.href;
        name = name.replace(/[\[\]]/g, "\\$&");
        var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
            results = regex.exec(url);
        if (!results) return null;
        if (!results[2]) return '';
        return decodeURIComponent(results[2].replace(/\+/g, " "));
    }

    var vueData =  {
        methods:{
            resetPassword:function(){
                if (scope.user.password == ""){
                    toastr.error('Please enter an email address', 'Password Reset Error');
                    return;
                }else {
                    if (scope.user.password != scope.user.confirmPassword){
                        toastr.error('Passwords should match', 'Password Reset Error');
                        return;
                    }
                }

                var handler = exports.getComponent("login-handler");
                scope.isButtonDisabled = true;
                let sendData = {"email":getParameterByName('email'), "token": getParameterByName('token'), "password": scope.user.password};
                handler.services.resetPassword(sendData)
                .then(function(result){

                    var result = result.result;
                    if (result.success == true){
                        toastr.success("Please check your email. We have sent you an email with a reset password link", 'Password reset successfull');
                        location.href = "#/mobilewelcome";
                    }else {
                        toastr.error(result.message, 'Password Reset Error');    
                    }
                    scope.isButtonDisabled = false;
                }).error(function(){
                    scope.isButtonDisabled = false;
                    toastr.error('Unknown error occured', 'Password Reset Error');
                });
            }
        },
        data :{
            isButtonDisabled:false,
            user:{"password":"", "confirmPassword":""},
        },
        onReady: function(s){
            scope = s;

        }
    } 

    exports.vue = vueData;
    exports.onReady = function(){
        
    }
});
