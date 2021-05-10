
WEBDOCK.component().register(function(exports, scope){
    var $progress,$progressBar,$closebutton,$modal;
    exports.initialize = function(){
        
        //clearCeate();
    }
    var callback;
    var errCallback,completed,data_collected;

    
    exports.downloadAPP=function(appId,startupComponent,id,cb,er,cbcompleted,d){
        callback=cb;
        errCallback=er;
        completed=cbcompleted;
        data_collected=d;
        //var leftMenu = exports.getComponent("left-menu");
        //WEBDOCK.freezeUiComponent("left-menu",true);
        //leftMenu.getApps(function(apps){
            var renderDiv = $("#" + id);
            renderDiv.empty();
            renderDiv.html("<h1>Please Wait Loading</h1>");
            ver="9.0";
            //var appObj = apps[appId];
            WEBDOCK.componentManager.downloadAppDescriptor(appId, function(descriptor){
                WEBDOCK.componentManager.downloadComponents(appId, descriptor,function(){
                    WEBDOCK.componentManager.getOnDemand(appId,descriptor, startupComponent, function(results,desc, instance){
                        renderApp(results,id,desc,instance);
                        
                    },ver);
                },ver);       
            },ver);
        //});
    }

    function renderApp(data,id,desc,instance){
        try {
            var renderDiv = $("#" + id);
            renderDiv.empty();

            var vueData, view;        
            for (var i=0;i<data.length;i++)
            if (data[i].object.type === "mainView")
                view = data[i].object.view;

            renderDiv.html(view);
            renderDiv.attr("style", "animation: fadein 0.2s;padding-top: 0px;");
            //renderDiv.append("<div class='modal fade' id='appPopup0001' role='dialog' tabindex='-1'  style='overflow-x: auto;overflow-y: auto;width:100%;'><div class='modal-dialog modal-dialog-centered' role='document'><div class='modal-content' style='overflow-x: auto;overflow-y: auto;'><div class='modal-header'><h1>Appname</h1></div><div id='appbody' class='modal-body'>{{appbody}}}</div></div>");
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
                    if(completed){
                        instance.vue.onReady(scope,{status:"internalcall",data:data_collected,completedEvent:completed,renderDiv:renderDiv});
                    }else{
                        instance.vue.onReady(scope,renderDiv);
                    }
                }
            }

            if (canCallOnReady && instance.onReady)
                instance.onReady(renderDiv);
            
            callback(desc);
        } catch (e){
            console.log ("Error Occured While Loading...");
            console.log (e);
            errCallback(e);
        }
    }

});
