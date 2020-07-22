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
        image:'components/dock/soss-uploader/service/get/profile/1',
        profile:{},
        isCompleted:false,
        isBusy:false,
        paymetRequest:null
    };

    var vueData = {
        onReady: function(){
            initializeComponent();
        },
        data:bindData,
        methods: {
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
            },
            submitPurchase:function(){
                var menuhandler  = exports.getComponent("productsvr");
                bindData.profile.profileid=bindData.item.id;
                menuhandler.services.PaymentRequest(bindData.profile)
                        .then(function(r){
                            console.log(JSON.stringify(r));
                            
                        })
                        .error(function(error){
                            //bindData.products=[];
                            //bindData.loading=false;
                            bindData.allloaded=false;
                            //page=
                            console.log(error.responseJSON);
                    });
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
        bindData.isBusy=true;
        if(routeData.id){
            getPayrequest(routeData.id)
        }else{

        }
        //console.log(routeData);
    }

    

    function clearProfile(){
        bindData.item={};
        showSearch=false;
    }
    
    //function 
    function getPayrequest(id){
        var query=[{storename:"payment_ext_request",search:"id:"+id}];
        pInstance.services.q(query)
            .then(function(r){
                if(r.success){
                    if(r.result.payment_ext_request.length!=0){
                        bindData.paymetRequest=r.result.payment_ext_request[0];
                        getProfilebyID(bindData.paymetRequest.profileId);
                    }
                }
            }).error(function(error){
                console.log(error.responseJSON);
            });
    }

    function getProfilebyID(id){
        //console.log(bindData.item)
        
            console.log("items chnaged");
            //bindData.item=response.result[0];
            bindData.image = 'components/dock/soss-uploader/service/get/profile/'+id;
            
            var query=[{storename:"profilestatus",search:"profileid:"+id},
                        {storename:"profile","search":"id:"+id},
                        {storename:"ledger","search":"profileid:"+id},
                        {storename:"orderheader","search":"profileid:"+id}];
                        pInstance.services.q(query)
            .then(function(r){
                console.log(JSON.stringify(r));
                if(r.success){
                    bindData.isBusy=false;
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
