WEBDOCK.component().register(function(exports, scope){

    var currentLang = "en";
    var components = {};
    var scopes = {};

    function downloadLanguageFile(appId, componentId, cb){
        cb({});
    }

    exports.initialize = function(instance, scope, cb){
        var appId = instance.getAppId();
        var componentId = instance.getId();
        var appComponentId = appId + "." + componentId;
        scopes[appComponentId] = scope;
        
        if (!components[appComponentId])
            components[appComponentId] = {};

        if (components[appComponentId][currentLang]){
            //scope.i18n = components[appComponentId][currentLang];
            cb(scope.i18n);
        } else {
            downloadLanguageFile(appId, componentId, function(languageFile){
                //components[appComponentId][currentLang] = languageFile;
                //scope.i18n = languageFile;
                cb(scope.i18n);
            });
        }
       
    }

    exports.changeLanguage = function(lang,cb){
        currentLang = lang;
        cb(lang);
    }

});
