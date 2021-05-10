WEBDOCK.component().register(function(exports){

    var services = {
        topMenu : exports.getComponent("top-menu")
    }

    var bindData =  {
            message : "Works!!!",
            items: [],
            canShowSlider:true,
            gpspoint: "",
            isMapsLoaded : false,
            areItemsLoaded: false,
            itemCount:0
    };

    var items=[];
    if(localStorage.items){           
        items=JSON.parse(localStorage.items);
        for (var i=0;i<items.length;i++){
            var item = items[i];
            bindData.itemCount += item.qty;
        }
        services.topMenu.additems(undefined,bindData.itemCount);              
    }
    
    var routeData;

    var updateLocationCallback;
    

    var vueData =  {
        data : bindData,
        onReady: function(app){

            $("#idHeader").css("visibility","visible");
            $("#idFooter").css("visibility","visible");
            
            var pInstance = exports.getShellComponent("soss-routes");
            routeData = pInstance.getInputData();

            services.product = exports.getComponent("product-handler");

            var config = exports.getComponent("config").getSettings();
            var promiseObj;
            
            if (routeData)
            if (routeData.cat){
                promiseObj = services.product.transformers.allProducts({catid:routeData.cat});
                //promiseObj = services.product.services.allProducts({catid:routeData.cat, lat:config.location.lat, lng:config.location.lng});
                app.canShowSlider = false;
            }

            if (!promiseObj)
                promiseObj = services.product.transformers.allProducts({catid:routeData.cat});
                //promiseObj = services.product.services.allProducts();

            promiseObj.then(function(result){
                bindData.items = result.result;
                app.areItemsLoaded = true;
            }).error(function(){
                console.log(result);
                app.areItemsLoaded = true;
            });

            // $('.carousel').carousel({
            //     interval: 2000
            // });
        },
        methods:{
            additem:function(item,isOrder){
                items=[];
                if(localStorage.items){           
                    items=JSON.parse(localStorage.items);
                }
                x=0;
                for(i in items){
                    if(items[i].itemid===item.itemid){
                        items[i].qty++;
                        items[i].isOrder = isOrder;
                        localStorage.items=JSON.stringify(items);
                        bindData.itemCount =0;
                        for (var i=0;i<items.length;i++){
                            var it = items[i];
                            bindData.itemCount += it.qty;
                        }
                        services.topMenu.additems(items[i],bindData.itemCount);
                        return;
                    }
                    x++;
                }
                item.qty=1;
                item.isOrder = isOrder;
                items.push(item);
                localStorage.items=JSON.stringify(items);
                bindData.itemCount =0;
                for (var i=0;i<items.length;i++){
                    var it = items[i];
                    bindData.itemCount += it.qty;
                }
                services.topMenu.additems(item,bindData.itemCount);
            }
        }
    }


    exports.vue = vueData;
    exports.onReady = function(){
        $("#idHeader").css("visibility","visible");
        $("#idFooter").css("visibility","visible");
    }

});
