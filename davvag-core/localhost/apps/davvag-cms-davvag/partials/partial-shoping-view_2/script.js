WEBDOCK.component().register(function(exports){
    var page=0;
    var size=40;
    //var q
    //document.body.addEventListener('scroll', loadproducts);
    var bindData={
        products:[],
        product:{caption:""},
        q:"",
        loading:false,
        noproducts:false,
        allloaded:false
    };
    var vueData =  {
        methods:{
        selectStore: function(p){
            bindData.product=p;
            $('#modalImagePopup').modal('show');
        },selectStoreClose: function(){
            //bindData.product=p;
            $('#modalImagePopup').modal('toggle');
        },handleScroll (event) {
            // Any code to be executed when the window is scrolled
            console.log(event);
          }, 
        onSearch () {
            if(bindData.q!=""){
                page=0;
                bindData.allloaded=false;
                bindData.products=[];
                loadproducts();
            }
        },
        OnkeyEnter: function(e){
            bindData.noproducts=false;
            if (e.keyCode === 13) {
                if(bindData.q!=""){
                    page=0;
                    bindData.products=[];
                    loadproducts();
                }
            }
        }
        },
        data :bindData
        ,
        onReady: function(s){
           
            loadproducts();
            window.document.body.onscroll = function(e) {
        
                //console.log(window.document.body);
                //console.log("test  " + (window.innerHeight + window.scrollY) +" yo " +document.body.offsetHeight);
                if ((window.innerHeight + window.scrollY+30) >= document.body.offsetHeight) {
                    // you're at the bottom of the page
                    console.log("In the event ...");
                    if(!bindData.allloaded && !bindData.loading){
                        //page=page+size;
                        loadproducts();
                        console.log("Bottom of the page products " +bindData.products.length +" pageNumber "+page);
                    }
                }
                //loadproducts();
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

    

    function loadproducts(){
       

        bindData.loading=true;
        var menuhandler  = exports.getComponent("productsvr");
            //var query=[{storename:"products",search:""}];
            menuhandler.services.allProducts({page:page, size:size+"&page="+page+"&q=", q:bindData.q})
                        .then(function(r){
                            console.log(JSON.stringify(r));
                            if(r.success){
                                if(r.result.length==0){
                                    bindData.allloaded=true;
                                }else{
                                    bindData.allloaded=false;
                                }
                                r.result.forEach(element => {
                                    bindData.products.push(element);
                                });
                                if(bindData.products.length==0){
                                    bindData.noproducts=true;
                                }else{
                                    bindData.noproducts=false;
                                }
                                bindData.loading=false;
                                page=page+40;
                            }
                        })
                        .error(function(error){
                            //bindData.products=[];
                            bindData.loading=false;
                            bindData.allloaded=false;
                            //page=
                            console.log(error.responseJSON);
            });
    }

    exports.vue = vueData;
    exports.onReady = function(){
        
        

    }

    
});
