WEBDOCK.component().register(function(exports){
    var bindData = {
        item:{catogory:"Student",id:0,title:"Mr",name:"Lasitha",gender:"m",organization:"Christ Gospel",email:"lasitha@gmail.com",contactno:"sss",addresss:"ssss",country:"sssss",city:"dddddddd"},
        submitErrors: undefined,
        SearchItem:"",
        items:[],
        Activities:[],
        Transaction:[],
        attributes:{},
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
            navDonate: function(id){
                window.location="#/app/com_qti_students/donate?id="+id.toString();
            },
            
            getProfilebyID:getProfilebyID,
            navigate: function(id){
                handler = exports.getShellComponent("soss-routes");
                handler.appNavigate("../edit?id=" + id);
            },
            navBack: function(id){
                window.location="#/app/com_qti_students/";
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
        },filters:{
            formatMoney:function(v){
                if(v){
                    return (v).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                }else{
                    return 0.00;
                }
            },
            markeddown: function (value) {  
                if (!value) return ''
                value = value.toString()
                return marked(unescape(value));
              },
              dateformate:function(v){
                  if(!v){
                      return ""
                  }else{
                    return moment(v, "MM-DD-YYYY hh:mm:ss").format('MMM Do YYYY');
                  }
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
        rInstance = exports.getShellComponent("soss-routes");
        //localStorage.profile
        routeData = rInstance.getInputData();
        //console.log("profile");
        //console.log(localStorage.profile);
        if(routeData.id){
            //profile =JSON.parse(localStorage.profile);
            //console.log(profile);
            getProfilebyID(routeData.id)
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
                        {storename:"profile_attributes","search":"id:"+id},
                        {storename:"profileservices","search":"profileid:"+id}];
                        pInstance.services.q(query)
            .then(function(r){
                console.log(JSON.stringify(r));
                if(r.success){
                    //bindData.Transaction=r.result.ledger;
                    bindData.attributes=r.result.profile_attributes[0]==null?{}:r.result.profile_attributes[0]
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
