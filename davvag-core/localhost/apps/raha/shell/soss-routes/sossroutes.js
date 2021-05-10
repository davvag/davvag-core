WEBDOCK.component()
.configure(function(exports){
    exports.settings = {};
    exports.set = function (r){
        exports.settings.routes = r;
    };
})
.register(function(exports){

    var w = window;
    var currentRoute;
    
    var settings = exports.settings;

    var domLoaded = false;
    var awaitingPartial;

    w.onhashchange =  function(url){
        navigate(url);
    };


    function browserNavigate (route){
        var mainUrl = location.protocol+'//'+location.host+location.pathname+(location.search?location.search:"");
        var hashUrl = mainUrl + "#"  + route;
        window.location.href = hashUrl;  
    }

    function navigate(url){

        if (!url){
            browserNavigate(settings.routes.home);
            return;
        }
        var hi = url.newURL.indexOf ("#");
        var ohi = url.oldURL.indexOf ("#");

        var partialToDownload;
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

            if (settings.routes.partials)
                partialToDownload = settings.routes.partials[toUrl];
            
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
            if (domLoaded)
                injectPartial (partialToDownload);
            else 
                awaitingPartial = partialToDownload;
        }

    }

    function injectPartial(partial){
        downloadPartials(partial, function(data,instance){
            if (settings["inject-engine"]){
                var ie = settings["inject-engine"];
                ie.inject(data,instance, function(){
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
