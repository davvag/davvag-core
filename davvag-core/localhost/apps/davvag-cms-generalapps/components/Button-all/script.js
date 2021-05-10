WEBDOCK.component().register(function(exports){
    var scope;

    function loadUoms(skip, take){
        var handler = exports.getComponent("cms-gapp-handler");
        
        handler.transformers.allButtons()
        .then(function(result){
            vueData.data.items = result.result;
        })
        .error(function(){
    
        });
    }
    function deleteitem(i){
        handler = exports.getComponent("cms-gapp-handler");
                handler.services.DeleteButton(i).then(function(r){
                    if(r.success){
                        filteredItems = scope.items.filter(function(item) {
                            if(item.id!==i.id)
                                return item;
                        });
                        scope.items=filteredItems==null?[]:filteredItems;
                    }
                }).error(function(e){

                });
    }

    var vueData =  {
        methods:{
            navigate: function(id){
                handler = exports.getShellComponent("soss-routes");
                handler.appNavigate(id ? "../buttons?id=" + id : "../buttons");
            },
            deleteitem:deleteitem
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
