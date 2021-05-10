WEBDOCK.component().register(function(exports){
    var scope;
    var bindData = {
        grn : {
            items:[],
            grndate: getCurrentDate()
        },
        currentItem : {qty:0, unitprice:0, totalprice:0, product:undefined, datemfd: getCurrentDate(), dateexp: getCurrentDate(1)},
        products : [],
        myDate: null,
        submitErrors: undefined
    };

    var validator_item;
    var validator_current;

    function getCurrentDate(addDays){
        var cDate = new Date();
        var month = cDate.getMonth() + 1;
        var date = cDate.getDate();
        var year = cDate.getFullYear();
        if (addDays)
            date += addDays;

        return (month < 10 ? "0" + month : month) + "-" + (date < 10 ? "0" + date : date) + "-"+ year;
    }

    function loadValidator(){
        validator_item = validatorInstance.newValidator (scope);
        validator_item.map ("currentItem.qty",true, "You should enter a QTY");
        validator_item.map ("currentItem.qty","number", "QTY should be a number");
        validator_item.map ("currentItem.unitprice",true, "You should enter a Unit Price");
        validator_item.map ("currentItem.unitprice","number", "Unit Price should be a number");
        validator_item.map ("currentItem.product",true, "You should select a product");
    }

    function goToAllGrn(){
        location.href = "#/admin-grn";
    } 

    function loadGrn(){

    }

    function saveGrn(){
        scope.submitErrors = undefined;
        if (bindData.grn.items.length > 0){
            var handler = grnHandler;
            handler.services.newGrn(scope.grn).then(function(result){
                goToAllGrn();
            }).error(function(){

            });
        }else {
            scope.submitErrors = ["You should add atleast one item to GRN"];
        }

    }

    function addCurrentItem(){
        scope.submitErrors = validator_item.validate(); 
        if (!scope.submitErrors){
            scope.grn.items.push(scope.currentItem);
            clearCurrentItem();
        }
    }

    function clearCurrentItem(){
        scope.currentItem = {qty:0, unitprice:0, totalprice:0, product:undefined, datemfd: getCurrentDate(), dateexp: getCurrentDate(1)};
    }

    function selectCurrentItem(item){
        for (var i=0;i<scope.products.length;i++)
            if (scope.products[i].itemid === item.productid){
                item.product = scope.products[i]; 
                break;
            }

        scope.currentItem = item;
    }

    function calculateTotal(){
        var ci = scope.currentItem;
        var tp;
        if (ci.qty && ci.unitprice)
        if (typeof (ci.qty) === "number" && typeof (ci.unitprice) === "number")
            tp = ci.qty * ci.unitprice;

        ci.totalprice = tp ? tp :0;
    }
    var vueData = {
        methods: {
            saveGrn: saveGrn,
            addCurrentItem: addCurrentItem,
            selectCurrentItem: selectCurrentItem,
            calculateTotal: calculateTotal,
            clearCurrentItem: clearCurrentItem,
            navigateBack: function(){
                handler1 = exports.getShellComponent("soss-routes");
                handler1.appNavigate("..");
            }
        },
        watch: {
            "currentItem.qty": calculateTotal,
            "currentItem.unitprice": calculateTotal,
            "currentItem.product": function(){
                if (this.currentItem.product){
                    this.currentItem.productid = this.currentItem.product.itemid;
                    this.currentItem.productname = this.currentItem.product.name;
                }
            }
        },
        data : bindData,
        onBeforeRender: function(){
            //Vue.use(vPikaday);
        },
        onReady: function(s){
            scope = s;

            pInstance = exports.getShellComponent("soss-routes");
            routeData = pInstance.getInputData();
            validatorInstance = exports.getShellComponent ("soss-validator");
    
            exports.getAppComponent("productapp", "product", function(instance){
                productHandler = instance;
                productHandler.transformers.allProducts().then(function(result){
                    scope.products = result.result;
                }).error(function(){
                    
                });
            });
    
            exports.getAppComponent("inventory","inventory-handler", function(handler){
                grnHandler = handler;
            });
            
            loadValidator();
            
            if (routeData.grnid)
                loadGrn();
    
            $('#grnDatePicker').datepicker().on('changeDate', function(ev){
                scope.grn.grndate = $('#grnDatePicker').val(); 
            });
    
            $('#grnMfdDatePicker').datepicker().on('changeDate', function(ev){
                scope.currentItem.datemfd = $('#grnMfdDatePicker').val(); 
            });
    
            $('#grnExpDatePicker').datepicker().on('changeDate', function(ev){
                scope.currentItem.dateexp = $('#grnMfdDatePicker').val(); 
            });
        }
    }

    exports.vue = vueData;
    exports.onReady = function(element){

    }    
});

