WEBDOCK.component().register(function(exports){
    var bindData = {
        submitErrors: undefined,
        SearchItem:"",
        SearchColumn:"name",
        items:[],
        image:''
    };

    var vueData = {
        onReady: function(s){
            initializeComponent();
        },
        data:bindData,
        methods: {
            searchItems:searchItems,
            navigate: function(id){
                handler = exports.getShellComponent("soss-routes");
                handler.appNavigate(id ? "/product?productid=" + id : "/product");
            }
        }
    }
    
    exports.vue = vueData;
    exports.onReady = function(element){
    }
    //var catogoryid ={"Staff",""};
    //var item ={};
    
    var handler;

    function initializeComponent(){
        handler = exports.getComponent("product");
        searchItems("","");
    }

    

    

    function searchItems(columncode,columnvalue){
        console.log(encodeURI(columncode+":"+columnvalue))
        handler.services.allProducts()
        .then(function(response){
            console.log(JSON.stringify(response));
            if(response.success){
                //console
                //bindData.item.id=response.result.result.generatedId;
                bindData.items=[];
                bindData.items=response.result;
                
                console.log(response);
                
            }else{
                alert (response.error);
            }
        })
        .error(function(error){
            alert (error.responseJSON.result);
            console.log(error.responseJSON);
        });
    }


});
