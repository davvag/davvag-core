WEBDOCK.component().register(function(exports){
    var scope;
    var handler;
    var pInstance, validatorInstance;
    var routeData;
    
    var bindData = {
        product:{BType:"Top",parentButtonid:0},
        parentButtons:[],
        content:""

    };

    var vueData =   {
        methods: {
            submit: submit,
            gotoUom: gotoUom,
            navigateBack: function(){
                handler1 = exports.getShellComponent("soss-routes");
                handler1.appNavigate("..");
            }
        },
        data : bindData,
        onReady : function(s){
            scope = s;
            handler = exports.getComponent("cms-gapp-handler");
            pInstance = exports.getShellComponent("soss-routes");
            validatorInstance = exports.getShellComponent ("soss-validator");
            routeData = pInstance.getInputData();
            loadValidator();
            var menuhandler  = exports.getShellComponent("soss-data");
            var query=[{storename:"d_cms_cat_v1",search:"parentButtonid:0"}];
            //var tmpmenu=[];
            bindData.TopButtons=[];
            menuhandler.services.q(query)
                        .then(function(r){
                            console.log(JSON.stringify(r));
                            if(r.success){
                                //if(r.result.d_cms_artical_v1.length!=0)
                                bindData.parentButtons=r.result.d_cms_cat_v1;
                            }
                        })
                        .error(function(error){
                            //bindData.product={title:"Artical Not found or Internal query Erorr",content:"Please refresh or navigate back"};
                            console.log(error.responseJSON);
            });
            if (routeData)
                loadCategory(scope);
            //$('#editor').wysiwyg();
        }
    }

    function loadCategory(scope){
        if (routeData.id)
        handler.transformers.getcatbyid(routeData.id)
        .then(function(result){
            if (result.result.length !=0){
                bindData.product = result.result[0];
                /*bindData.product.Name=unescape(bindData.product.Name);
                bindData.product.Caption=unescape(bindData.product.Caption);*/
            }
        })
        .error(function(){
    
        });
    }

    var validator;
    function loadValidator(){
        validator = validatorInstance.newValidator (scope);
        validator.map ("product.Name",true, "You should enter a name");
        validator.map ("product.url",true, "You should enter a url");
    }

    function submit(){
        $('#send').prop('disabled', true);
        scope.submitErrors = validator.validate(); 
        var product =bindData.product;
        /*product.Name=escape(bindData.product.Name);
        product.Caption=escape(bindData.product.Caption);*/
        if (!scope.submitErrors){

            var promiseObj;
            if (routeData.id) promiseObj = handler.transformers.updateCat (product);
            else promiseObj = handler.transformers.insertCat (product);

            promiseObj
            .then(function(){
                gotoUom();
            })
            .error(function(){
                $('#send').prop('disabled', false);
            });
        }else{
            $('#send').prop('disabled', false);
        }
    }

    function gotoUom(){
        handler1 = exports.getShellComponent("soss-routes");
        handler1.appNavigate("..");
    }


    exports.vue = vueData;
    exports.onReady = function(element){
        
    }
});
