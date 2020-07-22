WEBDOCK.component().register(function(exports){
    var scope;
    var handler;
    var pInstance, validatorInstance;
    var routeData;
    
    var bindData = {
        product:{name:"",caption:"",title:"",imguri:"",buttoncaption:"",buttonuri:""},
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
            
            //var menuhandler  = exports.getShellComponent("soss-data");
           // var promiseObj = handler.services.Settings({name:"cms-global"});
            //else promiseObj = handler.transformers.insertArtical (bindData.product);
            handler.services.Settings({name:"cms-global"})
            .then(function(result){
                if(result.result){
                    bindData.product=result.result;
                    console.log("body");
                    console.log(bindData.product);
                }
                //gotoUom();
            })
            .error(function(){
                //$('#send').prop('disabled', false);
            });
            loadValidator();
        }
    }

    function loadCategory(scope){
        if (routeData.id)
        handler.transformers.getButtonbyid(routeData.id)
        .then(function(result){
            if (result.result.length !=0){
                bindData.product = result.result[0];
            }
        })
        .error(function(){
    
        });
    }

    var validator;
    function loadValidator(){
        validator = validatorInstance.newValidator (scope);
        //validator.map ("product.Name",true, "You should enter a name");
        //validator.map ("product.url",true, "You should enter a url");
    }

    function submit(){
        $('#send').prop('disabled', true);
        scope.submitErrors = validator.validate(); 
        if (!scope.submitErrors){
            var saveObject={name:"cms-global",body:bindData.product}
            var promiseObj = handler.services.saveSettings(saveObject);
            //else promiseObj = handler.transformers.insertArtical (bindData.product);
            

            promiseObj
            .then(function(result){
                //uploadFile(promiseObj.)
                /*
                uploadFile(result.result.id, function(){
                    gotoUom();
                });*/
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
