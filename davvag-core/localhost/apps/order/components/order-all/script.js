WEBDOCK.component().register(function(exports){
    var scope;
    var handler;
    
    function loadRiders(){
        exports.getAppComponent("riders", "rider-handler", function(handler){
            handler.transformers.allRiders().then(function(result){
                scope.riders = result.result;
            }).error(function(){
                
            });
        });

    }
    
    function loadOrders(category, skip, take, type){
       
        var promiseObj;
        scope.orderTab = type;

        switch (type){
            case "pending":
                promiseObj = handler.services.pendingOrders();
                break;
            case "dispatched":
                promiseObj = handler.services.dispatchedOrders();
                break;
            case "nextday":
                promiseObj = handler.services.nextDayOrders();
                break;
            case "closed":
                promiseObj = handler.services.closedOrders();
                break;
            case "cancled":
                promiseObj = handler.services.cancledOrders();
                break;
            default:
                promiseObj = handler.services.allOrders();
                break;
        }
        
        promiseObj.then(function(result){
            scope.items = [];
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
        
        //orderdate=STR_TO_DATE('2017-06-05
    }

    function updateOrder(item, status){
        item.status = status;
        var date = item.orderdate.split("-");
        item.orderdate = date[1] + "-" + date[2] + "-" + date[0];
        date = item.deliverydate.split("-");
        item.deliverydate = date[1] + "-" + date[2] + "-" + date[0];
        handler.services.updateOrder(item).then(function(result){
            
        }).error(function(){

        });
    }

    var vueData = {
        methods:{
            navigatePage: function(){

            },
            changeStatusDispatch: function(item){
                scope.currentItem = item;
                $('#modalRider').modal('show');
                
            },
            changeStatusClose: function(item){
                updateOrder(item,"closed");
            },
            selectRider: function(){
                if (scope.currentRider){
                    scope.currentItem.riderid = scope.currentRider.id;
                    scope.currentItem.ridername = scope.currentRider.username;
                    updateOrder(scope.currentItem,"dispatched");
                    scope.currentItem = undefined;
                    scope.currentRider = undefined;
                    $('#modalRider').modal('hide');
                }
            },
            loadOrders: function(type){
                loadOrders(undefined,0,100,type);
            }
        },
        data :{
            items : [],
            riders:[],
            test:"123",
            currentItem: undefined,
            currentRider: undefined,
            orderTab: "pending"
        },
        onReady: function(s){
            scope = s;
            exports.getAppComponent("inventory","inventory-handler", function(hnd){
                handler = hnd;
                loadOrders(undefined,0,100,"pending");
                loadRiders();
            });
        }
    }    

    exports.vue = vueData;
    exports.onReady = function(element){
    }
});
