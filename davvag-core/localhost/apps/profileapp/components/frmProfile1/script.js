WEBDOCK.component().register(function(exports){
    var bindData = {
        i_profile:{catogory:"Student",id:0,title:"Mr",name:"Lasitha",gender:"m",organization:"Christ Gospel",email:"lasitha@gmail.com",contactno:"sss",addresss:"ssss",country:"sssss",city:"dddddddd"},
        submitErrors: undefined,
        SearchItem:"",
        items:[],
        showSearch:false,
        p_image:''
    };

    var vueData = {
        onReady: function(){
            initializeComponent();
        },
        data:bindData,
        methods: {
            save:saveProfile,
            clear:clearProfile,
            getProfilebyID:getProfilebyID,
            searchItems:searchItems,
            createImage:createImage ,
            removeImage: removeImage,
            onFileChange: function(e) {
                var files = e.target.files || e.dataTransfer.files;
                if (!files.length)
                    return;
                this.createImage(files[0]);
            },
            navigateBack: function(){
                handler1 = exports.getShellComponent("soss-routes");
                handler1.appNavigate("..");
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
        productHandler = exports.getComponent("product");
        profileHandler = exports.getComponent("profile");
        uploaderInstance = exports.getShellComponent("soss-uploader");
        pInstance = exports.getShellComponent("soss-routes");
        routeData = pInstance.getInputData();
        if(routeData.id!=null){
            getProfilebyID(routeData.id)
        }
        console.log(routeData);
        //jQuery('#datepicker').datepicker();
        //jQuery("#datepicker").mask("99/99/9999");
        //getAllProductsThroughService();
        //getAllProductsThroughTransform();
        //saveProfile();
    }

    var newFile;
    function uploadFile(productId, cb){
        if (!newFile)cb();
        else{
            uploaderInstance.services.uploadFile(newFile, "profile", productId)
            .then(function(result){
                cb();
            })
            .error(function(){
                cb();
            });
        }
    }

    function addProfileToTmp(p){
        var profiles=[];
        var additem=true;
        if( localStorage.getItem("tmpprofiles")!==null)
        {
            profiles=JSON.parse(localStorage.getItem("tmpprofiles"));
        }
        profiles.forEach(element => {
            if(element.id==p.id){
                element=p;
                additem=false;
                return;
            }
        });
        if(additem){
            profiles.push(p);
        }
        localStorage.setItem("tmpprofiles",JSON.stringify(profiles));
    }

    function removeImage(e) {
        bindData.p_image = '';
    }

    function createImage(file) {
        newFile = file;
        var image = new Image();
        var reader = new FileReader();

        reader.onload = function (e) {
            bindData.p_image = e.target.result;
        };

        reader.readAsDataURL(file);
    }

    function clearProfile(){
        bindData.i_profile={};
        showSearch=false;
    }
    function saveProfile(){
        console.log(bindData.i_profile)
        //return;
        profileHandler.services.Save(bindData.i_profile)
        .then(function(response){
            console.log(JSON.stringify(response));
            if(response.result.success){
                //console
                console.log(response.result.result.generatedId);
                if(response.result.result.generatedId!=0){
                    bindData.i_profile.id=response.result.result.generatedId;
                }
                //try replacing the id with name, then it will save as a normal filename
                addProfileToTmp(bindData.i_profile);
                uploadFile(bindData.i_profile.id, function(){
                    alert ("successfully saved");
                });
                console.log(response.result);
                console.log(response.result.result.generatedId);
                
            }else{
                alert (response.result.error);
            }
        })
        .error(function(error){
            alert (error.responseJSON.result);
            console.log(error.responseJSON);
        });
    }

    function getProfilebyID(id){
        console.log(bindData.item)
        profileHandler.services.Search({q:"id:"+id})
        .then(function(response){
            console.log(JSON.stringify(response));
            if(response.success){
                //console
                //bindData.item.id=response.result.result.generatedId;
                bindData.showSearch=false;
                console.log(response);
                if(response.result.length!=0){
                    console.log("items chnaged");
                    bindData.i_profile=response.result[0];
                    bindData.p_image = 'components/dock/soss-uploader/service/get/profile/'+bindData.i_profile.id;
                    console.log( bindData.p_image);
                    //image
                }else{
                    clearProfile();
                }
            }else{
                alert (response.error);
            }
        })
        .error(function(error){
            alert (error.responseJSON.result);
            console.log(error.responseJSON);
        });
    }

    function searchItems(columncode,columnvalue){
        console.log(bindData.items)
        profileHandler.services.Search({q:columncode+":"+columnvalue})
        .then(function(response){
            console.log(JSON.stringify(response));
            if(response.success){
                //console
                //bindData.item.id=response.result.result.generatedId;
                console.log(response);
                if(response.result.length!=0){
                    console.log("items chnaged");
                    bindData.items=response.result;
                    bindData.showSearch=true;
                    console.log(JSON.stringify(bindData.items));
                }
            }else{
                alert (response.error);
            }
        })
        .error(function(error){
            alert (error.responseJSON.result);
            console.log(error.responseJSON);
        });
    }

    function getAllProductsThroughService(){
        productHandler.services.allProducts()
        .then(function(response){
            vueData.data.products = response.result;
        })
        .error(function(error){

        });
    }
});
