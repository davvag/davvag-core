WEBDOCK.component().register(function(exports){
    var scope,Rini;
    
    function allloadkeyword(category, skip, take){
        var handler = exports.getComponent("schedules-handler");

        handler.services.allPendingSchedules().then(function(result){
            scope.items = result.result;
        }).error(function(){
            
        });
    }

    var vueData = {
        methods:{navigate: function(e){
            //handler = exports.getShellComponent("soss-routes");
            //Rini.appNavigate(id ? "../broadcast?id=" + id : "../broadcast");
            var handler = exports.getComponent("schedules-handler");

            handler.services.DeleteItem(e).then(function(result){
                //scope.items = result.result;
                filteredItems = scope.items.filter(function(item) {
                    if(item.id!==result.result.id)
                        return item;
                });
                scope.items=filteredItems==null?[]:filteredItems;
                //console.log(JSON.stringify(result));
            }).error(function(){
                
            });
        }},
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
