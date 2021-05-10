WEBDOCK.component().register(function(exports){
    var bindData={
        userData: {
            email : "Loading",
            

        },
        appData : {
            title:""
        },
        appKey : "",
        name:"Loading...",
        icon:"",
        MobileMenu:[],
        headerbars_TopButtons:[],
        searchbar:false,
        facebook:"",
        youtube:"",
        gpluse:undefined,
        twitter:undefined,
        instergram:undefined,
        headerbar_url:"#/home",
        headerdata:{}
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
        var query=[{storename:"d_cms_buttons_v1",search:"BType:Top"}];
        var tmpmenu=[];
        bindData.headerbars_TopButtons=[];
        menuhandler.services.q(query)
                    .then(function(r){
                        //console.log(JSON.stringify(r));
                        if(r.success){
                            //bindData.headerbars_TopButtons=r.result.d_cms_buttons_v1;
                            //tmpmenu=r.result.d_cms_buttons_v1;
                            r.result.d_cms_buttons_v1.sort((a,b) => (a.sortorder > b.sortorder) ? 1 : ((b.sortorder > a.sortorder) ? -1 : 0)); 
                            r.result.d_cms_buttons_v1.forEach(element => {
                                getSubMenu(element.id,function(s){
                                    element.sub=s;
                                    bindData.headerbars_TopButtons.push(element);
                                });             
                                
                            });
                            
                        }
                    })
                    .error(function(error){
                        console.log(error.responseJSON);
        });
        query=[{storename:"d_cms_buttons_v1",search:"BType:Mobile-Menu-Bar"}];

        menuhandler.services.q(query)
                    .then(function(r){
                        console.log(JSON.stringify(r));
                        if(r.success){
                            //bindData.headerbars_TopButtons=r.result.d_cms_buttons_v1;
                            //tmpmenu=r.result.d_cms_buttons_v1;
                            bindData.MobileMenu =r.result.d_cms_buttons_v1;
                            
                        }
                    })
                    .error(function(error){
                        console.log(error.responseJSON);
        });
    
        if(sessionStorage.blogheader){
            document.title=JSON.parse(sessionStorage.blogheader).name;
            bindData.name=JSON.parse(sessionStorage.blogheader).name;
            bindData.headerbar_url=JSON.parse(sessionStorage.blogheader).buttonuri;
            bindData.headerdata=JSON.parse(sessionStorage.blogheader);
        }else{
            var data={name:"cms-global"}
            menuhandler.services.Settings(data)
                    .then(function(r){
                        console.log(JSON.stringify(r));
                        if(r.success){
                            bindData.name= r.result.name;
                            bindData.headerbar_url=r.result.buttonuri;
                            document.title=r.result.name;
                            //JSON.parse(sessionStorage.blogheader)
                            bindData.headerdata=r.result;
                            if(r.result.icon){
                                bindData.icon=r.result.icon;
                            }
                            sessionStorage.blogheader=JSON.stringify(r.result)
                        }
                    })
                    .error(function(error){
                        //bindData.Articals=[];
                        console.log(error.responseJSON);
            });
        }
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
        console.log(JSON.stringify(bindData.headerbars_TopButtons));
        new Vue(vueData);
    }

    function getSubMenu(id,cb){
        var query=[{storename:"d_cms_buttons_v1",search:"parentButtonid:"+id}];
        //var tmpmenu=[];
        //bindData.headerbars_TopButtons=[];
        var menuhandler  = exports.getComponent("soss-data");
        menuhandler.services.q(query)
                    .then(function(r){
                        //console.log(JSON.stringify(r));
                        if(r.success){
                            //bindData.headerbars_TopButtons=r.result.d_cms_buttons_v1;
                            cb(r.result.d_cms_buttons_v1);
                            //return r.result.d_cms_buttons_v1;
                        }else{
                            cb([]);
                        }
                    })
                    .error(function(error){
                        cb([]);
        });

        //cb([]);
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
