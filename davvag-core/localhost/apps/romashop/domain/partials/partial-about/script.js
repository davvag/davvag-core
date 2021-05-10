WEBDOCK.component().register(function(exports){
    var scope;

    var vueData =  {
        methods:{
            navigatePage: function(){

            }
        },
        data :{
            items : []
        },
        onReady: function(s){
            scope = s;
        }
    } 

    exports.vue = vueData;
    exports.onReady = function(){
    }
});
