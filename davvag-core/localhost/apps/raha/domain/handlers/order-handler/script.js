WEBDOCK.component().register(function(exports){

    exports.test = function(){
        return exports.backend().get("test");
    }

    exports.categories = function(){
        return exports.backend().get("testcategories");
    }
    
});
