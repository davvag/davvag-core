WEBDOCK.component()
.configure(function(exports){
    exports.settings = null;
    exports.set = function (r){
        exports.settings.routes = r;
    };
})
.register(function(exports){

    var w = window;
    var currentRoute;
    
    var settings = exports.settings;

    if (!settings){
        settings = {};
        var appDesc = exports.getAppDescriptor();
        if (appDesc.configuration)
        if (appDesc.configuration.webdock){
            settings.routes = appDesc.configuration.webdock.routes;
        }
    }

    settings.location = location;

    var domLoaded = false;
    var awaitingPartial;

    w.onhashchange =  function(url){
        navigate(url);
    };


    function browserNavigate (route, relativePath){
        var hashUrl;

        if (route){
            var mainUrl = location.protocol+'//'+location.host+location.pathname+(location.search?location.search:"");
            hashUrl = mainUrl + "#"  + route;
        }else {
            if (WEBDOCK.helpers.contains(relativePath, "..")){
                var newLoc = "";
                if (location.hash){

                    var doubleDotCount =0;
                    var pathWithoutDots = "";
                    var relativeSplit = relativePath.split("/");
                    for(var i=0;i<relativeSplit.length;i++){
                        if (relativeSplit[i] == "..")
                            doubleDotCount++;
                        else {
                            pathWithoutDots+=("/" + relativeSplit[i]);
                        }
                    }

                    var splitData = location.hash.split("/");
                    newLoc = "#";
                    for (var i=1;i<splitData.length -1; i++)
                        newLoc += ("/" + splitData[i]);
                    newLoc += pathWithoutDots;
                }


                relativePath = undefined;
                hashUrl = location.protocol+'//'+location.host+location.pathname+ newLoc;
            }else {
                hashUrl = location.protocol+'//'+location.host+location.pathname+ (location.hash ? location.hash : "") + (location.search?location.search:"");
            }
        }

        if (relativePath)
            hashUrl += relativePath;

        window.location.href = hashUrl;  
    }

    function navigate(url){

        if (!url){
            browserNavigate(settings.routes.home);
            return;
        }
        var hi = url.newURL.indexOf ("#");
        var ohi = url.oldURL.indexOf ("#");

        var partialToDownload,routeParams;
        if (hi !=-1){
            var toUrl = url.newURL.substring(hi +1);
            var fromUrl = url.oldURL.substring(ohi +1);


            var qi = toUrl.indexOf ("?");
            
            //if (!dataBag[toUrl]) 
                dataBag[toUrl] = {};

            if (qi !=-1){
                qparams = toUrl.substring (qi + 1);
                toUrl = toUrl.substring(0,qi);
                
                dataBag[toUrl] = {};
                
                var paramList = qparams.split ("&");

                for (pi in paramList){
                    var kv =  paramList[pi].split ("=");                    
                    dataBag[toUrl][kv[0]] = kv.length == 1 ? undefined : kv [1];
                }

            } 

            currentRoute = toUrl;

            if (settings.routes.partials){
                var partials = settings.routes.partials;

                for (var pk in partials){
                    if (WEBDOCK.helpers.contains(pk, ["@", "*"])){
                        var routeParts = pk.split("/");
                        var urlParts = toUrl.split("/");

                        var isMatched = true;
                        var variables = {};

                        if (WEBDOCK.helpers.contains(pk, "*")){
                            var canBreak = false;
                            for (var i=0;i<routeParts.length;i++){
                                switch (routeParts[i][0]){
                                    case "@":
                                        variables[routeParts[i].substring(1)] = urlParts[i];
                                        break;
                                    case "*":
                                        var restUrl=  "";
                                        for (j=i;j<urlParts.length;j++)
                                            restUrl += ("/" + urlParts[j]);
                                        variables[routeParts[i].substring(1)] = restUrl;
                                        canBreak = true;
                                        break;
                                    default:
                                        if (urlParts[i] != routeParts[i]){
                                            isMatched = false;
                                            canBreak = true;
                                        }
                                        break;
                                }

                                if (canBreak)
                                    break;
                            }
                        }else {
                            if (routeParts.length == urlParts.length){
                                for (var i=0;i<urlParts.length;i++){
                                    if (routeParts[i][0] == "@"){
                                        variables[routeParts[i].substring(1)] = urlParts[i];
                                    }else {
                                        if (urlParts[i] != routeParts[i]){
                                            isMatched = false;
                                            break;
                                        }
                                    }
                                }
                            } 
                            else {
                                isMatched = false;
                            }
                        }

                        if (isMatched){
                            partialToDownload = partials[pk];
                            routeParams = {routeParams:variables, queryParams:{}};
                            break;                            
                        }
                    }else {
                        if (pk == toUrl){
                            partialToDownload = partials[pk];
                            routeParams = {routeParams:{}, queryParams:{}};
                            break;
                        }
                    }
                }
            }
                
            
        }else{
            if (settings.routes)
            if (settings.routes.home){
                browserNavigate(settings.routes.home);
            }            
        }

        if (!partialToDownload){
            if (settings.routes.notFound && settings.routes.partials){
                partialToDownload = settings.routes.partials[settings.routes.notFound];
            }
        }

        if (!partialToDownload){
            alert ("Not Found at all!!!");
        } else {
            if (typeof partialToDownload === "string")
                partialToDownload = {
                    partial: partialToDownload,
                    persist: false
                }

            var downloadObj = {
                partial: partialToDownload,
                routeParams : routeParams
            }
            if (domLoaded)
                injectPartial (downloadObj);
            else 
                awaitingPartial = downloadObj;
        }

    }

    function injectPartial(downloadObj){
        var partial = downloadObj.partial;

        downloadPartials(partial, function(data,instance){
            if (settings["inject-engine"]){
                var ie = settings["inject-engine"];
                ie.inject(data,instance, downloadObj.routeParams, function(){
                    window.scrollTo(0,0);
                    if (instance.onReady){
                        if (!instance.onReadyCalled){
                            instance.onReady();
                            instance.onReadyCalled = true;
                        }
                    }
                    //console.log (data);
                });
            }
        })
    }

    function downloadPartials(pObj, cb){
        WEBDOCK.componentManager.downloadAppDescriptor(undefined, function(descriptor){
            WEBDOCK.componentManager.getOnDemand(undefined, descriptor, pObj.partial, function(results,desc, instance){
                cb(results, instance);
            });
        });
    }

    var dataBag = {

    }

    function initialNavigate(){
        var hashLoc = window.location.href.indexOf("#")
        if (hashLoc != -1){
            var routeToNavigate = window.location.href.substr(hashLoc);
            navigate ({
                newURL: routeToNavigate,
                oldURL: routeToNavigate
            });
        }else navigate();
    }

    exports.sendMessage = function (m){

    };
    exports.configure = function (k, v){
        settings [k] = v;
    };
    exports.getSettings = function(){
        return settings;
    };
    exports.navigate = function (route, data){
        dataBag[route] = data;
        browserNavigate("route");
    };

    exports.appNavigate = function(relativeUrl){
        browserNavigate(undefined, relativeUrl);
    }

    exports.getInputData = function (){
        return (dataBag && currentRoute) ? dataBag[currentRoute] : undefined;
    }
    
    exports.onReady = function(el){
        domLoaded = true;

        var id = "routes_" + jQuery.now();
        el.attr("id", id)
        settings.routes.renderDiv  = id;

        if (awaitingPartial){
            injectPartial(awaitingPartial);
            awaitingPartial = undefined;
        }
    }

    initialNavigate();
});
