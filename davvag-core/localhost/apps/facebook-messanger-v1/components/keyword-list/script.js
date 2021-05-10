WEBDOCK.component().register(function(exports){
    var scope,Rini;
    
    function loadkeyword(category, skip, take){
        var handler = exports.getComponent("keyword-handler");

        handler.services.allKeywords().then(function(result){
            scope.items = result.result;
        }).error(function(){
            
        });
    }

    var vueData = {
        methods:{navigate: function(id){
            //handler = exports.getShellComponent("soss-routes");
            Rini.appNavigate(id ? "/keyword?id=" + id : "/keyword");
        },
        navigateBr: function(id){
            //handler = exports.getShellComponent("soss-routes");
            Rini.appNavigate(id ? "/broadcastlist?id=" + id : "/broadcastlist");
        }},
        data :{
            items : []
        },
        onReady: function(s){
            scope = s;
            loadkeyword(undefined,0,100);
            Rini = exports.getShellComponent("soss-routes");
            routeData = Rini.getInputData();
            if(routeData){
                
            }

        }
    }    

    exports.vue = vueData;
    exports.onReady = function(element){
    }
});
