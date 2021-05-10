WEBDOCK.component().register(function(exports){
    var scope;

    function loadProducts(){
        var handler = exports.getComponent("product-handler");

        handler.transformers.allProducts().then(function(result){
            for (var i=0;i<result.result.length;i++)
            {
                var p = result.result[i];
                vueData.data.allProducts[p.itemid] = p;
            }
        }).error(function(){
            
        });
    }

    function loadGrns(category, skip, take){
        
        var promiseObj;
        var handler = exports.getComponent("inventory-handler");

        handler.services.nextDayOrders().then(function(result){
            for (var i=0;i<result.result.length;i++){
                var item = result.result[i];
                var total =0;
                var uom;
                
                for (var j=0;j<item.details.length;j++){
                    total += (item.details[j].qty * item.details[j].unitprice);
                    
                    if (!uom)
                        uom = item.details[j].uom;
                }
                
                item.total = uom ? uom + " " + total : total;
                scope.items.push(item);
            }
            
        }).error(function(){
            
        });
        
        
    }

    var vueData = {
        methods:{
            navigatePage: function(){

            }
        },
        data :{
            allProducts:{},
            items : []
        },
        onReady: function(s){
            scope = s;
            loadProducts();
            loadGrns(undefined,0,100);
            $("body").css("background-image","none");
        }
    }    

    exports.vue = vueData;
    exports.onReady = function(){
    }
});
