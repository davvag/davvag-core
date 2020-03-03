WEBDOCK.component().register(function(exports){
    var scope;

    var bindData = {
        profile: localStorage.profile ? JSON.parse(localStorage.profile) : {address:{gpspoint:"", city:""},address2:{},address3:{}},
        submitErrors : [],submitInfo : [],
        isLoggedIn: localStorage.loginData ? true: false,
        loginData : localStorage.loginData ? JSON.parse(localStorage.loginData) : {},
        loginForm : {
            email :"",
            password :""
        },
        signupForm : {},
        resetToken:"xxx",
        canShowSignUp: false,
        partialToShow: 0,
        isBusy: false,
        isCompleted: false
    };

    var vueData =  {
        methods:{
            navigate: function(id){
                handler = exports.getShellComponent("soss-routes");
                handler.appNavigate(id ? "/uom?uomid=" + id : "/uom");
            },
            resetpassword:function(){
                var handler = exports.getComponent("login-handler");
                bindData.submitErrors-[];
                bindData.submitInfo-[];
                scope.isBusy = true;
                handler.services.resetPassword({email: bindData.loginForm.email,token: bindData.resetToken,password: bindData.loginForm.password}).then(function(result){
                    console.log(result);
                    if(result.result.success){
                        bindData.submitInfo.push(result.result.message);
                        pInstance = exports.getShellComponent("soss-routes");
                        pInstance.appNavigate("../login");
                    }else{
                        bindData.submitErrors.push(result.result.message);
                    }
                }).error(function(result){
                    bindData.submitErrors.push(result.result.message);
                    scope.isBusy = false;
                });
            }
        },
        data :bindData,
        onReady: function(s){
            scope = s;
            //scope.isBusy=true;
            pInstance = exports.getShellComponent("soss-routes");
            routeData = pInstance.getInputData();
            if(routeData.email !=null && routeData.token!=null){
                bindData.loginForm.email=routeData.email;
                bindData.resetToken=routeData.token;

            }else{
                pInstance.appNavigate("../login");
            }
            //routeData = pInstance.getInputData();
            //handler = exports.getShellComponent("soss-routes");
            //handler.appNavigate(id ? "/uom?uomid=" + id : "/uom");
            if(bindData.isLoggedIn){
                if(sessionStorage.redirecturl){
                    scope.isBusy=false;
                    location.href=sessionStorage.redirecturl;
                }else{
                    pInstance.appNavigate("../profile");

                }
            }
        }
    } 

    exports.vue = vueData;
    exports.onReady = function(element){
    }



    function setCookie(cname, cvalue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays*24*60*60*1000));
        var expires = "expires="+ d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }
});
