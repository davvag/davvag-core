WEBDOCK.component().register(function(exports){

    var bindData={
        Articals:[],
        Products:[]
    };
    var vueData =  {
        methods:{
        },
        data :bindData
        ,
        onReady: function(s){
            $('#list').click(function(event){event.preventDefault();$('#products .item').addClass('list-group-item');});
            $('#grid').click(function(event){event.preventDefault();$('#products .item').removeClass('list-group-item');$('#products .item').addClass('grid-group-item');});
            var menuhandler  = exports.getComponent("soss-data");
            var query=[{storename:"d_cms_artical_v1",search:"boost:y"}];
            //var tmpmenu=[];
            bindData.TopButtons=[];
            menuhandler.services.q(query)
                        .then(function(r){
                            console.log(JSON.stringify(r));
                            if(r.success){
                                bindData.Articals= r.result.d_cms_artical_v1;
                            }
                        })
                        .error(function(error){
                            bindData.Articals=[];
                            console.log(error.responseJSON);
            });
        },
        filters:{
            markeddown: function (value) {
                if (!value) return ''
                value = value.toString()
                return marked(value);
              }
        }
    } 

    exports.vue = vueData;
    exports.onReady = function(){
        
        

    }

    
});
