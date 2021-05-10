WEBDOCK.component().register(function(exports){
    var scope;
    var relativeLoc = window.location.protocol + "//" + window.location.host;

    function getCurrentDate(addDays){
        var cDate = new Date();
        var month = cDate.getMonth() + 1;
        var date = cDate.getDate();
        var year = cDate.getFullYear();
        if (addDays)
            date += addDays;

        return (month < 10 ? "0" + month : month) + "-" + (date < 10 ? "0" + date : date) + "-"+ year;
    }

    var vueData =  {
       data : {
           isBusy:false,
           paymentmode:"cashondelivery", 
           totalPrice:0, 
           receiptId:0,
           urls : {
               action: "http://pay.shopperz.lk",
               success: relativeLoc + "/#/paycomplete",
               error: relativeLoc + "/#/payerror"
           }
        },
       onReady: function(s){
           scope = s;
           scope.profile = JSON.parse(localStorage.profile);

            for(var i in scope.profile.items){
                scope.totalPrice += (scope.profile.items[i].qty*scope.profile.items[i].price);
            }

           console.log(scope.profile);
       },
       methods:{
           submit: function(){
               scope.isBusy = true;
               cartHandler = exports.getComponent ("cart-handler");
               scope.profile.paymenttype = scope.paymentmode;
               var deliverToday = localStorage.deliverToday === 'true';
               scope.profile.deliverydate = deliverToday ? getCurrentDate() : getCurrentDate(1);
               scope.profile.orderstatus = deliverToday ? "readytodispatch" : "nextdayorder";

               if (scope.profile.paymenttype == "cashondelivery"){
                    cartHandler.services.checkout(scope.profile).then(function(result){
                        location.href="#/paycomplete";
                        localStorage.removeItem("items");
                        scope.isBusy = false;
                    }).error(function(){
                        scope.isBusy = false;
                    });
               }else {
                   $('#idPaymentForm').submit();
               }

           }
       }
    }

    exports.vue = vueData;
    exports.onReady = function(){
    }
});

