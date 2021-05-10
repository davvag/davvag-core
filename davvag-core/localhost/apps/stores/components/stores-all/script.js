WEBDOCK.component().register(function(exports){
    var scope;

    function loadStores(skip, take){
        var promiseObj;
        var handler = exports.getComponent("store-handler");

        if (!promiseObj)
            promiseObj = handler.transformers.allStores();

        promiseObj.then(function(result){
            scope.items = result.result;
        }).error(function(){
            
        });
    }
    

    var vueData = {
        methods:{
            deleteStore: function(cat){
                var handler = exports.getComponent("store-handler");
                handler.transformers.deleteStore(cat)
                .then(function(result){
                    for (var i=0;i<scope.items.length;i++)
                        if (scope.items[i].id == cat.id){
                            scope.items.splice(i,1);
                            break;
                        }
                })
                .error(function(){
            
                });
            },
            navigate: function(id){
                handler = exports.getShellComponent("soss-routes");
                handler.appNavigate(id ? "/store?storeid=" + id : "/store");
            }
        },
        data :{
            items : []
        },
        onReady: function(s){
            scope = s;
            loadStores(0,100);
        }
    }    

    exports.vue = vueData;
    exports.onReady = function(element){
    }
});
