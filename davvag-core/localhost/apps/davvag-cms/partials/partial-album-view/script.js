WEBDOCK.component().register(function(exports){

    var bindData={
        products:[]
    };
    var vueData =  {
        methods:{
            getURL:function(i){
                return "http://"+window.location.hostname+"/components/davvag-album/cms-album-handler/service/Album/?q="+i.id;
            }
        },
        data :bindData
        ,
        onReady: function(s){
            var menuhandler  = exports.getComponent("soss-data");
            var routId   = exports.getShellComponent("soss-routes");
            routeData = routId.getInputData();
            if(!routeData.id){
                var query=[{storename:"d_cms_album_v1",search:""}];
            }else{
                var query=[{storename:"d_cms_album_v1",search:"catid:"+routeData.id.toString()}];
            }
            menuhandler.services.q(query)
                        .then(function(r){
                            console.log(JSON.stringify(r));
                            if(r.success){
                                bindData.products= r.result.d_cms_album_v1;
                            }
                        })
                        .error(function(error){
                            bindData.products=[];
                            console.log(error.responseJSON);
            });
            
        },
        filters:{
            markeddown: function (value) {
                if (!value) return ''
                value = value.toString()
                return marked(unescape(value));
              },
              dateformate:function(v){
                  if(!v){
                      return ""
                  }else{
                    return moment(v, "MM-DD-YYYY hh:mm:ss").format('MMMM Do YYYY');
                  }
              }
        }
    } 



    exports.vue = vueData;
    exports.onReady = function(){
        
        

    }

    
});
