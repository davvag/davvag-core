WEBDOCK.component().register(function(exports){
    var bindData = {
        i_profile:{catogory:"Customer",id:0,country:"Sri Lanka",city:"",attributes:{}},
        submitErrors: undefined,
        SearchItem:"",
        items:[],
        showSearch:false,
        p_image:'',
        submitErrors:undefined
    };

    var vueData = {        
        onReady: function(s){
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

    //exports.vue = vueData;
    exports.onReady = function(element){

    }
    exports.deferredVue = function(resolver, renderDiv){
        attributes = exports.getShellComponent("dynamic-attributes");
        attributes.renderForm("profileattribute","i_profile.attributes",renderDiv,"AttributeText",function(){
            resolver(vueData);
        });
    };
    //var catogoryid ={"Staff",""};
    //var item ={};
    var productHandler;
    var profileHandler;
    var uploaderInstance;
    var pInstance;

    function initializeComponent(){
        WEBDOCK.freezeUiComponent("soss-routes",true); 
        productHandler = exports.getComponent("product");
        profileHandler = exports.getComponent("profile");
        uploaderInstance = exports.getShellComponent("soss-uploader");
        pInstance = exports.getShellComponent("soss-routes");
        validatorInstance = exports.getShellComponent("soss-validator");
        loadValidator();
        routeData = pInstance.getInputData();
        $('#grnDatePicker').datepicker().on('changeDate', function(ev){
            bindData.i_profile.dateofbirth = $('#grnDatePicker').val(); 
        });
        if(routeData.id!=null){
            getProfilebyID(routeData.id)
        }
        WEBDOCK.freezeUiComponent("soss-routes",false);

        console.log(routeData);
        
        //jQuery('#datepicker').datepicker();
        //jQuery("#datepicker").mask("99/99/9999");
        //getAllProductsThroughService();
        //getAllProductsThroughTransform();
        //saveProfile();
    }

    function loadValidator(){
        validator = validatorInstance.newValidator (bindData);
        validator.map ("i_profile.name",true, "You should enter a name");
        validator.map ("i_profile.title",true, "You should enter a title");
        validator.map ("i_profile.address",true, "You should enter a address");
        validator.map ("i_profile.city",true, "You should enter a city");
        validator.map ("i_profile.email","email", "email address is not valied");
        validator.map ("i_profile.contactno",true, "contact no is incorrect");
        validator.map ("i_profile.id_number",true, "ID no is incorrect");
        //validator.map ("i_profile.dateofbirth","date", "date of birth is incorrect");
        validator.map ("i_profile.catogory",true, "You should select a product category");
        //validator.map ("p_image",true, "You should upload an image");
    }

    var newFile;
    function uploadFile(productId, cb){
        if (!newFile)cb();
        else{
            uploaderInstance.services.uploadFile(newFile, "profile", productId)
            .then(function(result){
                $.notify("Profile Image Has been uploaded", "info");
                cb();
            })
            .error(function(){
                $.notify("Profile Image Has not been uploaded", "error");
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
        WEBDOCK.freezeUiComponent("soss-routes",true); 
        
        bindData.submitErrors = validator.validate(); 
            if (!bindData.submitErrors){
            //return;
            //bindData.i_profile.attributes
            profileHandler.services.Save(bindData.i_profile)
            .then(function(response){
                //console.log(JSON.stringify(response));
                if(response.success){
                    //console
                    //console.log(response.result.result.generatedId);
                    //if(response.result.result.generatedId!=0){
                        bindData.i_profile=response.result;
                       
                    //}
                    $.notify("Profile Has been saved", "success");
                    addProfileToTmp(bindData.i_profile);
                    uploadFile(bindData.i_profile.id, function(){
                        //pInstance.appNavigate("..");
                    });
                    WEBDOCK.freezeUiComponent("soss-routes",false); 
                    
                }else{
                    $.notify("ERROR! Saving Profile", "error");
                    console.log(JSON.stringify(response));
                    WEBDOCK.freezeUiComponent("soss-routes",false); 
                    //alert (response.result.error);
                }
            })
            .error(function(error){
                //alert (error.responseJSON.result);
                $.notify("ERROR! "+ error.responseJSON.result, "error");
                console.log(JSON.stringify(error));
                WEBDOCK.freezeUiComponent("soss-routes",false); 
                
            });
        }else{
            WEBDOCK.freezeUiComponent("soss-routes",false); 
        }
    }

    function getProfilebyID(id){
        console.log(bindData.item)
        profileHandler.services.ByID({id:id})
        .then(function(response){
            console.log(JSON.stringify(response));
            if(response.success){
                //console
                //bindData.item.id=response.result.result.generatedId;
                bindData.showSearch=false;
                console.log(response);
                if(response.result!=null){
                    console.log("items chnaged");
                    bindData.i_profile=response.result;
                    //bindData.i_profile.attributes={};s
                    bindData.p_image = 'components/dock/soss-uploader/service/get/profile/'+bindData.i_profile.id;
                    //console.log( bindData.p_image);
                    //image
                }else{
                    clearProfile();
                }
            }else{
                $.notify("ERROR! Loading Profile Error", "error");
                //alert (response.error);
            }
        })
        .error(function(error){
            $.notify(error.responseJSON.result, "error");
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
                $.notify("Searching error please refresh your application", "error");
                console.log(JSON.stringify(response));
                //alert (response.error);
            }
        })
        .error(function(error){
            //alert (error.responseJSON.result);
            $.notify("Searching error please refresh your application", "error");
            console.log(JSON.stringify(error.responseJSON));
        });
    }

    
});
