WEBDOCK.component().register(function(exports){
    var scope,validator_profile,service_handler,sossrout_handler;

    var bindData = {
        submitErrors : [],submitInfo : [],data:{},stripe_pulich_key:"pk_test_wkKAU02B62x9Rut6udRmiuHa"
    };

    var vueData =  {
        methods:{
            submit:stripePay
           
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
        loadValidator();
        
    }

    function stripePay(){
        lockForm();
        Stripe.setPublishableKey(bindData.stripe_pulich_key);
        Stripe.createToken({
            number: bindData.data.cardnumber,
            cvc: bindData.data.cvc,
            exp_month: bindData.data.month,
            exp_year: bindData.data.year
        }, function(status,response){
            if (response.error) {
                
                scope.submitErrors.push(response.error.message);
                unlockForm();
            } else {
                //get token id
                var token = response['id'];
                console.log(JSON.stringify(response));
                bindData.data.token=token;
                service_handler.services.ChargeAmountFromCard({token:response['id'],id:45}).then(function(result){
                
                    console.log(result);
                    
                    if(result.success){
                        scope.submitInfo.push("result.result.message");
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
        });
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
                    scope.submitInfo.push("result.result.message");
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
        validator_profile.map ("data.cardnumber",true, "Please enter your card number");
        validator_profile.map ("data.name",true, "Please enter name appers on your card");
        validator_profile.map ("data.cvc",true, "cvc number on the back of the card");
        validator_profile.map ("data.year",true, "Please Select your Expiry Year");
        validator_profile.map ("data.month",true, "Please Select your Expiry Month");

        
        
    }

    exports.vue = vueData;
    exports.onReady = function(element){
        
    }

});
