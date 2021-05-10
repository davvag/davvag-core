
WEBDOCK.component().register(function(exports){
    var scope;
    var validator_login;
    var validator_signup;
    var validator_profile;

    var bindData = {
        profile: localStorage.profile ? JSON.parse(localStorage.profile) : {address:{gpspoint:"", city:""},address2:{},address3:{}},
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


    function displayPartial(){
        if (bindData.isLoggedIn){
            bindData.partialToShow = 2; //profile
        }else {
            if (bindData.canShowSignUp){
                bindData.partialToShow = 1;
            }else {
                bindData.partialToShow = 0;
            }
        }
    }

    displayPartial();

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


    function createProfileLogic (cb){
        scope.isBusy = true;
        exports.getAppComponent("profileapp","profile", function(handler){
            
            handler.services.Save(scope.profile).then(function(data){
                scope.profile.id = data.result.result.generatedId;
                localStorage.setItem("profile",JSON.stringify(scope.profile));
                scope.isBusy = false;
                cb();
            }).error(function(){
                scope.isBusy = false;
            });

        });
    }

    function setCookie(cname, cvalue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays*24*60*60*1000));
        var expires = "expires="+ d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }

    function loginLogic (loginData, validate, cb){
        var self = this;
        if (validate){
            scope.submitErrors = [];
            scope.submitErrors = validator_login.validate(); 
        }else {
            scope.submitErrors = undefined;
        }

        if (!scope.submitErrors){
            var handler = exports.getComponent("login-handler");
            scope.isBusy = true;
            handler.services.login({email: loginData.email,password:loginData.password,domain:window.location.hostname}).then(function(result){
                scope.isBusy = false;
                if (result.result)
                    result = result.result;
                    setCookie("authData", JSON.stringify(result),1)

                    if (!result.error){
                    // var passhash = CryptoJS.MD5(result.email);
                    // self.profileimage = "https://www.gravatar.com/avatar/" + passhash+"?s=200&r=pg&d=mm";
                    bindData.loginData = result;
                    localStorage.loginData = JSON.stringify(result);
                    bindData.isLoggedIn = true;
                    
                    if (!cb)
                        displayPartial();
                    
                    if (result.profile){
                        scope.profile = result.profile;
                        localStorage.setItem("profile",JSON.stringify(scope.profile));
                    }
                    
                    
                    if(cb)
                        cb();
    
                    WEBDOCK.callRest("http://en.gravatar.com/"+self.email+".json")
                        .success(function(result){
                            self.profileurl=result.thumbnailUrl;
                        })
                    .error(function(){
                        console.log("No Profile pic");
                    }); 
                }else {
                    toastr.error('email and password is incorrect.', 'Security!');    
                }
    
            }).error (function(result){
                scope.isBusy = false;
            });;
        }
    };

    function submitLogin (){
        loginLogic({email: bindData.loginForm.email,password:bindData.loginForm.password}, true);
    }

    function submitSignup(){
        scope.submitErrors = [];
        scope.submitErrors = validator_signup.validate(); 

        if (!scope.submitErrors)
        {
           var su = scope.signupForm;

            var newUser = {"name":su.name,"email":su.email,"userid":su.email,"password":su.password, "confirmpassword":su.password}
            scope.profile.email = su.email;
            scope.profile.name = su.name;
            scope.profile.contactno = su.contactno;
            scope.profile.remarks = su.remarks;

            newUser.userid = newUser.userid.replace(" ","_");

           var handler = exports.getComponent ("login-handler");
           scope.isBusy = true;
           handler.transformers.createUser(newUser).then(function(){
                scope.isBusy = false;
                loginLogic({email: su.email,password:su.password}, false, function(){
                    scope.profile.userid = scope.loginData.userid;
                    localStorage.setItem("profile", JSON.stringify(scope.profile));
                    
                    createProfileLogic(function(){
                        if(localStorage.items)
                            scope.profile.items=JSON.parse(localStorage.items);
                        completePayment();
                    });                
                });
           }).error(function(err){
               scope.isBusy = false;
               var message = err.responseJSON.message;
               toastr.error(message);
           });
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
        var validatorInstance = exports.getComponent ("soss-validator");

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

    function upadteLocationInMap(lat,lng){
        if (exports.onInitMap) //google maps loaded first
            exports.onInitMap(lat,lng);
        else //geolocation loaded first
            exports.latLng = {lat:lat ,lng:lng};
    }

    var config = exports.getComponent("config").getSettings();
    var defaultLatLng = {
        lat:config.location.lat,
        lng: config.location.lng
    } 


    function showSignUp(){
        scope.canShowSignUp = true;
        displayPartial();
    }

    function showLogin(){
        scope.canShowSignUp = false;
        displayPartial();
    }

    var vueData =  {
       data : bindData,
       onReady: function(s){
           scope = s;
           var self = this;
           loadValidator();

       },
       methods:{
            submitLogin:submitLogin,
            showSignUp: showSignUp,
            showLogin: showLogin,
            submitSignup: submitSignup,
            submitPurchase: submitPurchase
        }
    }


    // function initializeGoogleMaps(){
    //     if (!window.googleMapsLoaded){
    //         var head= document.getElementsByTagName('head')[0];
    //         var script= document.createElement('script');
    //         script.type= 'text/javascript';
    //         script.src= window.location.protocol + "//maps.googleapis.com/maps/api/js?key=AIzaSyBL3DJiw5bsubnv67q-mNfect1uHWjRiRE&callback=partial_cart_checkout_gmap_callback";
    //         head.appendChild(script);
    //    }else{
    //         partial_cart_checkout_gmap_callback();
    //    }

    //    getMyLocation();
    // }

    // function getMyLocation(){
    //     var lat = defaultLatLng.lat,lng = defaultLatLng.lng;
        
    //     if (navigator.geolocation) {
            
    //         var watchId = navigator.geolocation.watchPosition(function(position){
    //             if (position.coords){
    //                 lat = position.coords.latitude;
    //                 lng = position.coords.longitude;
    //                 defaultLatLng.lat = lat;
    //                 defaultLatLng.lng = lng;
    //             }

    //             scope.profile.address.gpspoint = lat + ","  + lng;
                
    //             upadteLocationInMap(lat,lng);
    //             navigator.geolocation.clearWatch(watchId);
    //         }, function(err){
    //             console.log(err);
    //             scope.profile.address.gpspoint = lat + ","  + lng;
    //             upadteLocationInMap(lat,lng);    
    //         },{timeout: 10000, enableHighAccuracy: false});
    //     } else {
    //         scope.profile.address.gpspoint = lat + ","  + lng;
    //         upadteLocationInMap(lat,lng);
    //     }
    // }


    // function updateMarkerPos(marker){
    //     defaultLatLng.lat = marker.lat();
    //     defaultLatLng.lng = marker.lng();
    //     bindData.profile.address.gpspoint = marker.lat() + ","  + marker.lng();
    // }

    exports.vue = vueData;
    // exports.updateMarkerPos = updateMarkerPos;
    exports.onReady = function(){

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