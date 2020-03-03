<?php
if ($_SERVER["REQUEST_METHOD"] === "POST"){
    $latlng = $_POST["latlng"];
    setcookie("Location", $latlng, time() + (86400 * 30), "/"); // 86400 = 1 day
    $page = str_replace("index.php","",$_SERVER['PHP_SELF']);
    header("Location: $page");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <link rel="icon" type="image/png" href="assets/raha/img/favicon.png" />
        <title>raha.lk - Please pick your location to proceeed</title>
        <style>
            
            body, html {
                height: 100%;
                width: 100%;
                font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
                background-color:black;
                color:white;
            }

            div#idHeader{
                background:#112241;
                color:white;
                
            }

            div#idContainer{
                position:fixed;
                left:0px;
                top:0px;
                width: 100%;
                height: 100%;
            }

            div#myLunchMap{
                position:absolute;
                width: 100%;
                height: 100%;
            }

            div#idBottomStrip{
                width: 100%;
                height: 50px;
            }

            .fabButton{
                position: fixed;
                left: 100%;
                top: 100%;
                margin-left: -100px;
                z-index: 99;
                height: 60px;
                width: 60px;
                background-color: #112241;
                border-radius: 75px;
                -webkit-box-shadow: 2px 2px 38px -13px rgba(0,0,0,1);
                -moz-box-shadow: 2px 2px 38px -13px rgba(0,0,0,1);
                color:white;
                box-shadow: 2px 2px 38px -13px rgba(0,0,0,1);
                border:none;
                background-position: center; 
                background-repeat: no-repeat;
                cursor:pointer;
                visibility:hidden;
            }

            p#idUserMessage{
                font-size:15.5px;
                padding:10px;
                margin:0px;
                text-align: center;
            }

        </style>


        <script type="text/javascript">
            function submitForm(){
                // alert ($("#idLatLng").val());
                // return false;
            }
        </script>

        <script type="text/javascript">
            var map,marker;
            var markerOldPosition,markerNewPosition;
            var defaultLatLng = {
                lat:6.879618672440538,
                lng: 79.85974331143188
            }

            function upadteLocationInMap(){
                var lat = defaultLatLng.lat;
                var lng = defaultLatLng.lng;
                $("#idLatLng").val(JSON.stringify(defaultLatLng));

                if (!marker){
                    marker = new google.maps.Marker({
                        position: {lat: lat, lng: lng},
                        map: map,
                        title: 'You are Here',
                        draggable:true
                    });

                    google.maps.event.addListener(marker, 'dragstart', function(evt) 
                    {
                        markerOldPosition = this.getPosition();
                        
                    });

                    google.maps.event.addListener(marker, 'dragend', function(evt) 
                    {
                        markerNewPosition = this.getPosition();
                        defaultLatLng.lat = evt.latLng.lat();
                        defaultLatLng.lng = evt.latLng.lng();
                        $("#idLatLng").val(JSON.stringify(defaultLatLng));
                    });
                }else {
                    marker.setPosition(new google.maps.LatLng(lat, lng));
                }

                map.setZoom(17);
                map.panTo(marker.position);
            }

            window.googlemap_callback = function(){
                map = new google.maps.Map(document.getElementById('myLunchMap'), {
                    center: {lat: defaultLatLng.lat, lng: defaultLatLng.lng},
                    zoom: 17
                });

                getCurrentLocation();
            }

            function getCurrentLocation(){

                if (navigator.geolocation) {
                    var watchId = navigator.geolocation.watchPosition(function(position){
                        if (position.coords){
                            defaultLatLng.lat = position.coords.latitude;
                            defaultLatLng.lng = position.coords.longitude;
                        }
                        upadteLocationInMap();
                        navigator.geolocation.clearWatch(watchId);
                        document.getElementById("idButtonProceed").style.visibility = "visible"; 
                        document.getElementById("idButtonLocation").style.visibility = "visible"; 
                        document.getElementById("idButtonRefresh").style.visibility = "hidden"; 
                    }, function(err){
                        document.getElementById("idButtonProceed").style.visibility = "hidden"; 
                        document.getElementById("idButtonLocation").style.visibility = "hidden"; 
                        document.getElementById("idButtonRefresh").style.visibility = "visible"; 
                        document.getElementById("idGeolocationEnable").style.visibility = "visible"; 
                        document.getElementById("myLunchMap").style.visibility = "hidden"; 
                        document.getElementById("idUserMessage").innerHTML = "";
                        
                        console.log(err);
                        upadteLocationInMap();    
                    },{timeout: 10000, enableHighAccuracy: false});
                } else {
                    document.getElementById("idUserMessage").innerHTML = "Geolocation is not supported in your browser. Please upgrade your browser to a newer version";
                    //upadteLocationInMap();
                }
            }

        </script>
    </head>
    <body>
        <div id="idContainer">
            <div id="idHeader" >
                <img src="assets/raha/img/cart.png">
                <p id="idUserMessage">Please pick your location to show us the best food offers for you!!!</p>
            </div>
            <div id="myLunchMap"></div>
            <div id="idGeolocationEnable" style="visibility: hidden; position: absolute; top: 50%; left: 50%; transform: translateX(-50%) translateY(-50%);text-align: center;">
                <h2>
                    Please allow geolocation for raha.lk and hit refresh
                </h2>
            </div>
            <div id="idBottomStrip">
                <form onsubmit="return submitForm()" method="post">
                    <input type="hidden" name="latlng" id="idLatLng">
                    <input id ="idButtonLocation" type="button" class="fabButton" style="margin-top: -150px;background-image:url(assets/raha/img/location.png);" onclick="getCurrentLocation()"/>
                    <button id ="idButtonProceed" class="fabButton" style="margin-top: -80px;background-image:url(assets/raha/img/tick.png);"></button>
                    <input id ="idButtonRefresh" class="fabButton" style="margin-top: -80px;background-image:url(assets/raha/img/refresh.png);" onclick="location.reload()"/>
                </form>
                
            </div>
        </div>
        <script type="text/javascript" src="//maps.googleapis.com/maps/api/js?key=<?php echo MAPS_APIKEY; ?>&callback=googlemap_callback"></script>
        <script src="lib/jquery.js"></script>
    </body>
</html>