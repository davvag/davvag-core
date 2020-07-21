WEBDOCK.component().register(function(exports){
   
    var vueData = {
        data:{apps:[]},
        methods: {
            navigateApp: function(appKey,value){
                var titleComponent = exports.getComponent("navigation-title");
                titleComponent.setDisplayData(appKey,value);
                location.href = "#/app/" + appKey;               
            }
        }
    }

    var isAppsLoaded = false;
    var appLoadedCallback;
    exports.onReady = function(element){
        vueData.el = '#' + $(element).attr('id');
        new Vue(vueData);

        WEBDOCK.callRest("components/object/apps?tags=showindock")
        .success(function(data){
            vueData.data.apps = data.result;
            isAppsLoaded = true;
            if (appLoadedCallback)
                appLoadedCallback(data.result);
        })
        .error(function(){

        });
    }

    exports.getApps = function(callback){
        if (isAppsLoaded)
            callback(vueData.data.apps);
        else
            appLoadedCallback = callback;
    }

});
