WEBDOCK.component().register(function(exports){
    var scope;
   
    var vueData =  {
        methods:{
            navigatePage: function(){

            },
            login:function(){
                var handler = exports.getComponent("login-handler");
                handler.transformers.login(scope.user.email,scope.user.password,window.location.hostname)
                .then(function(result){
                    localStorage.loginData = JSON.stringify(result);


                    $("#idHeader").css("visibility","visible");
                    $("#idFooter").css("visibility","visible");
                    $("#idMobileWelcome").css("visibility","collapse");
                    location.href="#/home";
                }).error(function(){
                    toastr.error('Invalid username or password', 'Login error');
                });
            }
        },
        data :{
            items : [],
            user:{"email":"","password":""},
        },
        onReady: function(s){
            scope = s;
            if (!localStorage.loginData){
                $("#idMobileWelcome").css("visibility","visible");
            }          
        }
    } 

    exports.vue = vueData;
    exports.onReady = function(){
        if (localStorage.items)
	        localStorage.removeItem("items");

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
