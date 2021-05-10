WEBDOCK.component().register(function(exports){
    var scope;
   
    var vueData =  {
        methods:{
            resetPassword:function(){
                if (scope.user.email == ""){
                    toastr.error('Please enter an email address', 'Password Reset Error');
                    return
                }
                var handler = exports.getComponent("login-handler");
                scope.isButtonDisabled = true;
                handler.services.getResetToken({email:scope.user.email})
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
            user:{"email":""},
        },
        onReady: function(s){
            scope = s;

        }
    } 

    exports.vue = vueData;
    exports.onReady = function(){
        
    }
});
