WEBDOCK.component().register(function(exports){
    var scope;
    var bindData = {deliverToday:false};
    var vueData =  {
       data : bindData,
       onReady: function(){
            bindData.deliverToday = localStorage.deliverToday === 'true';
            localStorage.removeItem("deliverToday");
            var handler  = exports.getComponent("top-menu");
            handler.completeCheckout();
       }
    }
    

    exports.vue = vueData;
    exports.onReady = function(){
    }
});

