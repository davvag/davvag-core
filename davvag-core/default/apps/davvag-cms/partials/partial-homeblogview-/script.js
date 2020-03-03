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
            //createlayout();
            createlayout();
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
                return marked(unescape(value));
              },
              dateformate:function(v){
                  if(!v){
                      return ""
                  }else{
                    return moment(v, "MM-DD-YYYY hh:mm:ss").format('MMMM Do YYYY');
                  }
              }
        }
    } 


    function createlayout(){
        /// im using this to set up the blog list
        var container = document.querySelector('#bloglist');
        var msnry = new Masonry( container, {
        // options
        columnWidth: '.col-xs-6',
        itemSelector: '.col-xs-6'
        });
        
        // check on load
        if(jQuery(window).width() <= 480 )
            msnry.destroy();

        // check on resize
        jQuery(window).resize(function(){
            if(jQuery(this).width() <= 480 )
                msnry.destroy();
        });
        
        // relayout items when clicking chat icon
        jQuery('#chatview, .menutoggle').click(function(){
        msnry.layout();
        });

        //msnry.layout();
    }
    exports.vue = vueData;
    exports.onReady = function(){
        
        

    }

    
});
