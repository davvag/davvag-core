WEBDOCK.component().register(function(exports){
    var scope;

    function loadGrns(category, skip, take){
        
        var promiseObj;
        exports.getAppComponent("inventory","inventory-handler", function(handler){
            handler.transformers.allGrn().then(function(result){
                scope.items = result.result;
            }).error(function(){
                
            });
        });
    }

    var vueData = {
        methods:{
            navigate: function(id){
                handler = exports.getShellComponent("soss-routes");
                handler.appNavigate(id ? "/grn?grnid=" + id : "/grn");
            }
        },
        data :{
            items : []
        },
        onReady: function(s){
            scope = s;
            loadGrns(undefined,0,100);
        }
    }    

    exports.vue = vueData;
    exports.onReady = function(element){        
    }
});
