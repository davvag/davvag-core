
WEBDOCK.component().register(function(exports){
    var bindData = {
        item:{catogory:"",id:0,title:"",name:"",gender:"",organization:"",email:"",contactno:"",addresss:"",country:"",city:""},
        submitErrors: undefined,
        SearchItem:"",
        items:[],
        items_pending:[],
        items_rejected:[],
        Activities:[],
        Transaction:[],
        Summary:{},
        showSearch:false,
        image:'components/dock/soss-uploader/service/get/profile/1',
        appName:"Tranaction History",
        profile_policy:{id:1,profilephoto:1,lastseen:1,status:1,read_receipts:1,posts:1},
        loadingApp:false,
        loadingAppError:false
    };
    var newFile;
    function completeResponce(results){
        if($('#decker1100').hasClass("profile-content-show")){
            bindData.apptitle="Tranaction History";
            showTab("#tran");
            $('#decker1100').removeClass("profile-content-show");
        }
        console.log(results);
    }

    function showTab(x){
        $("#tabs>div.active").removeClass("active");
        $(x).addClass("active");
    }

    var vueData = {
        onReady: function(){
            initializeComponent();
        },
        data:bindData,
        methods: {
            updatepolicy:updatePolicy,
            changeProfilePic:function(){
                cropper1.crope(1,1,function(e){
                    //console.log(e);
                    bindData.image=e.data;
                    newFile=e.fileData;
                    exports.getAppComponent("davvag-tools","davvag-file-uploader", function(uploader){
                        uploader.initialize();
                        var files=[];
                        newFile.name=bindData.item.id;
                        files.push(newFile);
                        uploader.upload(files, "profile", null,function(e){
                            console.log(e);
                        });
                    });
                });

            },
            showTab:function(tab,title){
                $('#decker1100').addClass("profile-content-show");
                bindData.appName=title;
                showTab(tab);
            },
            downloadapp:function(appname,form,data,apptitle){
                $('#decker1100').addClass("profile-content-show");
                bindData.loadingApp=false;
                bindData.appName=apptitle;
                showTab("#app");
                apploader.downloadAPP(appname,form,"appdock",function(d){
                    
                    
                    bindData.loadingApp=true;
                    bindData.loadingAppError=false;
                    
                },function(e){
                    console.log(e);
                    bindData.loadingAppError=true;
                },completeResponce,data);
            },
            hide:function(){
                $('#decker1100').removeClass("profile-content-show");
                bindData.apptitle="Tranaction History";
                showTab("#tran");
            },
            showMenu:function(a){
                if($(a).hasClass("show")){
                    $(a).removeClass("show");
                    $(a).addClass("toggle");
                }else{
                    $(a).addClass("show");
                    $(a).removeClass("toggle");
                }
                
            },
            setAttribute:function(a,itemprop,newValue){
                $(a).removeClass("show");
                $(a).addClass("toggle");
                bindData.profile_policy[itemprop]=newValue;
                updatePolicy();
            }
            ,
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
                        //sessionStorage.clear();
                        pInstance = exports.getShellComponent("soss-routes");
                        pInstance.appNavigate("../login");
                    }else{
                        localStorage.clear();
                        sessionStorage.clear();
                        pInstance = exports.getShellComponent("soss-routes");
                        pInstance.appNavigate("../login");
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
            },
            privacy_filter:function(value){
                switch(value){
                    case 0:
                        return "Nobody";
                        break;
                    case 1:
                        return "Everyone";
                        break;
                    case 2:
                        return "Followers";
                        break;
                }
            }

          
        }
    }

    function updatePolicy(){
        bindData.profile_policy.id=bindData.item.id;
        authhandler.services.updatePolicy(bindData.profile_policy).then(function(r){
            if(r.success){
                bindData.profile_policy=r.result;
            }
        }).error(function(er){

        });
    }

    exports.vue = vueData;
    exports.onReady = function(element){
    }
    //var catogoryid ={"Staff",""};
    //var item ={};
    var productHandler;
    var profileHandler;
    var authhandler;
    var pInstance;
    var apploader,cropper1;
    function initializeComponent(){
        exports.getAppComponent("davvag-tools","davvag-img-cropper", function(cropper){
            cropper.initialize(300,300);
            cropper1=cropper;
        });
        profileHandler = exports.getComponent("profile");
        pInstance = exports.getShellComponent("soss-data");
        authhandler = exports.getComponent ("login-handler");
        exports.getAppComponent("davvag-tools","davvag-app-downloader", function(_uploader){
            apploader=_uploader;
            apploader.initialize();
        });
        if(localStorage.profile!=null){
            profile =JSON.parse(localStorage.profile);
            //console.log(profile);
            getProfilebyID(profile.id);
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
            authhandler.services.ProfileData()
            .then(function(r){
                if(r.success){
                    bindData.Transaction=r.result.ledger;
                    if(r.result.profilestatus!=null){
                        bindData.Summary=r.result.profilestatus;
                    }
                    if(r.result!=null){
                        bindData.item=r.result;
                    }
                    if(r.result.profile_policy!=null){
                        bindData.profile_policy=r.result.profile_policy;
                    }
                    bindData.items=r.result.orders;
                    bindData.items_pending=r.result.order_pending;
                    bindData.items_rejected=r.result.order_rejected;
                }
            })
            .error(function(error){
                console.log(error.responseJSON);
            });
            //image
        
    }


});
