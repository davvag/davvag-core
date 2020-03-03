WEBDOCK.component().register(function(exports){

    var bindData={
        Articals:[],
        Products:[],
        titlepage:{name:"",title:"",caption:""}
    };
    var vueData =  {
        methods:{
        },
        data :bindData
        ,
        onReady: function(s){
            //createlayout();
            //createlayout();
            var menuhandler  = exports.getComponent("soss-data");
            var query=[{storename:"d_cms_artical_v1",search:"boost:y"}];
            //var tmpmenu=[];
            bindData.TopButtons=[];
            menuhandler.services.q(query)
                        .then(function(r){
                            console.log(JSON.stringify(r));
                            if(r.success){
                                r.result.d_cms_artical_v1.sort((a,b) => (moment(a.createdate, "MM-DD-YYYY hh:mm:ss").format('YYYYMMDDhhmm') > moment(b.createdate, "MM-DD-YYYY hh:mm:ss").format('YYYYMMDDhhmm')) ? -1 : ((moment(b.createdate, "MM-DD-YYYY hh:mm:ss").format('YYYYMMDDhhmm') > moment(a.createdate, "MM-DD-YYYY hh:mm:ss").format('YYYYMMDDhhmm')) ? 1 : 0)); 
                                bindData.Articals= r.result.d_cms_artical_v1;
                            }
                        })
                        .error(function(error){
                            bindData.Articals=[];
                            console.log(error.responseJSON);
            });

            if(sessionStorage.blogheader){
                bindData.titlepage=JSON.parse(sessionStorage.blogheader);
            }else{
                var data={name:"cms-global"}
                menuhandler.services.Settings(data)
                        .then(function(r){
                            console.log(JSON.stringify(r));
                            if(r.success){
                                bindData.titlepage= r.result;
                                sessionStorage.blogheader=JSON.stringify(r.result)
                            }
                        })
                        .error(function(error){
                            //bindData.Articals=[];
                            console.log(error.responseJSON);
            });
        }
        
            
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



    exports.vue = vueData;
    exports.onReady = function(){
        
        

    }

    
});
