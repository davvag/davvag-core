WEBDOCK.component().register(function(exports){
    var scope,validator_profile,service_handler,sossrout_handler,callHandler,cbcomplete;

    var bindData = {
        submitErrors : [],submitInfo : [],data:{},showtitle:true
    };

    var vueData =  {
        methods:{
            submit:submit
           
        },
        data :bindData,
        onReady: function(s,c){
            scope=s;
            callHandler=c;
            if(callHandler.completedEvent){
                bindData.showtitle=false;
                cbcomplete=callHandler.completedEvent;
            }
            initialize();
        }
    }

    function initialize(){
        service_handler = exports.getComponent("login-handler");
        if(!service_handler){
            console.log("Service has not Loaded please check.");
        }
        loadValidator();
    }

    function submit(){
        lockForm();
        scope.submitErrors = [];
        scope.submitErrors = validator_profile.validate(); 
        if(scope.data.newpassword!=scope.data.changepassword){
            if(scope.submitErrors){
                scope.submitErrors.push("Password Miss Match.");
            }else{
                scope.submitErrors=["Password Miss Match."];
            }
        }
        if (!scope.submitErrors){
            lockForm();
            scope.submitErrors = [];
            scope.submitInfo=[];
            service_handler.services.ChangePassword(bindData.data).then(function(result){
                //console.log(result);
                
                if(result.success){
                    if(result.result.error){
                        scope.submitErrors.push(result.result.message);
                    }else{
                        if(cbcomplete){
                            cbcomplete(result);
                        }
                        scope.submitInfo.push("Successfuly Changed.");
                        scope.data.password=scope.data.newpassword=scope.data.changepassword="";
                        
                    }
                    
                }else{
                    scope.submitErrors.push("Error Chnaging Password");
                }
                unlockForm();
            }).error(function(result){
                scope.submitErrors = [];
                bindData.submitErrors.push("Error");
                unlockForm();
            });

        }else{
            unlockForm();
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
        validator_profile.map ("data.password",true, "Please enter your Old  password");
        validator_profile.map ("data.newpassword",true, "Please enter your New  password");
        validator_profile.map ("data.changepassword",true, "Please enter your New  Confirm Password");


        
        
    }

    exports.vue = vueData;
    exports.onReady = function(element){
        
    }

});
