WEBDOCK.component().register(function(exports){
    function getQueryVariable(sParam) {
        var sPageURL = decodeURIComponent(window.location.hash.substring(11)),
            sURLVariables = sPageURL.split('&'),
            sParameterName,
            i;

        for (i = 0; i < sURLVariables.length; i++) {
            sParameterName = sURLVariables[i].split('=');

            if (sParameterName[0] === sParam) {
                return sParameterName[1] === undefined ? true : sParameterName[1];
            }
        }
    };

    var scope;
    var bindData = {errorMessage:getQueryVariable("message")};
    var vueData =  {
       data : bindData,
       onReady: function(){

       }
    }
    

    exports.vue = vueData;
    exports.onReady = function(){
    }
});

