WEBDOCK.component().register(function(exports){
    var scope,validator_profile,service_handler,sossrout_handler,call_handler,complete_call;
    var p=0;
    var s=40;
    var l=0;

    var bindData = {
        submitErrors : [],submitInfo : [],data:{},messages:[],message:"",messagetype:"text",profile:localStorage.profile?JSON.parse(localStorage.profile):{id:0}
    };

    var vueData =  {
        methods:{
            send:submit,
            scrollToElement() {
                const el = this.$el.getElementsByClassName('.comments-box')[0];
            
                if (el) {
                  el.scrollIntoView();
                }
              },
            textOnChage:function(){
                var element = document.getElementById("message_data");
                $("#message_data").animate({ scrollTop: element.scrollHeight }, 1000);
            }
           
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
                return this.message.length;
            }
        },
        mounted() {
            //this.scrollToElement();
        }
    }
    scrolled =false;
    function updateScroll(){
        if(!scrolled){
            var element = document.getElementById("message_data");
            $("#message_data").animate({ scrollTop: element.scrollHeight }, 1000);
            //element.scrollTop =element.scrollHeight;
            //element.scrollIntoView();
            //console.log("S");
        }
        
    }

    

    function initialize(){
        service_handler = exports.getComponent("p_svr");
        if(!service_handler){
            console.log("Service has not Loaded please check.")
        }
        data= {id:bindData.data.storeid?bindData.data.storeid:bindData.data.pid,page:p.toString(),size:s.toString(),lastid:l.toString()};
        service_handler.services.Messages(data).then(function(result){

            
            if(result.success){
                bindData.messages=result.result;
                //setTimeout(updateScroll(),5000);
                
            }else{
                lockForm();
            }
            unlockForm();
        }).error(function(result){
            console.log(result);
            
        });
        
    }

    function submit(c_input){
        lockForm();
       
            //data={}
            data ={id:bindData.data.storeid?bindData.data.storeid:bindData.data.pid,messagetype:bindData.messagetype,message:bindData.message}
            service_handler.services.SendMessage(data).then(function(result){
                
                //console.log(result);
                
                if(result.success){
                    bindData.message="";
                    bindData.messages.push(result.result);
                    complete_call(bindData.data);
                }else{
                    scope.submitErrors.push("Error");
                }
                unlockForm();
            }).error(function(result){
                scope.submitErrors = [];
                bindData.submitErrors.push("Error");
                unlockForm();
            });

        //}
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

    

    exports.vue = vueData;
    exports.onReady = function(element){
        
    }

});
