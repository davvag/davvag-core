WEBDOCK.component().register(function(exports){
    var page=0;
    var size=40;
    var menuhandler,apploader;
    //var q
    //document.body.addEventListener('scroll', loadproducts);
   

    var bindData={
        products:[],
        q:"",
        loading:false,
        noproducts:false,
        allloaded:false,
        a5:0,
        profile:localStorage.profile!=null?JSON.parse(localStorage.profile):null,
        showstore:false,
        showshare:false,
        appTitle:"",
        appIcon:""
    };
    function calqty(){
        var items=[];
        if(sessionStorage.items){           
            items=JSON.parse(sessionStorage.items);
            for (var i=0;i<items.length;i++){
                var item = items[i];
                bindData.a5 += item.qty;
            }
            //services.topMenu.additems(undefined,bindData.a5);              
        }
    }   
   
    function rechecktable(){
        var e  = exports.getShellComponent("soss-data");
        //e.services.q(query);
        var query=[];
        query.push({storename:"products_likes",search:"pid:0"},{storename:"products_favorites",search:"pid:0"});
        e.services.q(query).then(function(r){
            console.log(r);
        }).error(function(e){
            console.log(e);
        });
    }
    //var firstLoad=true;
    function loadproducts(){
       
        var routId   = exports.getShellComponent("soss-routes");
        routeData = routId.getInputData();
        calqty();
            //var query=[{storename:"products",search:""}];
        if(menuhandler){
            
            try{
                bindData.loading=true;
                menuhandler.services.allProducts({page:page.toString(), size:size.toString(),pid:bindData.profile==null?"0":bindData.profile.id.toString(),q:bindData.q})
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
                            //firstLoad=false;
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
    function completeResponce(d){
        console.log(e);
    }

    function goLogin(){
        //$('#modalImagePopup').modal('hide');
        //$('#modalImagePopup').on('hidden.bs.modal', function (e) {
        window.location="#/app/userapp/?u=#/app/stelup_shop";
        //});s
    }
    function additem(item,isOrder){
        items=[];
        if(sessionStorage.items){           
            items=JSON.parse(sessionStorage.items);
        }
        x=0;
        item.qty=1;
        for(i in items){
            if(items[i].itemid===item.itemid){
                items[i].qty++;
                items[i].isOrder = isOrder;
                sessionStorage.items=JSON.stringify(items);
                bindData.a5 =0;
                for (var i=0;i<items.length;i++){
                    var it = items[i];
                    bindData.a5 += it.qty;
                }
                //services.topMenu.additems(items[i],bindData.a5);
                
                return;
            }
            x++;
        }
        //item.qty=1;
        item.isOrder = isOrder;
        items.push(item);
        sessionStorage.items=JSON.stringify(items);
        bindData.a5 =0;
        for (var i=0;i<items.length;i++){
            var it = items[i];
            bindData.a5 += it.qty;
        }
        //services.topMenu.additems(item,bindData.a5);
        //$('#modalImagePopup').modal('toggle');
    }
    var vueData =  {
        methods:{
        edit:function(i){
            //$('#modalImagePopup').modal('toggle');
            //$('#modalImagePopup').on('hidden.bs.modal', function (e) {
            window.location="#/app/stelup_shop/itemonboard?id="+i.itemid.toString();
            //});
            //window.location="#/app/stelup_shop/itemonboard?id="+i.itemid.toString();
        },
        downloadapp:function(appname,form,data,apptitle,appicon){
            //$('#decker1100').addClass("profile-content-show");
            bindData.appIcon=appicon;
            bindData.appTitle=apptitle;
            $('#modalappwindow').modal('toggle');
            apploader.downloadAPP(appname,form,"appdock",function(d){
                
            },function(e){
                console.log(e);
                bindData.loadingAppError=true;
            },completeResponce,data);
        },close: function(){
            //bindData.product=p;
            $('#modalappwindow').modal('toggle');
        },
        marksold:function(i){
            i.showonstore="N";
            menuhandler.services.SaveProduct(i).then(function(r){
                //bindData.data.showonstore="N";
                filteredItems = bindData.products.filter(function(a) {
                    if(a.itemid!==i.itemid)
                        return a;
                });
                bindData.products=filteredItems==null?[]:filteredItems;
                //$('#modalImagePopup').modal('toggle');
            }).error(function(e){
                console.log(e);
            });
            
            //window.location="#/app/stelup_shop/itemonboard?id="+i.itemid.toString();
        },
        like:function(i){
            if(bindData.profile){
                var like={itemid:i.itemid,pid:bindData.profile.id,liked:true};
                if(i.liked==0){
                    like.liked=true;
                }else{
                    like.liked=false;
                }
                menuhandler.services.Like(like).then(function(a){
                    if(a.success){
                        if(like.liked){
                            i.liked=1;
                        }else{
                            i.liked=0;
                        }
                    }else{
                        console.log(a);
                    }
                }).error(function(e){
                    console.log(e);
                })
                
            }else{
                goLogin();
            }
        },favorite:function(i){
            
            if(bindData.profile){
                var like={itemid:i.itemid,pid:bindData.profile.id,liked:true};
                if(i.favorite==0){
                    like.liked=true;
                }else{
                    like.liked=false;
                }
                menuhandler.services.Favorite(like).then(function(a){
                    if(a.success){
                        if(like.liked){
                            i.favorite=1;
                        }else{
                            i.favorite=0;
                        }
                        
                    }else{
                        console.log(a);
                    }
                }).error(function(e){
                    console.log(e);
                })
                
            }else{
                goLogin();
            }
        },
        isEditable:function(item){
            if(bindData.profile){
                if(bindData.profile.id==item.storeid){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        },
        selectStore: function(p){
            bindData.product=p;
            bindData.product.qty=1;
            bindData.product.url="http://"+window.location.hostname+"/components/davvag-shop/productsvr/service/Product/?q="+bindData.product.itemid.toString();
            //$('#modalImagePopup').modal('show');
        }, 
        navcheckout: function(){
            window.location="#/app/stelup_shop/checkout-cart";
        }   
        ,handleScroll (event) {
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
        additem:additem,addchecout:function(item,isorder){
            additem(item,isorder);
            window.location="#/app/stelup_shop/checkout";
        }
        },
        data :bindData
        ,
        onReady: function(s){
            menuhandler  = exports.getComponent("app-handler");
            exports.getAppComponent("davvag-tools","davvag-app-downloader", function(_uploader){
                apploader=_uploader;
                apploader.initialize();
            });
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

    exports.vue = vueData;
    
    exports.onReady = function(){
        
        

    }


});
