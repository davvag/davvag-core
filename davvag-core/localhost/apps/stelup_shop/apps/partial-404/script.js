WEBDOCK.component().register(function(exports){
    bindData={header:{buttonuri:""}};
    var vueData =  {
        methods:{
        },
        data :bindData,
        onReady: function(s){
            var menuhandler  = exports.getComponent("soss-data");
            if(sessionStorage.blogheader){
    
                //document.title=JSON.parse(sessionStorage.blogheader).name;
                //bindData.url=r.result.buttonuri;
                bindData.header=JSON.parse(sessionStorage.blogheader);
            }else{
                var data={name:"cms-global"}
                menuhandler.services.Settings(data)
                        .then(function(r){
                            //console.log(JSON.stringify(r));
                            if(r.success){
                                bindData.header= r.result;
                                sessionStorage.blogheader=JSON.stringify(r.result)
                            }
                        })
                        .error(function(error){
                            //bindData.Articals=[];
                            console.log(error.responseJSON);
                });
            }
        }
    } 

    exports.vue = vueData;
    exports.onReady = function(){
       
    }
    
});
