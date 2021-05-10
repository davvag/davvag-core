WEBDOCK.component().register(function(exports){
    var page=0,size=3;
    var routeData={},menuhandler={};
    
    var bindData={
        Articals:[],
        products:[],
        sidebar:[],
        titlepage:{name:"",title:"",caption:""},
        id:0,allloaded:false,loading:false,cards:[]
    };
    var vueData =  {
        methods:{
            
            getfiletype:function(filename){
                if(filename==null){
                    return "";
                }
                var a = filename.split(".");
                if( a.length === 1 || ( a[0] === "" && a.length === 2 ) ) {
                    return "";
                }
                filetype=a.pop().toLowerCase();
                switch(filetype){
                    case "jpeg":
                            return "img";
                    break;
                    case "png":
                        return "img";
                    break;
                    case "jpg":
                        return "img";
                    break;
                    case "mp3":
                        return "audio";
                    break;
                    default:
                        return "link";
                    break;
                }
                return a.pop().toLowerCase();
            }, 
            navProfile: function(id){
                window.location="#/app/com_qti_students/profile?id="+id.toString();
            },
            navDonate: function(id){
                window.location="#/app/com_qti_students/donate?id="+id.toString();
            }
        },
        data :bindData
        ,
        onReady: function(s){
            //createlayout();
            //createlayout();
            pInstance = exports.getShellComponent("soss-routes");
            routeData = pInstance.getInputData();
            menuhandler  = exports.getComponent("soss-data");
            


            loadData();
            
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

    function loadData(){
        bindData.loading=true;
        var query=[];
        
            if(routeData.pid==null){
                //
                //query.push({storename:"d_cms_cat_v1",search:"parentButtonid:0"});
                if(bindData.sidebar.length==0){
                    query.push({storename:"d_cms_cat_v1",search:"parentButtonid:0"},{storename:"d_cms_cards_v1",search:"parentButtonid:0"});
                }
                query.push({storename:"profiles_search",parameters:{page:page.toString(),size:size.toString(),search:"",rad:'0',lon:'0',lan:'0',cat:'0'}});

            }else{
                //query.push({storename:"d_cms_artical_v1",search:"catid:"+routeData.pid});
                //query.push({storename:"d_cms_cat_v1",search:"parentButtonid:"+routeData.pid});
                if(bindData.sidebar.length==0){
                    query.push({storename:"d_cms_cat_v1",search:"parentButtonid:"+routeData.pid},{storename:"d_cms_cards_v1",search:"parentButtonid:"+routeData.pid});
                }
                query.push({storename:"d_cms_artical_v1_pod_bycat_paging",parameters:{page:page.toString(),size:size.toString(),catid:routeData.pid.toString()}});

                bindData.id=routeData.pid;
            }
            //var tmpmenu=[];
            //bindData.TopButtons=[];
            var articals=[];
            menuhandler.services.q(query)
                        .then(function(r){
                            console.log(JSON.stringify(r));
                            if(r.success){
                                //if(r.result.d_cms_artical_v1!=null){
                                    if(r.result.profiles_search!=null){
                                        
                                        bindData.products=r.result.profiles_search;
                                    }

                                    
                                if(r.result.d_cms_cards_v1!=null){
                                    bindData.cards=r.result.d_cms_cards_v1;
                                }
                                if(r.result.d_cms_cat_v1!=null){
                                    bindData.sidebar=r.result.d_cms_cat_v1;
                                }
                                bindData.loading=false;

                            }
                        })
                        .error(function(error){
                            //bindData.Articals=[];
                            bindData.loading=false;
                            console.log(error.responseJSON);
            });
        }

    exports.vue = vueData;
    exports.onReady = function(){
        
        

    }

    
});
