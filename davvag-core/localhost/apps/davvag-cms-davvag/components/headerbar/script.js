WEBDOCK.component().register(function(exports){
    var bindData={
        userData: {
            email : "Loading",
            

        },
        appData : {
            title:""
        },
        appKey : "",
        name:"Davvag",
        icon:"",
        TopButtons:[{id:"home",caption:"Home",url:"#/profile",class:"nav-item",sub:[]},
                   {id:"about",caption:"About",url:"#/app/profileapp",class:"nav-item dropdown",sub:[{id:"home",caption:"Home",url:"#app",sub:[]},{id:"home",caption:"Home",url:"#app",sub:[]}]},
                   {id:"admin",caption:"Admininstration",url:"admin.php",class:"nav-item align-left",sub:[]}],
        searchbar:true
    };
    var vueData = {
        data:bindData,
        methods: {
            signout: signout,
            submitSearch: function(event){
                event.preventDefault();
            }
        }
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
        var menuhandler  = exports.getComponent("soss-data");
        var domaininfo  = exports.getComponent("domain-info");
        domaininfo.services.DomainInfo().then(function(r){
           if(r.success){
            //console.log(JSON.stringify(r));
            bindData.name=r.result.name;    
           }
        }).error(function(err){

        });
        var query=[{storename:"d_cms_buttons_v1",search:"BType:Top"}];
        var tmpmenu=[];
        bindData.TopButtons=[];
        menuhandler.services.q(query)
                    .then(function(r){
                        console.log(JSON.stringify(r));
                        if(r.success){
                            //bindData.TopButtons=r.result.d_cms_buttons_v1;
                            //tmpmenu=r.result.d_cms_buttons_v1;
                            r.result.d_cms_buttons_v1.sort((a,b) => (a.id > b.id) ? 1 : ((b.id > a.id) ? -1 : 0)); 
                            r.result.d_cms_buttons_v1.forEach(element => {
                                element.sub=getSubMenu(element.id);             
                                bindData.TopButtons.push(element);
                            });
                            
                        }
                    })
                    .error(function(error){
                        console.log(error.responseJSON);
        });
        
        //r.result.d_cms_buttons_v1
        
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
        console.log(JSON.stringify(bindData.TopButtons));
        new Vue(vueData);
    }

    function getSubMenu(id){
        var query=[{storename:"d_cms_buttons_v1",search:"id:"+id}];
        //var tmpmenu=[];
        //bindData.TopButtons=[];
        var menuhandler  = exports.getComponent("soss-data");
        menuhandler.services.q(query)
                    .then(function(r){
                        //console.log(JSON.stringify(r));
                        if(r.success){
                            //bindData.TopButtons=r.result.d_cms_buttons_v1;
                            return r.result.d_cms_buttons_v1;
                        }else{
                            return []
                        }
                    })
                    .error(function(error){
                       return [];
        });

        return [];
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
