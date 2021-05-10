WEBDOCK.component().register(function(exports){
    var scope,Rini;
    
    function loadkeyword(category, skip, take){
        var handler = exports.getComponent("broadcaster-handler");

        handler.services.allBroadcast().then(function(result){
            scope.items = result.result;
        }).error(function(){
            
        });
    }

    var vueData = {
        methods:{navigate: function(id){
            //handler = exports.getShellComponent("soss-routes");
            Rini.appNavigate(id ? "../broadcast?id=" + id : "../broadcast");
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
