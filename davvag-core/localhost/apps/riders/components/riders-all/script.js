WEBDOCK.component().register(function(exports){
    var scope;

    function loadRiders(skip, take){
        var promiseObj;
        var handler = exports.getComponent("rider-handler");

        if (!promiseObj)
            promiseObj = handler.transformers.allRiders();

        promiseObj.then(function(result){
            scope.items = result.result;
        }).error(function(){
            
        });
    }
    

    var vueData = {
        methods:{
            navigate: function(id){
                handler = exports.getShellComponent("soss-routes");
                handler.appNavigate(id ? "/rider?riderid=" + id : "/rider");
            }
        },
        data :{
            items : []
        },
        onReady: function(s){      
            scope = s;
            loadRiders(0,100);     
        }
    }    

    exports.vue = vueData;
    exports.onReady = function(element){
    }
});
