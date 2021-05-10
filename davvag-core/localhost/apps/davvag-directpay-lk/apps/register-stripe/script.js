WEBDOCK.component().register(function(exports){
    var scope,validator_profile,service_handler,routeData,complete_call,call_handler;

    var bindData = {
        submitErrors : [],submitInfo : [],data:{},stripe_pulich_key:"",order:{},linked:4,header:sessionStorage.blogheader?JSON.parse(sessionStorage.blogheader):{buttonuri:"/#/home"},css_main:"input-form"
    };

    var vueData =  {
        methods:{
            submit:stripePay,
            escape:escape,
            change:function(){bindData.linked=0;}
           
        },
        data :bindData,
        onReady: function(s,c){
            lockForm()
            scope=s;
            call_handler=c;
            complete_call=call_handler.completedEvent?call_handler.completedEvent:null;
            //c.data
            if(c.data.css!=null){
                bindData.css_main=c.data.css;
            }
            if(c.data!=null){
                bindData.data=c.data;
                
                bindData.data.courncycode="gbp";
                bindData.data.amount=0.50;
                loadValidator();
                initialize();
            }else{
                if(localStorage.profile){
                    bindData.data=JSON.parse(localStorage.profile)
                    bindData.data.courncycode="gbp";
                    bindData.data.amount=0.50;
                    loadValidator();
                    initialize();
                }else{
                    scope.submitErrors.push("UnAutherized");
                }
            }
            
        }
    }

    function initialize(){
        service_handler = exports.getComponent("app-handler");
        if(!service_handler){
            scope.submitErrors.push("Service has not Loaded please check.");
            
        }
        pInstance = exports.getShellComponent("soss-routes");
        routeData = pInstance.getInputData();
        service_handler.services.PublicToken({id:bindData.data.id}).then(function(result){
            if(result.success){
                if(result.result!=null){
                    scope.linked=2;
                    scope.data.stripeKey=result.result;
                    scope.data.stripeSecretKey="******";
                    unlockForm();
                }else{
                    scope.linked=0;
                    unlockForm();
                }
            }else{
                scope.submitErrors.push("Error while loading Tokens");
                unlockForm();
            }
            
        }).error(function(result){
            scope.submitErrors = [];
            bindData.submitErrors.push("Error");
            unlockForm();
        });
        
        
    }

    function escape(){
        if(complete_call){
            complete_call(scope.data);
        }
    }
    function stripePay(){
        scope.submitErrors = [];
        scope.submitErrors = validator_profile.validate(); 
        if (!scope.submitErrors){
            lockForm();
            Stripe.setPublishableKey(bindData.data.stripeKey);
            Stripe.createToken({
                number: bindData.data.cardnumber,
                cvc: bindData.data.cvc,
                exp_month: bindData.data.month,
                exp_year: bindData.data.year
            }, function(status,response){
                scope.submitErrors = [];
                if (response.error) {
                    
                    scope.submitErrors.push(response.error.message);
                    unlockForm();
                } else {
                    //get token id
                    var token = response['id'];
                    console.log(JSON.stringify(response));
                    bindData.data.token=token;
                    service_handler.services.TestChargeAmountFromCard(bindData.data).then(function(result){
                    
                        console.log(result);
                        
                        if(result.success){
                            
                            if(complete_call){
                                complete_call(scope.data);
                            }
                            scope.linked=3;
                            scope.submitInfo.push("Strip Account Linked");
                            
                        }else{
                            scope.submitErrors.push("Error");
                        }
                        
                    }).error(function(result){
                        scope.submitErrors = [];
                        bindData.submitErrors.push("Error");
                        unlockForm();
                    });
                    
                }
            });
        }
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
        validator_profile.map ("data.stripeSecretKey",true, "Please Enter Stripe Publishable Key");
        validator_profile.map ("data.stripeKey",true, "Please Enter Stripe Secret Key");

        
        
    }

    exports.vue = vueData;
    exports.onReady = function(element){
        
    }

});
