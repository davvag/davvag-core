WEBDOCK.component().register(function(exports){
   
    var vueData = {
        data:{
            userData: {
                email : "Loading"
            },
            appData : {
                title:""
            },
            appKey : ""
        },
        methods: {
            signout: signout,
            submitSearch: function(event){
                event.preventDefault();
            }
        }
    }

    function signout(){
        var handler  = exports.getComponent("auth-handler");
        handler.services.Logout().then(function(result){
            if(result.result){  
                localStorage.clear();
                sessionStorage.clear();
                pInstance = exports.getShellComponent("soss-routes");
                window.location = window.location.href.split('#')[0];
            }else{
                alert ("error");
            }
        }).error(function(result){
            //$("#form-reset :input").prop("disabled", false);

            alert ("error");
        });
    }

    exports.onReady = function(element){
        vueData.el = '#' + $(element).attr('id');
        var handler  = exports.getComponent("auth-handler");
        console.log(getCookie("securityToken"));
        handler.services.Session(getCookie("securityToken"))
                .then(function(result){
                    if(result.result!=null){
                        vueData.data.userData=result.result;
                        console.log(result);
                    }else{
                        signout();
                    }
                })
                .error(function(){
                    signout();
                });
        
        new Vue(vueData);
    }

    function getCookie(cname) {
        var name = cname + "=";
        var decodedCookie = decodeURIComponent(document.cookie);
        var ca = decodedCookie.split(';');
        for(var i = 0; i <ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }

    exports.setDisplayData = function(appKey,appData) {
        vueData.data.appData = appData;
        vueData.data.appKey = appKey;
    }

});
