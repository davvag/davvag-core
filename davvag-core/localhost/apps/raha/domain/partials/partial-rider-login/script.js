WEBDOCK.component().register(function(exports){

    var bindData =  {
            userName :"",
            password:""
    };

    var services = {}

    var routeData;

    var vueData =  {
        data : bindData,
        onReady: function(app){
        },
        methods:{
            login: function () {
                    //this.isLogin=true;
                    var self = this;
                    var handler = exports.getComponent("login-handler");
                    handler.services.login({email: this.userName,password:this.password,domain:window.location.hostname}).then(function(result){
                        if (result.result)
                            result = result.result;
                            
                        var passhash = CryptoJS.MD5(result.email);
                        self.profileimage = "https://www.gravatar.com/avatar/" + passhash+"?s=200&r=pg&d=mm";
                        self.userid=result.userid;
                        self.email=result.email;
                        self.name=result.email;
                        self.isLogin=true;
                        self.password="";
                        localStorage.loginData = JSON.stringify(result);
                        console.log(result); 
                        console.log("result"); 
                        
                        location.href="#/riderorders"; 


                    }).error(function(){
                        this.isLogin=false;
                        self.password="";
                        toastr.error('email and password is incorrect.', 'Security!');
                    });
                }
        }
    }

    exports.vue = vueData;
    exports.onReady = function(){
    }
});
