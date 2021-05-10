WEBDOCK.component().register(function(exports){


    var scope;
    var bindData = {};
    var vueData =  {
       data : bindData,
       onReady: function(s){
           scope = s;
           scope.profile = JSON.parse(localStorage.profile);
            var cartHandler = exports.getComponent ("cart-handler");
            cartHandler.services.checkout(scope.profile).then(function(result){
                location.href="#/paycomplete";
                localStorage.removeItem("items");
            }).error(function(){
            });
       }
    }
    

    exports.vue = vueData;
    exports.onReady = function(){
    }
});

