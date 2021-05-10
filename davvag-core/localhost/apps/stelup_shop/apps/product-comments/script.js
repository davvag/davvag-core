WEBDOCK.component().register(function(exports){
    var scope,validator_profile,service_handler,sossrout_handler,call_handler,complete_call;

    var bindData = {
        submitErrors : [],submitInfo : [],data:{},comments:[],comment:"",profile:localStorage.profile?JSON.parse(localStorage.profile):{id:0}
    };

    var vueData =  {
        methods:{
            send:submit
           
        },
        data :bindData,
        onReady: function(s,c){
            scope=s;
            call_handler=c;
            complete_call=call_handler.completedEvent?call_handler.completedEvent:null;
            if(c.data){
                bindData.data=c.data;
            }
            initialize();
        },
        computed:{
            TextLength:function(){
                return this.comment.length;
            }
        }
    }

    function initialize(){
        service_handler = exports.getComponent("app-handler");
        if(!service_handler){
            console.log("Service has not Loaded please check.")
        }
        
        service_handler.services.AllComments({id:bindData.data.itemid}).then(function(result){

            
            if(result.success){
                bindData.comments=result.result;
            }else{
                lockForm();
            }
            unlockForm();
        }).error(function(result){
            console.log(e);
            
        });
        loadValidator();
    }

    function submit(c_input){
        lockForm();
        scope.submitErrors = [];
        scope.submitErrors = validator_profile.validate(); 
        if (!scope.submitErrors){
            lockForm();
            scope.submitErrors = [];
            scope.submitInfo=[];
            //data={}
            data ={itemid:bindData.data.itemid,pid:bindData.profile.id,comment:bindData.comment}
            service_handler.services.Comment(data).then(function(result){
                
                //console.log(result);
                
                if(result.success){
                    bindData.comment="";
                    bindData.comments.push(result.result);
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
        $("#form-row :input").prop("disabled", true);
        $("#form-row :textarea").prop("disabled", true);
        $("#form-row :button").prop("disabled", true);
    }

    function unlockForm(){
        $("#form-row :input").prop("disabled", false);
        $("#form-row :textarea").prop("disabled", false);
        $("#form-row :button").prop("disabled", false);
    }

    function loadValidator(){
        var validatorInstance = exports.getShellComponent ("soss-validator");

        validator_profile = validatorInstance.newValidator (scope);
        

        
        
    }

    exports.vue = vueData;
    exports.onReady = function(element){
        
    }

});
