WEBDOCK.component().register(function(exports){

    var bindData={
        products:[]
    };
    var vueData =  {
        methods:{
        },
        data :bindData
        ,
        onReady: function(s){
            var menuhandler  = exports.getComponent("soss-data");
            var query=[{storename:"products",search:""}];
            menuhandler.services.q(query)
                        .then(function(r){
                            console.log(JSON.stringify(r));
                            if(r.success){
                                bindData.products= r.result.products;
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
