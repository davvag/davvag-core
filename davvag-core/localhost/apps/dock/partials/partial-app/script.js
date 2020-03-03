WEBDOCK.component().register(function(exports){
    
    var pInstance = exports.getShellComponent("soss-routes");
    var routeSettings = pInstance.getSettings();

    var vueData =  {
        methods:{
        },
        data :{
            appName:undefined
        },
        deferRendering : true,
        onReady: function(s, renderDiv, variables){
            var appName = variables.routeParams.appName;
            var subRoute = variables.routeParams.appRoute;
            downloadApp(appName,subRoute);
        }
    } 

    exports.vue = vueData;
    exports.onReady = function(){
        
    }

    function downloadApp(appId,subRoute){
        var leftMenu = exports.getComponent("left-menu");
        WEBDOCK.freezeUiComponent("left-menu",true);
        leftMenu.getApps(function(apps){
            var appObj = apps[appId];
            var startupComponent;
    
            if (appObj.config.webdock)
            if (appObj.config.webdock.routes)
            if (appObj.config.webdock.routes.partials)
            if (appObj.config.webdock.routes.partials[subRoute])
                startupComponent = appObj.config.webdock.routes.partials[subRoute];
    
            if (!startupComponent)
                startupComponent = appObj.config.webdock.startupComponent;
            
            
            var renderDiv = $("#" + routeSettings.routes.renderDiv);
            renderDiv.empty();
            showLoadingBar(renderDiv);
    
            WEBDOCK.componentManager.downloadAppDescriptor(appId, function(descriptor){
                WEBDOCK.componentManager.downloadComponents(appId, descriptor,function(){
                    WEBDOCK.componentManager.getOnDemand(appId,descriptor, startupComponent, function(results,desc, instance){
                        renderApp(results,desc,instance);
                        WEBDOCK.freezeUiComponent("left-menu",false);
                    },appObj.version);
                },appObj.version);       
            },appObj.version);
        });
        
    }

    function showLoadingBar(renderDiv){
        renderDiv.append("<div id='status' style='left:50%;top:50%;position:fixed;'><i class='fa fa-spinner fa-spin'></i></div>");
    }

    function renderApp(data,desc,instance){
        try {
            var renderDiv = $("#" + routeSettings.routes.renderDiv);
            renderDiv.empty();

            var vueData, view;        
            for (var i=0;i<data.length;i++)
            if (data[i].object.type === "mainView")
                view = data[i].object.view;

            
            var viewJQuery = $(view);

            if (!instance.deferredVue){
                renderDiv.html(view);
                renderDiv.attr("style", "animation: fadein 0.2s");
            }
            
            if (!instance)
                return;

            if (instance.onLoad)
                instance.onLoad(instance);
            
            var canCallOnReady = true;
            if (instance.vue){
                if (!$(renderDiv).attr('id'))
                    $(renderDiv).attr('id', "sossroutes_" + (new Date()).getTime() );

                instance.vue.el = '#' + $(renderDiv).attr('id');
                new Vue(instance.vue);
                scope = instance.vue.data;
                canCallOnReady = false;

                if (instance.vue.onReady){
                    instance.vue.onReady(scope,renderDiv);
                }
            }

            if (instance.deferredVue){
                canCallOnReady = false;

                instance.deferredVue(function(vueData){
                    renderDiv.html(viewJQuery);
                    renderDiv.attr("style", "animation: fadein 0.2s");

                    if (!$(renderDiv).attr('id'))
                        $(renderDiv).attr('id', "sossroutes_" + (new Date()).getTime() );

                    vueData.el = '#' + $(renderDiv).attr('id');
                    new Vue(vueData);
                    scope = vueData.data;

                    if (vueData.onReady){
                        vueData.onReady(scope,renderDiv);
                    }                       
                }, viewJQuery);

            }

            if (canCallOnReady && instance.onReady)
                instance.onReady(renderDiv);
            
        } catch (e){
            console.log ("Error Occured While Loading...");
            console.log (e);
        }
    }
    
});
