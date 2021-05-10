WEBDOCK.component().register(function(exports){
    var scope;
    var handler;
    var pInstance, validatorInstance;
    var routeData;
    
    var bindData = {
        item:{},
        submitErrors: undefined
    };

    var validator;
    function loadValidator(){
        validator = validatorInstance.newValidator (scope);
        validator.map ("item.name",true, "You should enter a name");
        validator.map ("item.symbol",true, "You should enter a symbol");
    }

    function submit(){
        $('#send').prop('disabled', true);
        scope.submitErrors = validator.validate(); 
        if (!scope.submitErrors){

            var promiseObj;
            if (routeData.uomid) promiseObj = handler.transformers.updateUom (bindData.item);
            else promiseObj = handler.transformers.insertUom (bindData.item);

            promiseObj
            .then(function(){
                gotoUom();
            })
            .error(function(){

            });
        }else{
            $('#send').prop('disabled', false);
        }
    }

    function gotoUom(){
        handler1 = exports.getShellComponent("soss-routes");
        handler1.appNavigate("..");
    }

    function loadCategory(scope){
        if (routeData.uomid)
        handler.transformers.getUomById(routeData.uomid)
        .then(function(result){
            if (result.result.length !=0){
                scope.item = result.result[0];
            }
        })
        .error(function(){
    
        });
    }

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
            handler = exports.getComponent("uom-handler");
            pInstance = exports.getShellComponent("soss-routes");
            validatorInstance = exports.getShellComponent ("soss-validator");
            routeData = pInstance.getInputData();
            loadValidator();
            if (routeData)
                loadCategory(scope);
            $("body").css("background-image","none");
        }
    }

    exports.vue = vueData;
    exports.onReady = function(element){
    }
});
