
WEBDOCK.component().register(function(exports){
    var scope;
    var validator_login;
    var validator_signup;
    var validator_profile;

    var bindData = {
        profile: localStorage.profile ? JSON.parse(localStorage.profile) : null,
        submitErrors : [],
        isLoggedIn: localStorage.loginData ? true: false,
        loginData : localStorage.loginData ? JSON.parse(localStorage.loginData) : {},
        loginForm : {
            email :"",
            password :""
        },
        signupForm : {},
        canShowSignUp: false,
        partialToShow: 0,
        isBusy: false,
        isCompleted: false
    };


    function submitPurchase(){
        scope.submitErrors = [];
        scope.submitErrors = validator_profile.validate(); 
        if (!scope.submitErrors){

            bindData.profile.email = bindData.loginData.email;
            localStorage.setItem("profile", JSON.stringify(bindData.profile));

            if(localStorage.items)
                scope.profile.items=JSON.parse(localStorage.items);

            completePayment();
        }
    }





    function getCurrentDate(addDays){
        var cDate = new Date();
        var month = cDate.getMonth() + 1;
        var date = cDate.getDate();
        var year = cDate.getFullYear();
        if (addDays)
            date += addDays;

        return (month < 10 ? "0" + month : month) + "-" + (date < 10 ? "0" + date : date) + "-"+ year;
    }

    function completePayment(){
        scope.isBusy = true;
        cartHandler = exports.getComponent ("cart-handler");
        scope.profile.paymenttype = "cashondelivery";
        var deliverToday = localStorage.deliverToday === 'true';
        scope.profile.deliverydate = deliverToday ? getCurrentDate() : getCurrentDate(1);
        scope.profile.orderstatus = deliverToday ? "readytodispatch" : "nextdayorder";

        cartHandler.services.checkout(scope.profile).then(function(result){
            localStorage.removeItem("items");
            scope.isBusy = false;
            scope.isCompleted = true;
            location.href="#/paycomplete";
        }).error(function(){
            scope.isBusy = false;
        });

    }

    function loadValidator(){
        var validatorInstance = exports.getShellComponent ("soss-validator");

        validator_profile = validatorInstance.newValidator (scope);
        validator_profile.map ("profile.name",true, "Please enter your full name");
        validator_profile.map ("profile.contactno",true, "Please enter your contact number");
        validator_profile.map ("profile.contactno","numeric", "Phone number should only consist of numbers");
        validator_profile.map ("profile.contactno","minlength:10", "Phone number should consit of 10 numbers");

        validator_signup = validatorInstance.newValidator (scope);
        validator_signup.map ("signupForm.email",true, "Please enter your email");
        validator_signup.map ("signupForm.email","email", "The format of your email address is incorrect");
        validator_signup.map ("signupForm.password",true, "Please enter your password");
        validator_signup.map ("signupForm.name",true, "Please enter your full name");
        validator_signup.map ("signupForm.contactno",true, "Please enter your contact number");
        validator_signup.map ("signupForm.contactno","numeric", "Phone number should only consist of numbers");
        validator_signup.map ("signupForm.contactno","minlength:10", "Phone number should consit of 10 numbers");
        

        validator_login = validatorInstance.newValidator (scope);
        validator_login.map ("loginForm.email",true, "Please enter your email");
        validator_login.map ("loginForm.email","email", "The format of the email address is incorrect");
        validator_login.map ("loginForm.password",true, "Please enter your password");
        
    }


    //var config = exports.getComponent("config").getSettings();

    var vueData =  {
       data : bindData,
       onReady: function(s){
           scope = s;
           var self = this;
           if(bindData.profile==null){
               window.location="#/app/userapp?u=#/app/davvag-shop/checkout-complete";
           }else{
               console.log(bindData.profile);
           }
           loadValidator();

       },
       methods:{
            submitPurchase: submitPurchase
        }
    }


    exports.vue = vueData;
    // exports.updateMarkerPos = updateMarkerPos;
    exports.onReady = function(){
        if(bindData.profile==null){
            window.location="#/app/userapp?u=#/app/davvag-shop/checkout-complete";
        }else{
            console.log(bindData.profile);
        }
    }
    

});


// function partial_cart_checkout_gmap_callback() {
//     var map, marker;
//     var markerOldPosition,markerNewPosition;
//     //var bounds = new google.maps.LatLngBounds(sw, ne);
    
//     var plugObj = WEBDOCK.getPlugin("partial-cart-checkout");
//     window.googleMapsLoaded = true;
//     function onInitMap(lat,lng){
//         if (!map){
//             map = new google.maps.Map(document.getElementById('myLunchMap'), {
//                 center: {lat: lat, lng: lng},
//                 zoom: 17
//             });

//             marker = new google.maps.Marker({
//                 position: {lat: lat, lng: lng},
//                 map: map,
//                 title: 'You are Here',
//                 draggable:false
//             });

//             google.maps.event.addListener(marker, 'dragstart', function(evt) 
//             {
//                 markerOldPosition = this.getPosition();
                
//             });

//             google.maps.event.addListener(marker, 'dragend', function(evt) 
//             {
//                 markerNewPosition = this.getPosition();
//                 plugObj.updateMarkerPos(evt.latLng);
//             });

//             map.setZoom(17);
//             map.panTo(marker.position);
//         }else {
//             var latlng = new google.maps.LatLng(lat,lng);
//             marker.setPosition(latlng);
//             map.panTo(marker.position);
//         }
        
//     }

//     if (plugObj.latLng) //geolocation loaded first
//         onInitMap(plugObj.latLng.lat,plugObj.latLng.lng); 
//     else //google maps loaded first
//         plugObj.onInitMap = onInitMap; 
// }