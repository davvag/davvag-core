WEBDOCK.component().register(function(exports){
    var scope;

    function loadInventory(category, skip, take){
        var handler = exports.getComponent("inventory-handler");

        handler.services.allInventory().then(function(result){
            scope.items = result.result;
        }).error(function(){
            
        });
    }

    var vueData = {
        methods:{},
        data :{
            items : []
        },
        onReady: function(s){
            scope = s;
            loadInventory(undefined,0,100);
        }
    }    

    exports.vue = vueData;
    exports.onReady = function(element){
    }
});
