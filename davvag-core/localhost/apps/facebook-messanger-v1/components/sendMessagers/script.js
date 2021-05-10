WEBDOCK.component().register(function(exports){
    var scope;
    var bindData = {
        item:{},
        filters:["is","contains","begins-with","end-with"],
        davvagflows:[],
        submitErrors: undefined,
        i18n: undefined,
        broadcast:{}
    };

    function loadall(){
        Rini=exports.getShellComponent("soss-routes");
        routeData = Rini.getInputData();
        if(routeData.id){
                var h = exports.getComponent("broadcaster-handler");
                h.services.BroadcastByID({"id":routeData.id}).then(function(r){
                    bindData.broadcast=r.result;
                    bindData.items=JSON.parse(r.result.content);
                    bindData.query=JSON.parse(r.result.query);
                }).error(function(){

                });
        }
        
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
