WEBDOCK.component().register(function(exports){
    var apploader, handler;
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
        TopButtons:[],
        searchbar:false,
        facebook:"",
        youtube:"",
        gpluse:undefined,
        twitter:undefined,
        instergram:undefined,
        url:"#/home",
        headerdata:{},
        Notify:[],        
        appTitle:"",
        appIcon:""
    };
    function completeResponce(d){
       if(d.notfy.id){
            handler.services.ClearNotiifcatiion({id:d.notfy.id.toString()})
            .then(function(result){
                if(result.success){
                    //vueData.data.Notify=result.result;
                    filteredItems = bindData.Notify.filter(function(item) {
                        if(item.id!==d.notfy.id)
                            return item;
                    });
                    bindData.Notify==null?[]:filteredItems;
                }
            })
            .error(function(){
                ///signout();
                //localStorage.clear();
            });
       }
        console.log(JSON.stringify(d));
    }

    var vueData = {
        data:bindData,
        methods: {
            signout: signout,
            submitSearch: function(event){
                event.preventDefault();
            },
            notification:function(){
                $(".notification-bar").show(100); 
            },downloadapp:function(appname,form,data,apptitle,m){
                //$('#decker1100').addClass("profile-content-show");
                bindData.appTitle=apptitle;
                console.log(data);
                data=JSON.parse(data);
                data.notfy=m;
                $('#notifyappwindow').modal('toggle');
                $(".notification-bar").hide(100); 
                apploader.downloadAPP(appname,form,"notifyappdock",function(d){
                    
                },function(e){
                    console.log(e);
                    bindData.loadingAppError=true;
                },completeResponce,data);
            },close: function(){
                //bindData.product=p;
                $('#notifyappwindow').modal('toggle');
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

    $(document).mouseup(function (e) { 
        if ($(e.target).closest(".notification-bar").length === 0) { 
            $(".notification-bar").hide(100); 
        } 
    }); 

    exports.onReady = function(element){
        vueData.el = '#' + $(element).attr('id');
        handler  = exports.getComponent("auth-handler");
        var menuhandler  = exports.getComponent("soss-data");
        var query=[{storename:"d_cms_buttons_v1",search:"BType:Top"}];
        var tmpmenu=[];
       
        $(".notification-bar").hide(); 
        exports.getAppComponent("davvag-tools","davvag-app-downloader", function(_uploader){
            apploader=_uploader;
            apploader.initialize();
        });
        bindData.TopButtons=[];
        menuhandler.services.q(query)
                    .then(function(r){
                        //console.log(JSON.stringify(r));
                        if(r.success){
                            //bindData.TopButtons=r.result.d_cms_buttons_v1;
                            //tmpmenu=r.result.d_cms_buttons_v1;
                            r.result.d_cms_buttons_v1.sort((a,b) => (a.sortorder > b.sortorder) ? 1 : ((b.sortorder > a.sortorder) ? -1 : 0)); 
                            r.result.d_cms_buttons_v1.forEach(element => {
                                getSubMenu(element.id,function(s){
                                    element.sub=s;
                                    bindData.TopButtons.push(element);
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
                            //bindData.TopButtons=r.result.d_cms_buttons_v1;
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
            bindData.url=JSON.parse(sessionStorage.blogheader).buttonuri;
            bindData.headerdata=JSON.parse(sessionStorage.blogheader);
        }else{
            var data={name:"cms-global"}
            menuhandler.services.Settings(data)
                    .then(function(r){
                        console.log(JSON.stringify(r));
                        if(r.success){
                            bindData.name= r.result.name;
                            bindData.url=r.result.buttonuri;
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
                    }else{
                        localStorage.clear();
                       // signout();
                    }
                })
                .error(function(){
                    ///signout();
                    localStorage.clear();
                });
        console.log(JSON.stringify(bindData.TopButtons));
        handler.services.Notification()
                .then(function(result){
                    if(result.result!=null){
                        vueData.data.Notify=result.result;
                    }
                })
                .error(function(){
                    ///signout();
                    //localStorage.clear();
                });
        console.log(JSON.stringify(bindData.TopButtons));
        new Vue(vueData);
    }

    function getSubMenu(id,cb){
        var query=[{storename:"d_cms_buttons_v1",search:"parentButtonid:"+id}];
        //var tmpmenu=[];
        //bindData.TopButtons=[];
        var menuhandler  = exports.getComponent("soss-data");
        menuhandler.services.q(query)
                    .then(function(r){
                        //console.log(JSON.stringify(r));
                        if(r.success){
                            //bindData.TopButtons=r.result.d_cms_buttons_v1;
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
