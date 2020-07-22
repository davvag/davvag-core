WEBDOCK.component().register(function(exports){
    var scope;

    function loadProductCategories(skip, take){
        exports.getAppComponent("productapp","product", function(handler){
            handler.transformers.allCategories()
            .then(function(result){
                scope.items = result.result;
            })
            .error(function(){
        
            });
        });
    }

    var vueData =  {
        methods:{
            navigate: function(id){
                handler = exports.getShellComponent("soss-routes");
                handler.appNavigate(id ? "/category?catid=" + id : "/category");
            },
            deleteCategory: function(cat){
                exports.getAppComponent("productapp","product", function(handler){
                    handler.transformers.deleteCategory(cat)
                    .then(function(result){
                        for (var i=0;i<scope.items.length;i++)
                            if (scope.items[i].id == cat.id){
                                scope.items.splice(i,1);
                                break;
                            }
                    })
                    .error(function(){
                
                    });
                });
            }
        },
        data :{
            items : []
        },
        onReady: function(s){
            scope = s;
            loadProductCategories(0,100);
        }
    } 

    exports.vue = vueData;
    exports.onReady = function(element){
    }
});
