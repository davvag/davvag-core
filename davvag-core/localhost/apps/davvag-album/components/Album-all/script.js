WEBDOCK.component().register(function(exports){
    var scope;

    function loadUoms(skip, take){
        var handler = exports.getComponent("cms-album-handler");
        
        handler.transformers.allalbums()
        .then(function(result){
            //bindData.product.title=unescape(bindData.product.title);
            vueData.data.items = result.result;
        })
        .error(function(){
    
        });
    }

    var vueData =  {
        methods:{
            navigate: function(id){
                handler = exports.getShellComponent("soss-routes");
                handler.appNavigate(id ? "../album?id=" + id : "../album");
            },
            DeleteItem:Delete
        },
        data :{
            items : []
        },
        onReady: function(s){
            scope = s;
            loadUoms(0,100);
        },
        filter:{
            foramtdata:function(v){
                var newdata=unescape(v);
                console.log(newdata);
                return newdata;
            }
        }
    } 

    exports.vue = vueData;
    exports.onReady = function(element){
    }

    function Delete(i){
        var handler = exports.getComponent("cms-album-handler");
        handler.services.DeleteAlbum(i)
        .then(function(r){
            console.log(JSON.stringify(r));
            if(r.success){
                filteredItems = vueData.data.items.filter(function(item) {
                    if(item.id!==i.id)
                        return item;
                });
                vueData.data.items=filteredItems==null?[]:filteredItems;
            }
        })
        .error(function(error){
            //bindData.products=[];
            console.log(error.responseJSON);
        });
        
    }
});
