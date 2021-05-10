WEBDOCK.component().register(function(exports){
    var scope;
    var bindData = {
        item:{},
        filters:["is","contains","begins-with","end-with"],
        davvagflows:[],
        submitErrors: undefined,
        i18n: undefined
    };

    function loadall(){
        var handler = exports.getComponent("keyword-handler");

        handler.services.DavvagFlows({"pageid":"@none"}).then(function(result){
            bindData.davvagflows = result.result;
            routeData = Rini.getInputData();
            if(routeData.id){
                handler.services.KeywordByID({"id":routeData.id}).then(function(r){
                    bindData.item=r.result;
                }).error(function(){

                });
            }
        }).error(function(){
            
        });
        
    }

    var vueData = {
        methods:{
            navigate: function(id){
                handler = exports.getShellComponent("soss-routes");
                handler.appNavigate("..");
            },
            submit:function(){
                var handler = exports.getComponent("keyword-handler");
                handler.services.SaveKeywords(bindData.item).then(function(result){
                    bindData.item=result.result;
                    console.log(result.result);
                    Rini.appNavigate("..");
                }).error(function(e){
                    bindData.submitErrors=[JSON.parse(e.responseText).result];
                   //JSON.parse(e.responseText)
                    console.log(e.responseText);
                });
            }
        },
        data :bindData,
        onReady: function(s){
            scope = s;
            Rini = exports.getShellComponent("soss-routes");
            loadall();
            
            
            //loadInventory(undefined,0,100);
        }
    }    

    exports.vue = vueData;
    exports.onReady = function(element){
    }
});
