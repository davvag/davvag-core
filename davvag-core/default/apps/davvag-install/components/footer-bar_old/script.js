WEBDOCK.component().register(function(exports){
   
    var vueData = {
        data:{
            userData: {
                email : "Loading",
                

            },facebook:"https://www.facebook.com/hopecenter.north",
            youtube:"https://www.youtube.com/channel/UCmr-voS45Jzn4yUE8wqCuzA",
            gpluse:undefined,
            twitter:undefined,
            instergram:undefined,
            contact:"+94777133777",
            appData : {
                title:""
            },
            appKey : "",
            name:"Davvag",
            icon:"http://citrusengine.com/wp-content/themes/citrus/images/logo/citrus-logo-2D.png",
            TopButtons:[{id:"home",caption:"Home",url:"#app",class:"nav-item",sub:[]},
                       {id:"about",caption:"About",url:"#about",class:"nav-item dropdown",sub:[{id:"home",caption:"Home",url:"#app",sub:[]},{id:"home",caption:"Home",url:"#app",sub:[]}]}]
        },
        methods: {
            signout: signout,
            submitSearch: function(event){
                event.preventDefault();
            }
        },
        
    }

    function signout(){
        var handler  = exports.getComponent("auth-handler");
        handler.services.logout()
        .then(function(result){
            window.location = window.location.href.split('#')[0];
        })
        .error(function(){
            alert ("error");
        });
    }

    exports.onReady = function(element){
        vueData.el = '#' + $(element).attr('id');
        var handler  = exports.getComponent("auth-handler");
        document.title="";
        console.log(getCookie("securityToken"));
        handler.services.Session(getCookie("securityToken"))
                .then(function(result){
                    if(result.result!=null){
                        vueData.data.userData=result.result;
                        console.log(result);
                    }else{
                       // signout();
                    }
                })
                .error(function(){
                    ///signout();
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
