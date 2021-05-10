WEBDOCK.component().register(function(exports){
    var scope;
    var handler;
       
    function loadOrders(category, skip, take){
       
        var promiseObj;

        var userObj = JSON.parse(localStorage.loginData);

        handler.services.riderOrders({riderid:userObj.email}).then(function(result){
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
            
            if (scope.items.length == 1)
                scope.items = [];

            var ci;
            for (var i=0;i<scope.items.length;i++)
            if (scope.items[i] === item){
                ci =i;
                break;
            }

            if (ci)
                scope.items.splice(ci,1);
        }).error(function(){

        });
    }

    var reasons= ["Prank order", "Customer was not available", "Customer rejected the food"]

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
                item.isBusy = true;
            },
            changeStatusCancel: function(item){
                scope.currentItem = item;
                $('#modalRiderOrders').modal('show');
            },
            selectRider: function(){
                if (scope.currentRider){
                    updateOrder(scope.currentItem,"dispatched");
                    scope.currentItem = undefined;
                    scope.currentRider = undefined;
                    $('#modalRider').modal('hide');
                }
            },
            selectReason: function(){
                $('#modalRiderOrders').modal('hide');
                updateOrder(scope.currentItem,"cancled");
                scope.currentItem.isBusy = true;
            },
            showInMaps: function(item){
                
            },
            refreshOrders: function(){
                loadOrders(undefined,0,100);
            }
        },
        data :{
            currentItem: undefined,
            items : [],
            test:"123",
            currentItem: undefined,
            currentRider: undefined,
            currentReason: undefined,
            reasons: reasons
        },
        onReady: function(s){
            scope = s;
            handler = exports.getComponent("inventory-handler");
            loadOrders(undefined,0,100);

            setInterval(function(){
                loadOrders(undefined,0,100);
            },10000);

           var confighandler = exports.getComponent("config");
           var settings = confighandler.getSettings();
           settings.home = "/riderorders";
           settings.notFound = "/riderorders";
           settings.partials["/"] = "partial-rider-orders";
           settings.partials["/rider-login"] = "partial-rider-orders";
           settings.partials["/home"] = "partial-rider-orders";
           exports.getShellComponent("soss-routes").set (settings);
        }
    }    

    exports.vue = vueData;
    exports.onReady = function(){
    }
});
