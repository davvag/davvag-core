WEBDOCK.component().register(function(exports){
    var scope,Rini;
    
    function allloadkeyword(category, skip, take){
        var handler = exports.getComponent("davvag-order-handler");

        handler.services.allPendingOrders().then(function(result){
            scope.items = result.result;
        }).error(function(){
            
        });
    }
    function reject(e){
        var handler = exports.getComponent("davvag-order-handler");

            handler.services.RejectOrder(e).then(function(result){
                //scope.items = result.result;
                if(result.success){
                    filteredItems = scope.items.filter(function(item) {
                        if(item.invoiceNo!==result.result.invoiceNo)
                            return item;
                    });
                    scope.items=filteredItems==null?[]:filteredItems;
                }else{

                }
                //console.log(JSON.stringify(result));
            }).error(function(){
                
            });
    }

    var vueData = {
        methods:{
            navigate: function(e){
            //handler = exports.getShellComponent("soss-routes");
            //Rini.appNavigate(id ? "../broadcast?id=" + id : "../broadcast");
            var handler = exports.getComponent("davvag-order-handler");

            handler.services.AcceptOrder(e).then(function(result){
                //scope.items = result.result;
                if(result.success){
                    filteredItems = scope.items.filter(function(item) {
                        if(item.invoiceNo!==result.result.invoiceNo)
                            return item;
                    });
                    scope.items=filteredItems==null?[]:filteredItems;
                }else{

                }
                //console.log(JSON.stringify(result));
            }).error(function(){
                
            });
        },reject:reject},
        data :{
            items : []
        },
        onReady: function(s){
            scope = s;
            allloadkeyword(undefined,0,100);
            Rini = exports.getShellComponent("soss-routes");
            routeData = Rini.getInputData();
            if(routeData){
                
            }else{
                
            }

        }
    }    

    exports.vue = vueData;
    exports.onReady = function(element){
    }
});
