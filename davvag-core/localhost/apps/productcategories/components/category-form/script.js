WEBDOCK.component().register(function(exports){
    var scope;
    var handler;
    var pInstance, validatorInstance;
    var routeData;
    
    var bindData = {
        product:{},
        submitErrors: undefined
    };

    var validator;
    function loadValidator(){
        validator = validatorInstance.newValidator (scope);
        validator.map ("product.name",true, "You should enter a name");
    }

    function submit(){
        $('#send').prop('disabled', true);
        scope.submitErrors = validator.validate(); 
        if (!scope.submitErrors){

            var promiseObj;
            if (routeData.catid) promiseObj = handler.transformers.updateCategory (bindData.product);
            else promiseObj = handler.transformers.insertCategory (bindData.product);

            promiseObj
            .then(function(){
                gotoProducts();
            })
            .error(function(){

            });
        }else{
            $('#send').prop('disabled', false);
        }
    }

    function gotoProducts(){
        handler1 = exports.getShellComponent("soss-routes");
        handler1.appNavigate("..");
    }

    function loadCategory(scope){
        if (routeData.catid)
        handler.transformers.getCategoryById(routeData.catid)
        .then(function(result){
            if (result.result.length !=0){
                scope.product = result.result[0];
            }
        })
        .error(function(){
    
        });
    }

    var vueData =   {
        methods: {
            submit: submit,
            gotoProducts: gotoProducts,
            navigateBack: function(){
                handler1 = exports.getShellComponent("soss-routes");
                handler1.appNavigate("..");
            }
        },
        data : bindData,
        onReady: function(s){
            scope = s;
        
            exports.getAppComponent("productapp","product", function(hand){
                handler = hand;
                pInstance = exports.getShellComponent("soss-routes");
                validatorInstance = exports.getShellComponent ("soss-validator");
                routeData = pInstance.getInputData();
                loadValidator();
                if (routeData)
                    loadCategory(scope);
            });
        }
    }

    exports.vue = vueData;
    exports.onReady = function(element){
    }
});
