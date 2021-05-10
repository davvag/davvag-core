WEBDOCK.component().register(function(exports){
    
    function createVue(element){

        prodCategories=new Vue({
        el: '#idProdCategories',
        data:{categories:[], activeItem: ""},
        methods: {
            load: function (items) {
                var self = this;

                var handler = exports.getComponent("product-handler");

                var pInstance = exports.getShellComponent("soss-routes");
                var routeData = pInstance.getInputData();
                if (routeData)
                if (routeData.cat)
                    self.activeItem = routeData.cat;

                handler.transformers.allCategories()
                .then(function(result){
                    for (var i=0;i<result.result.length;i++){
                        if (i==0 && !self.activeItem)
                            self.activeItem = result.result[0];
                        self.categories.push(result.result[i]);
                    }
                })
                .error(function(){
            
                });
            },
            navigatePage: function(name){
                this.activeItem = name;
                window.location ="#/home?cat=" + name;
            }
        }
        });

        prodCategories.load();   
    }
    
    exports.onReady = createVue;
    exports.updateLocation = function(pos){
        defaultLatLng = pos;
    }
});
