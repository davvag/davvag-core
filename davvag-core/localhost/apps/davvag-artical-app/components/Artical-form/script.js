WEBDOCK.component().register(function(exports){
    var scope;

    
    var bindData = {
        product:{},
        uoms:[],
        content:""

    };

    var vueData =   {
        methods: {

        },
        data : bindData,
        onReady : function(s){
            scope = s;
            
            $('#editor').wysiwyg();
        }
    }

    exports.vue = vueData;
    exports.onReady = function(element){
        
    }
});
