WEBDOCK.component().register(function(exports){

    var w = window;
    var pInstance = exports.getShellComponent("soss-routes");
    

    var exports = {
        inject: function (data, instance, routeParams, cb){
            if (!data)
                return;
                
            try {
                var routeSettings = pInstance.getSettings();
                var renderDiv = $("#" + routeSettings.routes.renderDiv);

                var vueData, view;        
                for (var i=0;i<data.length;i++)
                if (data[i].object.type === "mainView")
                    view = data[i].object.view;

                renderDiv.html(view);

                if (!instance)
                    return;

                if (instance.onLoad)
                    instance.onLoad(instance);                
                
                vueData = instance.vue; 
                var app;

                if (!vueData.deferRendering){
                    if (vueData.onBeforeRender)
                        vueData.onBeforeRender();
                    
                    vueData.el = '#' + routeSettings.routes.renderDiv;

                    app = new Vue(vueData);                    
                }

                if (vueData.onReady)
                    vueData.onReady(app, renderDiv,routeParams);

                cb (data);
            } catch (e){
                console.log ("Error Occured While Loading...");
                console.log (e);
                cb();
            }
        }
    };
    pInstance.configure ("inject-engine", exports);
});