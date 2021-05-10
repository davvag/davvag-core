WEBDOCK.component().register(function(exports){
    var pInstance;
    var routeData;
    var validatorInstance;
    var handler;
    var bindData = {
        product:{},
        image:'',
        categories:[],
        uoms: [],
        submitErrors: undefined
    };

    var vueData = {
        onReady: function(s){
            initializeComponent();
        },
        data:bindData,
        methods: {
            submit:submit,
            clear:clearProfile,
            searchItems:searchItems,
            createImage:createImage ,
            removeImage: removeImage,
            onFileChange: function(e) {
                var files = e.target.files || e.dataTransfer.files;
                if (!files.length)
                    return;
                this.createImage(files[0]);
            },
            navigateBack: function(){
                handler1 = exports.getShellComponent("soss-routes");
                handler1.appNavigate("..");
            }
        }
    }

    exports.vue = vueData;

    exports.onReady = function(element){
    }
    //var catogoryid ={"Staff",""};
    //var item ={};
    
    function initializeComponent(){
        pInstance = exports.getShellComponent("soss-routes");
        routeData = pInstance.getInputData();
        validatorInstance = exports.getShellComponent("soss-validator");
        producthandler = exports.getComponent("product");
        uomhandler = exports.getComponent("uom-handler");
        loadValidator();
        productHandler = exports.getAppComponent("uom", "uom-handler", function(i){
            uomhandler = i;
            uomhandler.transformers.allUom()
            .then(function(result){
                for (var i=0;i<result.result.length;i++)
                bindData.uoms.push(result.result[i]["symbol"]);
            })
            .error(function(){

            });
        });
        uploaderInstance = exports.getShellComponent("soss-uploader");

        if(routeData!=null){
            loadProduct(bindData);
        }

        loadInitialData();
        console.log(routeData);
        
    }

    var newFile;
    function uploadFile(productId, cb){
        if (!newFile)cb();
        else{
            uploaderInstance.services.uploadFile(newFile, "products", productId)
            .then(function(result){
                cb();
            })
            .error(function(){
                cb();
            });
        }
    }

    function removeImage(e) {
        bindData.image = '';
    }

    function createImage(file) {
        newFile = file;
        var image = new Image();
        var reader = new FileReader();

        reader.onload = function (e) {
            bindData.image = e.target.result;
        };

        reader.readAsDataURL(file);
    }

    function clearProfile(){
        bindData.item={};
        showSearch=false;
    }

    function loadInitialData(){
        producthandler.transformers.allCategories()
        .then(function(result){
            for (var i=0;i<result.result.length;i++)
            bindData.categories.push(result.result[i].name);
        })
        .error(function(){

        });

        
    }

    function loadValidator(){
        validator = validatorInstance.newValidator (bindData);
        validator.map ("product.name",true, "You should enter a name");
        validator.map ("product.caption",true, "You should enter a caption");
        validator.map ("product.price",true, "You should endter a price");
        validator.map ("product.price","number", "Price should be a number");
        validator.map ("product.catogory",true, "You should select a product category");
        validator.map ("image",true, "You should upload an image");
    }

    function submit(){
        $('#send').prop('disabled', true);

   
        bindData.submitErrors = validator.validate(); 
        if (!bindData.submitErrors){
            var productId =0;
            var promiseObj;
            if (bindData.product.itemid) {
                productId=bindData.product.itemid;
                promiseObj = producthandler.transformers.updateProduct(bindData.product);
            }
            else promiseObj = producthandler.transformers.insertProduct(bindData.product);

            promiseObj
            .then(function(result){
                if(result.result.generatedId!=0){
                    productId = result.result.generatedId;
                }
                uploadFile(productId, function(){
                    gotoProducts();
                });
            })
            .error(function(){
                
            });
        }else{
            $('#send').prop('disabled', false);
        }
    }

    function loadProduct(scope){
        //console.log(routeData.productid);
        producthandler.transformers.getById(routeData.productid)
        .then(function(result){
            console.log(result);
            if (result.result.length !=0){
                bindData.product = result.result[0];
                bindData.image = 'components/dock/soss-uploader/service/get/products/' + bindData.product.itemid;
                //console.log(bindData.product);
            }
        })
        .error(function(){

        });
    }

    function gotoProducts(){
        //location.href = "#/admin-allproducts";
        handler1 = exports.getShellComponent("soss-routes");
        handler1.appNavigate("..");
    }

    function searchItems(columncode,columnvalue){
        console.log(bindData.items)
        profileHandler.services.Search({q:columncode+":"+columnvalue})
        .then(function(response){
            console.log(JSON.stringify(response));
            if(response.success){
                //console
                //bindData.item.id=response.result.result.generatedId;
                console.log(response);
                if(response.result.length!=0){
                    console.log("items chnaged");
                    //bindData.items=response.result;
                /*
                    response.result.forEach(element => {
                        //var o=;
                        //if(bindData.items.includes(element)){
                            
                        var found=false;
                        bindData.items.forEach(searchEl => {
                                if(searchEl.id==element.id){
                                    found=true;
                                }
                            
                          });  
                          if(!found){
                            bindData.items.push(element);
                          }
                            
                        //}
                    });*/
                    bindData.items=response.result;
                    bindData.showSearch=true;
                    console.log(JSON.stringify(bindData.items));
                }
            }else{
                alert (response.error);
            }
        })
        .error(function(error){
            alert (error.responseJSON.result);
            console.log(error.responseJSON);
        });
    }

    function getAllProductsThroughService(){
        productHandler.services.allProducts()
        .then(function(response){
            vueData.data.products = response.result;
        })
        .error(function(error){

        });
    }
});
