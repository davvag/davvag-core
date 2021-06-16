(function (w){

    var settings = {
        componentRepo: {},
        descriptorRepo:{},
        orderedPlugins : [],
        callbacks : {}
    };

    var currentState = "loading";
    var initializingPlugin;

    var webdockMainApp = document.currentScript.getAttribute('webdockapp');

    function changeState(state, msg){
        currentState = state;
        if (settings.callbacks.onStatusChange)
            settings.callbacks.onStatusChange({state: state, message: msg});

        if (state === "loaded"){
            if (settings.callbacks.onReady)
                settings.callbacks.onReady();
        }
    }

    var Helpers = (function(){
        return {
            startsWith: function(haystack,str){
                return(haystack.indexOf(str) == 0);
            },
            paramsToArray : function (arr, argu, skipFirst){
                for (var i=0;i<argu.length;i++)
                if (!(i==0 && skipFirst))
                    arr.push(argu[i]);
                return arr;
            },
            getValueByPath : function (obj, path){
                var paths = path.split (".");
                var cObj = obj;

                for (var i=0;i<paths.length;i++){
                    cObj = paths[i];
                    if (!cObj)
                        break;
                }

                return cObj;
            },
            contains : function(value, searchFor){
                
                if (searchFor.constructor === Array){
                    for(var i=0;i<searchFor.length;i++){
                        if ((value || '').indexOf(searchFor[i]) > -1)
                            return true;
                    }
                    return false;
                }else {
                    return (value || '').indexOf(searchFor) > -1;
                }
            }
        };
    })();

    function WebdockPromise(logicFunc,params){
        var callbacks ={};
        
        var ctrl = {
            resolve: function(data){
                if (callbacks.success)
                    callbacks.success(data);
            },
            reject: function(data){
                if (callbacks.error)
                    callbacks.error(data);
            }
        }
        function evaluate(){
            if (callbacks.success && callbacks.error)
                logicFunc(ctrl, params);
        }

        return {
            then: function(cb){
                callbacks.success= cb;
                evaluate();
                return this;
            },
            error: function (cb){
                callbacks.error = cb;
                evaluate();
                return this;
            }
        }   
    }

    function BackendRequestor(appId,compId, type){

        function call(params){
            return new WebdockPromise(function(ctrl, params){
                var url = "components/" + appId + "/" + compId + "/" + type + "/" + params.method;
                var stub = new AjaxRequestor(url, params.params ? params.params : undefined, params.type ? params.type : "GET");
                
                stub.success(function(result){
                    ctrl.resolve(result);
                })
                .error(function(result){
                    ctrl.reject(result);
                });
            }, params);
        }

        return {
            get: function(method){
                return call({method: method, type: "GET"});
            },
            post:function (method, params){
                return call({method: method, params: params, type: "POST"});
            }
        }
    }

    var dataCacheManager = function (){
        return {
            
        }
    }

    function BackendFunction(appId,compId, type, sk,sv, extraParams){
        var br = new BackendRequestor(appId,compId, type);
        var meth = sv.method.toLowerCase();              
        
        function call(){

            var settingsObj;

            if (arguments.length >1)
                settingsObj = (meth === "get") ? arguments[1] : arguments[2];
            

            var routeObj = sv.route;
            if (!routeObj){
                routeObj = sk;
                
                if(extraParams && arguments.length > 0){
                    var getString = "?";
                    var paramObj = arguments[(meth === "get") ? 0 : 1];
                    var isFirst = true;
                    
                    for (var i=0;i<extraParams.length;i++){
                        var value = paramObj[extraParams[i]];
                        if (value){
                            if (!isFirst) getString+="&";
                            else isFirst = false;
                            getString += (extraParams[i] + "=" + value);
                        }
                    }
                    if (!isFirst)
                        routeObj += getString;   
                }
            }

            if (Helpers.contains(routeObj, "@")){
               var splitData = routeObj.split("/");
               var si = (meth === "get") ? 0 : 1;
               routeObj = "";
               for (var i=0;i<splitData.length;i++){
                   if (i > 0) routeObj +="/";

                   if (splitData[i][0] == "@"){
                       if (arguments.length > si)
                           routeObj += arguments[si];
                       si++;
                   }else {
                       routeObj += splitData[i];
                   }
               }
            }

            if (routeObj[0] === "/")
                routeObj = routeObj.substring(1);

            if (meth === "get"){
                if (settingsObj){
                    if (settingsObj.enableCache){
                        return new WebdockPromise(function(resolve,reject){
                            var parm = Helpers.paramsToArray([routeObj], arguments, false);
                            
                            br.get.apply(this,parm)
                            .then(function(data){
                                
                            })
                            .error(function(error){
                                console.log(error);
                            });
                        });
                    }
                }
                var parm = Helpers.paramsToArray([routeObj], arguments, false);
                return br.get.apply(this,parm);
            }
            else{
                var parm = Helpers.paramsToArray([routeObj, arguments[0]], arguments, true);
                return br.post.apply(this,parm);
            }
        }

        return call; 
    }

    function ComponentInstance(appId,compId, descriptor, appdescriptor){
        var regFunc, confFunc,statechangefunc;
        var state="normal";
        var exports =  {
            getAppDescriptor: function(){return appdescriptor;},
            getDescriptor: function(){return descriptor;},
            getAppId: function(){return appId;},
            getId: function(){return compId;},
            register: function(f){
                regFunc = f;
                //delete this.register;
            },
            configure: function(f){
                confFunc = f;
                return this;
            },
            onLoad: function(){
                if (regFunc)
                    regFunc(this);
            },
            onConfigure: function(){
                if (confFunc)
                    confFunc(this);
            },
            getComponent: function(c){
                return settings.componentRepo[appId][c];
            },
            getResource: function(type, params,cb){
                componentManager.downloadResource(type,params, appId, compId, function(data){
                    cb(data);
                });
            },
            getAppComponent: function(compAppId,c, cb){
                var canDownload = true;
                compAppId = compAppId ? compAppId : webdockMainApp;
                if (settings.componentRepo[compAppId])
                if (settings.componentRepo[compAppId][c]){
                    canDownload = false;
                    cb (settings.componentRepo[compAppId][c]);
                }
                    
                if (canDownload){
                    componentManager.downloadAppDescriptor(compAppId, function(descriptor){
                        componentManager.getOnDemand(compAppId, descriptor, c, function(res,desc,inst){
                            if(inst){
                                try {
                                    if(inst.onLoad){
                                        inst.error=false;
                                        inst.onLoad();
                                        cb (inst);
                                    }else{
                                        console.log("Error [getAppComponent: Lod is not diffined.] APPID:" +compAppId);
                                        console.log(inst);
                                        cb(inst);
                                        
                                    }
                                } catch (error) {
                                    cb({error:true,message:"app not loaded"});
                                    console.log("Error [getAppComponent: ]APPID:" +compAppId);
                                    console.log(error);
                                }
                                
                            }else{
                                console.log("Reloading... site please wait.NUll Exception APPID:" +compAppId)
                                //location.reload();
                            }
                            
                        });
                    });
                }
            },
            getShellComponent: function(c){
                return settings.componentRepo[webdockMainApp][c];
            },
            backend: function(o, type){
                if(!type)
                    type="service";
                return new BackendRequestor(appId,compId, type);
            },
            setState: function(s){
                state = s;
                if (statechangefunc)
                    statechangefunc(state);
            },
            getState:function(){
                return state;
            },
            onStateChange:function(func){
                statechangefunc = func;
            },
            componentId: compId
        }

        function injectBackendFunction(exportKey, type, mObj){
            exports[exportKey] = {}; 
            for (var sk in mObj){
                var sv = mObj[sk];
                if (sv.method)
                    exports[exportKey][sk] = new BackendFunction(appId,compId, type, sk,sv, sv.parameters);
            }
        }

        if (descriptor.serviceHandler){
            var mObj = descriptor.serviceHandler.methods; 
            if (mObj)
            injectBackendFunction("services","service", mObj);
        }

        if (descriptor.transformers){
            var mObj = descriptor.transformers; 
            injectBackendFunction("transformers","transform", mObj);
        }

        settings.componentRepo[appId][compId] = exports;

        return exports;
    }

    function AjaxRequestor(url, postParams, method){
        var sendObj;
        var sf, ef;
        var retries = 1;

        function issueRequest(){
            changeState ("busy");
            $.ajax(sendObj);
        }

        function errorFunc(data){
            retries++;
            if (retries ==3){
                changeState ("idle");
                ef(data);
            }
            else 
                issueRequest();
        }

        function successFunc(data){
            changeState ("idle");
            sf (data);
        }

        function callRest(){
            if (!sf || !ef) return;

            if (typeof (url) === "string"){
                
                sendObj = {
                    url: url,
                    xhrFields: {withCredentials: true},
                    contentType: "application/json",
                    success: successFunc,
                    error: errorFunc
                }
                
                if (postParams){
                    var isFile = (postParams instanceof File) || (postParams instanceof Blob);

                    if (isFile){
                        sendObj.data = postParams;
                        sendObj.processData = false;
                    }
                    else {
                        if (typeof(postParams) === "string")
                            sendObj.data = postParams;
                        else
                            sendObj.data = JSON.stringify(postParams);
                    }
                    

                }

                if (method)
                    sendObj.method = method;

            }else{
                sendObj = url;
                sendObj.success = successFunc;
                sendObj.error = errorFunc;
            }

            issueRequest();
        }

        
        return {
            success: function (f){ sf = f; callRest(); return this;},
            error: function(f){ ef = f; callRest(); return this;}
        }
    }


    function ParallelAsyncIterator(arr, callbacks){
        var results = [];
       
        function evaluate(){
            if (results.length == arr.length){
                if (callbacks.complete)
                    callbacks.complete(results);
            }
        }

        function ParallelController(index){
            return {
                success: function(res){
                    var resObj = {index: index, object:arr[index], result: res, success: true};

                    results.push (resObj);

                    if (callbacks.completeOne)
                        callbacks.completeOne(resObj);
                    
                    evaluate();
                },
                error: function(res){
                    var resObj = {index: index, object:arr[index], result: res, success: false};
                    results.push (resObj);
                    
                    if (callbacks.completeOne)
                        callbacks.completeOne(resObj);

                    if (callbacks.error)
                        callbacks.error(resObj);

                    evaluate();
                }
            }
        };

        function next(){
            
            for (var i=0;i<arr.length;i++){
                    var controller = new ParallelController(i);
                    callbacks.logic(arr[i], controller);
            }

        }

        return {
            start: next
        }
    }

    function SequentialAsyncIterator(arr, callbacks){
        var index = -1;
        var results = [];

        var controller =  (function(){
            return {
                success: function(res){
                    var resObj = {index: index, object:arr[index], result: res, success: true};

                    results.push (resObj);

                    if (callbacks.completeOne)
                        callbacks.completeOne(resObj);

                    next();
                },
                error: function(res){
                    var resObj = {index: index, object:arr[index], result: res, success: false};
                    results.push (resObj);
                    
                    if (callbacks.completeOne)
                        callbacks.completeOne(resObj);

                    if (callbacks.error)
                        callbacks.error(resObj);
                    next();
                }
            }
        })();

        function next(){
            index++;

            if (index == arr.length){
                if (callbacks.complete)
                    callbacks.complete(results);
            }
            else
                callbacks.logic(arr[index], controller);
        }

        return {
            start: next
        }
    }

    function AsyncIterator(arr, isParallel=true){
        var callbacks = {};
        var iterator = isParallel ? new ParallelAsyncIterator(arr, callbacks) : new SequentialAsyncIterator(arr, callbacks);

        return {
            onComplete: function (cb){callbacks.complete = cb},
            onCompleteOne: function (cb){callbacks.completeOne = cb},
            onError: function (cb){ callbacks.error = cb },
            logic: function(cb){callbacks.logic = cb},
            start: iterator.start 
        }
    }

    var componentDownloader = (function(){

        var callbacks = {};
        var loadingScripts = {};

        function fixUrl(url,appId,component,verid){
            if (Helpers.startsWith(url,"http") || Helpers.startsWith(url,"https")){
                return url;
            }else {
                if (Helpers.startsWith(url,"//"))
                    return  window.location.protocol +  url;
                else 
                    return "components/"  + appId + "/" + component + "/file/" + url + (verid ? ("?v=" + verid) : "");
            }
        }
        
        function download(appId,component, scriptInjectQueue, appdescriptor, cb, verid){
            appId = appId ? appId: webdockMainApp;
            var requestor = new AjaxRequestor("components/" + appId + "/" + component + "/object?object=desc" + (verid ? ("&v=" + verid) : ""))
            .success(function(desc){
                if (!desc.success)
                    cb(desc)
                else{
                    var res = desc.result.resources;
                    if (res){
                        if (res.files || res.js || res.css){
                            var downComponents = [];

                            if (res.js)
                                downComponents = downComponents.concat(res.js);
                            
                            if (res.css)
                                downComponents = downComponents.concat(res.css);

                            var hasScript = false, hasView =false;
                            
                            if (res.files)
                            for (var i=0;i<res.files.length;i++){
                                var file = res.files[i];
                                if (file.type){
                                    if (file.type.toLowerCase() === "mainscript"){
                                        hasScript=true;
                                        file.url = "components/" + appId + "/" + component + "/file/" + file.location + (verid ? ("?v=" + verid) : "");
                                        desc.result.mainScript = file;
                                    }
                                    if (file.type.toLowerCase() === "mainview"){
                                        hasView=true;
                                        file.url = "components/" + appId + "/" + component + "/file/" + file.location + (verid ? ("?v=" + verid) : "");
                                        desc.result.mainView = file;
                                    }
                                }
                                downComponents.push(file);
                            }

                            var canDownloadSequencial = desc.result.sequencialResourceDownload ? false : true;
                            var iterator = new AsyncIterator(downComponents, !canDownloadSequencial);

                            iterator.logic(function(obj,ctrl){
                                var url;
                                if (obj.type || obj.tag)
                                    url = fixUrl(obj.location, appId, component,verid);

                                if (url){
                                    if (Helpers.startsWith(url,"http") || Helpers.startsWith(url,"https")){
                                        ctrl.success({success:true});
                                    }
                                    else {

                                        if (obj.tag){
                                            if (!componentManager.hasLibrary(obj.tag)){
                                                var scriptInjector = new ScriptInjector(false);
                                                scriptInjector.script(url);
                                                componentManager.registerLibrary(obj,component);
                                            }
                                            ctrl.success({success:true});                                           
                                        } else {

                                            switch (obj.type){
                                                case "mainScript":
                                                case "script":
                                                    ctrl.success({success:true});
                                                    break;
                                                case "css":
                                                    var scriptInjector = new ScriptInjector();
                                                    scriptInjector.css(url);
                                                    ctrl.success({success:true});
                                                    break;
                                                default:
                                                    var reqFile = new AjaxRequestor({url:url})
                                                    .success(function(result){
                                                        if (obj.type == "view" || obj.type == "mainView")
                                                        obj.view = result;
                                                        ctrl.success({success:true});
                                                    })
                                                    .error(function(result){
                                                        ctrl.error({success:false});
                                                    });
                                                    break;
                                            }

                                        }
                                    }

                                }else 
                                    ctrl.error({success:false, result: "Unknown file type in component descriptor"});
                                
                            });
                            iterator.onComplete(function(results){
                                cb(results,desc.result, initializingPlugin);
                            });
                            iterator.onCompleteOne(function(result){
                                if (result.success){
                                                                      
                                    var obj = result.object;

                                    if (obj.type){
                                        switch (obj.type){
                                            case "mainScript":
                                                scriptInjectQueue.push({appId: appId, type:"mainScript", component: component, url: fixUrl(obj.location, appId, component,verid), descriptor: desc.result, appDescriptor:appdescriptor});
                                                break;
                                            default:
                                                break;
                                        }
                                    }
                                    
                                }
                            });
                            iterator.start();

                        }else
                            cb ({success: false, result: "No files available in component resources"}); 
                            
                    }else 
                        cb ({success: false, result: "No Resources found in component descriptor"});
                }
            })
            .error (function(desc){
                console.log(desc);
            });
        }

        return {
            download: download,
            onScriptsLoaded: function(f){
                callbacks.onScriptsLoaded = f;
            }
        }
    })();

    var componentInjector = function(){

    }

    function ScriptInjector (hasCallback=true){

        var cb;

        function injectScript(location){
            try{
            
            var scripts= document.getElementsByTagName('script');
            scripts=scripts?scripts:[];
            var script=null;
            var i;
            var getUrl = window.location;
            var baseUrl = getUrl .protocol + "//" + getUrl.host + getUrl.pathname;
            //console.log(baseUrl);
            //console.log(baseUrl);
            /*
            for (i = 0; i < scripts.length; i++) {
              loc=baseUrl+location; 
              
              if(loc== scripts[i].src){
                console.log("ignored");
                script=scripts[i];
                break;
              }
            }*/
            
            if(!script){
                var head= document.getElementsByTagName('head')[0];
                var sr= document.createElement('script');
                sr.type= 'text/javascript';
                sr.src= location;

                if (hasCallback)
                    sr.onload = function(){
                        cb(sr);
                    }

                head.appendChild(sr);
            }else{
                if (hasCallback)
                    cb(script);
            }
            }catch(e){
                console.log(e);
            }
        }

        function injectCss(location){
            var head= document.getElementsByTagName('head')[0];
            var fileref = document.createElement("link");
            fileref.rel = "stylesheet";
            fileref.type = "text/css";
            fileref.href = location;
            head.appendChild(fileref);
        }

        return {
            css: injectCss,
            script: injectScript,
            onScriptLoaded: function(c){
                cb = c;
            }
        }
    }

    var componentManager = (function (){
        
        var callbacks = {};
        var descriptor;
        var htmlComponents;
        var scriptInjectQueue = [];

        function initialize(){
            var requestor = new AjaxRequestor("components/object/appdescriptor/" + webdockMainApp)
            .success(function(desc){
                if (!desc.success){
                    if (callbacks.init){
                        callbacks.init(desc);
                        delete callbacks.init;
                    }
                }
                else{
                    settings.descriptorRepo[webdockMainApp] = desc.result;
                    descriptor = desc.result;
                    downloadComponents(undefined,undefined,undefined,descriptor.description.version);
                }
            })
            .error(function(error){
                
            });
        }

        function getMemoryApp(appId,compId){
            if(window["getOnDemandApp"])
            if(window["getOnDemandApp"][appId])
            if(window["getOnDemandApp"][appId][compId])
                return window["getOnDemandApp"][appId][compId]
            else
                return null; 
        }

        function downloadAppDescriptor(appId, cb, verid){
            appId = appId ? appId : webdockMainApp;
            if (settings.descriptorRepo[appId]){
                cb(settings.descriptorRepo[appId]);
            }else {
                var requestor = new AjaxRequestor("components/object/appdescriptor/" + appId + (verid ? ("?v=" + verid) : ""))
                .success(function(desc){
                    settings.descriptorRepo[appId] = desc.result;
                    cb(desc.result);
                })
                .error(function(error){
                    cb(undefined);                
                });
            }
        }

        function injectComponents(cb){
            var iterator_injector = new AsyncIterator(scriptInjectQueue,false);
            iterator_injector.logic(function(obj, ctl){
                
                switch(obj.type){
                    case "mainScript":
                        var scriptInjector = new ScriptInjector();
                        if (!settings.componentRepo[obj.appId][obj.component])
                            initializingPlugin = new ComponentInstance(obj.appId, obj.component, obj.descriptor, obj.appDescriptor);
                        else
                            initializingPlugin = settings.componentRepo[obj.appId][obj.component];
                        var scriptInjector = new ScriptInjector();
                        scriptInjector.onScriptLoaded(function(){
                            delete initializingPlugin;
                            ctl.success(true);
                        });

                        scriptInjector.script(obj.url);
                        break;
                }

            });

            iterator_injector.onComplete(function(results){
                scriptInjectQueue = [];
                cb(results);
            });

            iterator_injector.start();
        }

        function downloadComponents(appId,appdescriptor,ondemandcallback, verid){
            var isOnDemand = true;
            if (!appId){
                isOnDemand = false;
                appId = webdockMainApp;
            }
            
            settings.componentRepo[appId] = {};

            appdescriptor = appdescriptor? appdescriptor : descriptor;

            var downComponents = [];
            if (appdescriptor.configuration)
            if (appdescriptor.configuration.webdock)
            {
                
                if (htmlComponents && !isOnDemand)
                    downComponents = downComponents.concat(htmlComponents);
                    
                if (appdescriptor.configuration.webdock.onLoad)
                    downComponents = downComponents.concat(appdescriptor.configuration.webdock.onLoad);
                
                settings.orderedPlugins = downComponents;

                var iterator = new AsyncIterator(downComponents);
                iterator.logic(function(obj, ctrl){
                    componentDownloader.download(appId,obj, scriptInjectQueue, appdescriptor, function(result,desc){
                        if (result.success)
                            ctrl.success(result.result);
                        else 
                            ctrl.error(result.result);
                    },verid);
                });
                iterator.onComplete(function(results){
                    injectComponents(function(){
                        if (ondemandcallback)
                            ondemandcallback(appdescriptor);
                            
                        if(callbacks.init){
                            callbacks.init(appdescriptor);
                            delete callbacks.init;
                        }
                    });
                });
                iterator.start();

            }
        }

        var libraries = {};
        function registerLibrary(obj, component){
            var _3c = libraries;
            if (!_3c[obj.tag])
                _3c[obj.tag] = {components: []}
            
            _3c[obj.tag].components.push(component);
        }

        function hasLibrary(lib){
            return libraries[lib] !== undefined;
        }


        function getOnDemand(appId,appdescriptor,compId,cb,verid){
            appId = appId ? appId : webdockMainApp;
            appdescriptor = appdescriptor ? appdescriptor : descriptor;

            window["getOnDemandApp"]=window["getOnDemandApp"]?window["getOnDemandApp"]:{};
            window["getOnDemandApp"][appId]=window["getOnDemandApp"][appId]?window["getOnDemandApp"][appId]:{};
            window["getOnDemandApp"][appId][compId]=window["getOnDemandApp"][appId][compId]?window["getOnDemandApp"][appId][compId]:{instance:null,results:null,desc:null};

            if(window["getOnDemandApp"][appId][compId].instance){
                cb(window["getOnDemandApp"][appId][compId].results,window["getOnDemandApp"][appId][compId].desc,window["getOnDemandApp"][appId][compId].instance);
                return;
            }
            
            if (!settings.componentRepo[appId])
                settings.componentRepo[appId] = {};
                
            componentDownloader.download(appId,compId, scriptInjectQueue, appdescriptor, function(results,desc){
                injectComponents(function(res){
                    var instance;
                    
                    if (settings.componentRepo[appId][compId])
                        instance = settings.componentRepo[appId][compId];

                    window["getOnDemandApp"][appId][compId].results=results;
                    window["getOnDemandApp"][appId][compId].instance=instance
                    window["getOnDemandApp"][appId][compId].desc=desc;
                    cb(results,desc,instance);
                });
            },verid);
        }

        function downloadResource(type,params,appId, compId, cb){
            params = params ? params : {};
            var paramList = "";
            for (var pKey in params){
                paramList += ("&" + pKey + "=" + params[pKey]);
            }
            
            new AjaxRequestor("components/" + appId + "/" + compId +  "/object?object=resource&resource=" + type + paramList)
            .success(function(desc){
                settings.descriptorRepo[appId] = desc.result;
                cb(desc.result);
            })
            .error(function(error){
                cb(error);                
            });
        }

        return {
            initialize: initialize,
            downloadAppDescriptor: downloadAppDescriptor,
            onInitialized: function(cb){
                callbacks.init = cb;
            },
            registerLibrary: registerLibrary,
            hasLibrary: hasLibrary,
            getOnDemand:  getOnDemand,
            downloadComponents: downloadComponents,
            downloadResource: downloadResource,
            getDescriptor: function (){ return descriptor; },
            setHtmlComponents: function(a){ htmlComponents = a;},
            getMemoryApp:getMemoryApp
        }
    })();

    function freezeUiComponent(componentid,isfrozen){
        var comp = settings.componentRepo[webdockMainApp][componentid];
        if (comp){
            var canFreeze = false;
            var uicomp = $("[webdock-component=" + componentid +"]");
            if (isfrozen === undefined){
                canFreeze = comp.getState() === "normal";
            }else {
                canFreeze = isfrozen;
            }

            if (canFreeze){
                uicomp.css('pointer-events', 'none');
                comp.setState("frozen");
            }else {
                uicomp.css('pointer-events', 'auto');
                comp.setState("normal");
            }

        }
    }

    w.WEBDOCK = {
        freezeUiComponent: freezeUiComponent,
        component: function(){
            return initializingPlugin;
        },
        callRest: function (url, params){
            return new AjaxRequestor(url, params);
        },
        onStatusChange: function(f){
            settings.callbacks.onStatusChange = f;
        },
        onError: function (f){
            settings.callbacks.onError = f;
        },
        onReady: function(f){
            settings.callbacks.onReady = f;
        },
        componentManager: componentManager,
        helpers: Helpers
    };

     $(document).ready(function(){

       
        componentManager.onInitialized(function(descriptor){
            if (descriptor.description)
                if (descriptor.description.title)
                    document.title = descriptor.description.title;

            function callMethodInAllPlugins (callingMethod){
                for (var i=0; i<settings.orderedPlugins.length;i++){
                    var plugObj = settings.componentRepo[webdockMainApp][settings.orderedPlugins[i]];
                    if (plugObj)
                    if (plugObj[callingMethod])
                        plugObj[callingMethod]();
                }
            }

            callMethodInAllPlugins("onConfigure");
            callMethodInAllPlugins("onLoad");

            var viewPlugList = {};

            function renderUiComponents(element){

                element.find("[webdock-component]").each(function(i,el){
                    var pk = $(this).attr("webdock-component");
                    if (!viewPlugList[pk]){                
                        var plugObj = settings.componentRepo[webdockMainApp][pk];
                        if (plugObj){
                            var descObj = plugObj.getDescriptor();

                            if (descObj.mainView){
                                var jqObject = $(descObj.mainView.view);
                                $(this).html(jqObject);
                                renderUiComponents(jqObject);
                            }
                            
                            
                            viewPlugList[pk] = {name:pk, obj:plugObj, el: $(this)};
                        }else {
                            
                        }
                    }
                });
            }

            renderUiComponents($(document));
/*
            for (pk in settings.componentRepo){
                var plugObj = settings.componentRepo[pk]; 

                if (plugObj.onReady){
                    if (plugObj.type == "component"){
                        if (viewPlugList[pk])
                            plugObj.onReady(viewPlugList[pk].el);
                    }
                    else{
                        if (plugObj.type == "shell" || plugObj.type == "service")
                            plugObj.onReady();
                    }

                }
            }
*/

            for (var pk in viewPlugList){
                var p = viewPlugList[pk];
                if (p.obj.onReady){
                    if (!p.obj.onReadyCalled){
                        p.obj.onReady(p.el);
                        p.obj.onReadyCalled = true;
                    }
                }
            }

            changeState("loaded");

        });

        var initialHtmlComponentList = $("[webdock-component]");
        var htmlComponentsToDownload = [];
        initialHtmlComponentList.each(function(i,el){
            htmlComponentsToDownload.push($(this).attr("webdock-component"));
        });
        componentManager.setHtmlComponents(htmlComponentsToDownload);

        componentManager.initialize();

    });



})(window);