WEBDOCK.component().register(function(exports){
    var scope;

    function loadUoms(skip, take){
        var handler = exports.getComponent("uom-handler");
        
        handler.transformers.allUom()
        .then(function(result){
            vueData.data.items = result.result;
        })
        .error(function(){
    
        });
    }

    var vueData =  {
        methods:{
            navigate: function(id){
                handler = exports.getShellComponent("soss-routes");
                handler.appNavigate(id ? "/uom?uomid=" + id : "/uom");
            }
        },
        data :{
            items : []
        },
        onReady: function(s){
            scope = s;
            loadUoms(0,100);
        }
    } 

    exports.vue = vueData;
    exports.onReady = function(element){
    }
});
