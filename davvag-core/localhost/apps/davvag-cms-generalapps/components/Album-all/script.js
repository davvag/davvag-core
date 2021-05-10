WEBDOCK.component().register(function(exports){
    var scope;

    function loadUoms(skip, take){
        var handler = exports.getComponent("cms-gapp-handler");
        
        handler.transformers.allalbums()
        .then(function(result){
            //bindData.product.title=unescape(bindData.product.title);
            vueData.data.items = result.result;
        })
        .error(function(){
    
        });
    }

    var vueData =  {
        methods:{
            navigate: function(id){
                handler = exports.getShellComponent("soss-routes");
                handler.appNavigate(id ? "../album?id=" + id : "../album");
            }
        },
        data :{
            items : []
        },
        onReady: function(s){
            scope = s;
            loadUoms(0,100);
        },
        filter:{
            foramtdata:function(v){
                var newdata=unescape(v);
                console.log(newdata);
                return newdata;
            }
        }
    } 

    exports.vue = vueData;
    exports.onReady = function(element){
    }
});
