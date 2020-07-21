WEBDOCK.component().register(function(exports){
    var bindData ={
        SocailButtons:[]
    };
    var vueData = {
        data:bindData,
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
        
        //document.title="";
        //console.log(getCookie("securityToken"));
        new Vue(vueData);
        var menuhandler  = exports.getComponent("soss-data");
        var query=[{storename:"d_cms_buttons_v1",search:"BType:social-footer"}];
        var tmpmenu=[];
        bindData.TopButtons=[];
        menuhandler.services.q(query)
                    .then(function(r){
                        console.log(JSON.stringify(r));
                        if(r.success){
                            //bindData.TopButtons=r.result.d_cms_buttons_v1;
                            //tmpmenu=r.result.d_cms_buttons_v1;
                            bindData.SocailButtons=r.result.d_cms_buttons_v1;
                        }
                    })
                    .error(function(error){
                        console.log(error.responseJSON);
        });
        
       
    }

    

    exports.setDisplayData = function(appKey,appData) {
        vueData.data.appData = appData;
        vueData.data.appKey = appKey;
    }

});
