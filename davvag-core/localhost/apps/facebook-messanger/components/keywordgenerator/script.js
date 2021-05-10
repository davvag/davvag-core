WEBDOCK.component().register(function(exports){
    var scope;
    var handler;
    var pInstance, validatorInstance;
    var routeData;

    var bindData = {
        item:{},
        filters:["is","contains","begins-with"],
        davvagflows:[],
        submitErrors: undefined,
        i18n: undefined
    };

    var vueData =   {
        methods: {
            navigateBack: function(){
                handler1 = exports.getShellComponent("soss-routes");
                handler1.appNavigate("..");
            }
        },
        data : bindData,
        onReady : function(s){
            scope = s;
            
        }
    }

    exports.vue = vueData;
    exports.onReady = function(element){
    }
});
