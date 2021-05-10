WEBDOCK.component().register(function(exports){
    var scope,validator_profile,service_handler,sossrout_handler;

    var bindData = {
        submitErrors : [],submitInfo : [],data:{}
    };

    var vueData =  {
        methods:{
            submit:submit
           
        },
        data :bindData,
        onReady: function(s){
            scope=s;
            initialize();
        }
    }

    function initialize(){
        service_handler = exports.getComponent("app-handler");
        if(!service_handler){
            console.log("Service has not Loaded please check.")
        }
        service_handler.services.PublicToken({"id":"0"}).then(function(result){
                
            
            if(result.success){
                if(result.result!=null)
                    bindData.data=result.result;
                //scope.submitInfo.push("Settings saved successfully.");
            }else{
                scope.submitErrors.push("Error");
            }
            unlockForm();
        }).error(function(result){
            scope.submitErrors = [];
            bindData.submitErrors.push("Error");
            unlockForm();
        });
        loadValidator();
    }

    function submit(){
        lockForm();
        scope.submitErrors = [];
        scope.submitErrors = validator_profile.validate(); 
        if (!scope.submitErrors){
            lockForm();
            scope.submitErrors = [];
            scope.submitInfo=[];
            service_handler.services.Save(bindData.data).then(function(result){
                
                console.log(result);
                
                if(result.success){
                    scope.submitInfo.push("Settings saved successfully.");
                }else{
                    scope.submitErrors.push("Error");
                }
                unlockForm();
            }).error(function(result){
                scope.submitErrors = [];
                bindData.submitErrors.push("Error");
                unlockForm();
            });

        }
    }

    function navigateBack(){

    }

    

    function lockForm(){
        $("#form-details :input").prop("disabled", true);
        $("#form-details :button").prop("disabled", true);
    }

    function unlockForm(){
        $("#form-details :input").prop("disabled", false);
        $("#form-details :button").prop("disabled", false);
    }

    function loadValidator(){
        var validatorInstance = exports.getShellComponent ("soss-validator");

        validator_profile = validatorInstance.newValidator (scope);
        validator_profile.map ("data.mainkey",true, "Please enter Merchant ID.");
        validator_profile.map ("data.appkey",true, "Please enter the application key.");

        
        
    }

    exports.vue = vueData;
    exports.onReady = function(element){
        
    }

});
