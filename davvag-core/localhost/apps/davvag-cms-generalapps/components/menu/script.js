WEBDOCK.component().register(function(exports){
    var scope;



    var vueData =  {
        methods:{
            navigate: function(id){
                handler = exports.getShellComponent("soss-routes");
                handler.appNavigate(id);
            }
        },
        data :{
            items : []
        },
        onReady: function(s){
            scope = s;
            //loadUoms(0,100);
        }
    } 

    exports.vue = vueData;
    exports.onReady = function(element){
    }
});
