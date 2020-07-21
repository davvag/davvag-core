WEBDOCK.component().register(function(exports){
   
    var vueData = {
        data:{
            appData:{
                title:"Home"
            },
            appKey : ""
        },
        methods: {}
    }

    exports.onReady = function(element){
        vueData.el = '#' + $(element).attr('id');
        new Vue(vueData);
    }

    exports.setDisplayData = function(appKey,appData) {
        vueData.data.appData = appData;
        vueData.data.appKey = appKey;
    }

});
