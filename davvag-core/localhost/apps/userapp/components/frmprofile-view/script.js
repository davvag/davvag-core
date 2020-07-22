WEBDOCK.component().register(function(exports){
    var bindData = {
        item:{catogory:"Student",id:0,title:"Mr",name:"Lasitha",gender:"m",organization:"Christ Gospel",email:"lasitha@gmail.com",contactno:"sss",addresss:"ssss",country:"sssss",city:"dddddddd"},
        submitErrors: undefined,
        SearchItem:"",
        items:[],
        Activities:[],
        Transaction:[],
        Summary:{},
        showSearch:false,
        image:'components/dock/soss-uploader/service/get/profile/1'
    };

    var vueData = {
        onReady: function(){
            initializeComponent();
        },
        data:bindData,
        methods: {
            pay:function(){

            },
            cancelPay:function(){
                $('#modalImagePopup').modal('toggle');
            },
            showPay:function(){
                $('#modalImagePopup').modal('show');
            },
            logout:function(){
                userHandler = exports.getComponent("login-handler");
                userHandler.services.Logout().then(function(result){
                    if(result.result){
                        localStorage.clear();
                        sessionStorage.clear();
                        pInstance = exports.getShellComponent("soss-routes");
                        pInstance.appNavigate("../login");
                    }else{

                    }
                }).error(function(result){
                    //$("#form-reset :input").prop("disabled", false);

                    bindData.submitErrors.push(result.result.message);
                    scope.isBusy = false;
                });
            },
            getProfilebyID:getProfilebyID,
            navigate: function(id){
                handler = exports.getShellComponent("soss-routes");
                handler.appNavigate("../edit?id=" + id);
            },
            navigatepage: function(pagev,p){
                //console.log(p);
                //addProfileToTmp(p);
                handler = exports.getShellComponent("soss-routes");
                if(p!=null){
                    handler.appNavigate("../"+pagev+"?tid=" + p);
                }else{
                    handler.appNavigate("/"+pagev);
                }
            },
            status:function(status){
                switch(status){
                    case "ToBeActive":
                        return "primary";
                    break;
                    case "Deactive":
                        return "danger";
                    break;
                    case "Active":
                        return "success";
                    break;
                    default:
                        return "warning";
                    break;
                }
            }
        },
        filters: {
            currency: function (value) {
              if (!value) return ''
              value = value.toString()
              return parseFloat(value).toFixed(2);
            }
          
        }
    }

    exports.vue = vueData;
    exports.onReady = function(element){
    }
    //var catogoryid ={"Staff",""};
    //var item ={};
    var productHandler;
    var profileHandler;
    var uploaderInstance;
    var pInstance;

    function initializeComponent(){
        profileHandler = exports.getComponent("profile");
        pInstance = exports.getShellComponent("soss-data");
        uploaderInstance = exports.getComponent ("soss-uploader");
        //localStorage.profile
        //routeData = pInstance.getInputData();
        //console.log("profile");
        //console.log(localStorage.profile);
        if(localStorage.profile!=null){
            profile =JSON.parse(localStorage.profile);
            //console.log(profile);
            getProfilebyID(profile.id)
        }
        //console.log(routeData);
    }

    

    function clearProfile(){
        bindData.item={};
        showSearch=false;
    }
    

    function getProfilebyID(id){
        //console.log(bindData.item)
        
            console.log("items chnaged");
            //bindData.item=response.result[0];
            bindData.image = 'components/dock/soss-uploader/service/get/profile/'+id;
            
            var query=[{storename:"profilestatus",search:"profileid:"+id},
                        {storename:"profile","search":"id:"+id},
                        {storename:"ledger","search":"profileid:"+id},
                        {storename:"profileservices","search":"profileid:"+id}];
                        pInstance.services.q(query)
            .then(function(r){
                console.log(JSON.stringify(r));
                if(r.success){
                    bindData.Transaction=r.result.ledger;
                    if(r.result.profilestatus.length!=0){
                        bindData.Summary=r.result.profilestatus[0];
                    }
                    if(r.result.profile.length!=0){
                        bindData.item=r.result.profile[0];
                    }
                    bindData.items=r.result.profileservices;
                }
            })
            .error(function(error){
                console.log(error.responseJSON);
            });
            //image
        
    }


});
