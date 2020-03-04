WEBDOCK.component().register(function(exports){
    var scope;

    var bindData = {
        profile: localStorage.profile ? JSON.parse(localStorage.profile) : {address:{gpspoint:"", city:""},address2:{},address3:{}},
        submitErrors : [],
        isLoggedIn: localStorage.loginData ? true: false,
        loginData : localStorage.loginData ? JSON.parse(localStorage.loginData) : {},
        loginForm : {
            email :"",
            password :""
        },
        signupForm : {},
        canShowSignUp: false,
        partialToShow: 0,
        isBusy: false,
        isCompleted: false
    };

    

    var vueData =  {
        methods:{
            
        },
        data :bindData,
        onReady: function(s){
            
            /*
            if(bindData.isLoggedIn){
                if(routeData.u){
                    scope.isBusy=false;
                    location.href=unescape(routeData.u);
                }else{
                    
                    pInstance.appNavigate("/profile");

                }
            }else{
                if(routeData.u){
                    sessionStorage.redirecturl=unescape(routeData.u);
                    pInstance.appNavigate("/login");
                }else{
                    pInstance.appNavigate("/login");
                }
            }*/
        }
    } 

    exports.vue = vueData;
    exports.onReady = function(element){
    }


});
