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
        allloaded:false,
        itemCount:0
    };

    var items=[];
    if(sessionStorage.items){           
        items=JSON.parse(sessionStorage.items);
        for (var i=0;i<items.length;i++){
            var item = items[i];
            bindData.itemCount += item.qty;
        }
        //services.topMenu.additems(undefined,bindData.itemCount);              
    }
   
    

    function loadproducts(){
       
        var routId   = exports.getShellComponent("soss-routes");
        routeData = routId.getInputData();
        var menuhandler  = exports.getComponent("productsvr");
            //var query=[{storename:"products",search:""}];
        if(menuhandler){
            
            try{
                bindData.loading=true;
                menuhandler.services.allProducts({page:page.toString(), size:size.toString(),cat:routeData.cat==null?"0":routeData.cat ,q:bindData.q})
                        .then(function(r){
                            console.log(JSON.stringify(r));
                            if(r.success){
                                if(r.result.length==0){
                                    bindData.allloaded=true;
                                }else{
                                    bindData.allloaded=false;
                                }
                                
                                var i;
                                for (i = 0; i < r.result.length; i++) {
                                    //text += cars[i] + "<br>";
                                    bindData.products.push(r.result[i]);
                                }
                                if(bindData.products.length==0){
                                    bindData.noproducts=true;
                                }else{
                                    bindData.noproducts=false;
                                }
                                //bindData.loading=false;
                                page=page+bindData.products.length;
                            }
                        })
                        .error(function(error){
                            //bindData.products=[];
                            //bindData.loading=false;
                            bindData.allloaded=false;
                            //page=
                            console.log(error.responseJSON);
                    });
                
            }catch(e){
                console.log(e);
            }finally{
                bindData.loading=false;
            }
            
        }
    }
    var vueData =  {
        methods:{
        
        selectStore: function(p){
            bindData.product=p;
            $('#modalImagePopup').modal('show');
        }, 
        navProfile: function(id){
            window.location="#/app/com_qti_students/profile?id="+id.toString();
        },
        navDonate: function(id){
            window.location="#/app/com_qti_students/donate?id="+id.toString();
        }
        ,selectStoreClose: function(){
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
        },
        additem:function(item,isOrder){
            items=[];
            if(sessionStorage.items){           
                items=JSON.parse(sessionStorage.items);
            }
            x=0;
            for(i in items){
                if(items[i].itemid===item.itemid){
                    items[i].qty++;
                    items[i].isOrder = isOrder;
                    sessionStorage.items=JSON.stringify(items);
                    bindData.itemCount =0;
                    for (var i=0;i<items.length;i++){
                        var it = items[i];
                        bindData.itemCount += it.qty;
                    }
                    //services.topMenu.additems(items[i],bindData.itemCount);
                    $('#modalImagePopup').modal('toggle');
                    return;
                }
                x++;
            }
            item.qty=1;
            item.isOrder = isOrder;
            items.push(item);
            sessionStorage.items=JSON.stringify(items);
            bindData.itemCount =0;
            for (var i=0;i<items.length;i++){
                var it = items[i];
                bindData.itemCount += it.qty;
            }
            //services.topMenu.additems(item,bindData.itemCount);
            $('#modalImagePopup').modal('toggle');
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
            formatMoney:function(v){
                return v==null?"0.00":(v).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            },
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
