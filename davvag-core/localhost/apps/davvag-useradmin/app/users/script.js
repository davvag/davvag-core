WEBDOCK.component().register(function(exports){
    var scope,Rini,handler;
    
    function allloadkeyword(category, skip, take){
        //var handler = exports.getComponent("user-handler");
        handler.services.UserGroups().then(function(r){
            scope.groups=r.result;
        }).error(function(){

        });
        handler.services.allusers().then(function(result){
            scope.items = result.result;
        }).error(function(){
            
        });
    }
    
    function saveUser (){
        var self = this;
        scope.submitErrors=[];
        scope.submitInfo=[];
       if(scope.item.password!=scope.item.confirmpassword){
            scope.submitErrors.push("Password Mismatch");
            return 0;
        }
            //svar handler = exports.getComponent("login-handler");
            scope.isBusy = true;
            $("#form-signup :input").prop("disabled", true);

            handler.services.RegisterUser(scope.item).then(function(result){
                scope.isBusy = false;
                $("#form-signup :input").prop("disabled", false);

                if (result.success)
                {
                    if(result.result.error!=null){
                        scope.submitErrors.push(result.result.message);
                    }else{
                        scope.items.push(result.result);
                        $('#modalImagePopup').modal('toggle');
                    }
                    
                }else {
                    scope.submitErrors.push(result.result);
                    //console.log(JSON.stringify(result));   
                    //console.log('email and password is incorrect.', 'Security!');
                }
    
            }).error (function(result){
                $("#form-signup :input").prop("disabled", false);

                scope.submitErrors.push(result.result);
                //scope.isBusy = false;
            });;
        //}
    };

    function changeGroup(){
        handler.services.ChangeGroup({"userid":scope.item.userid,"groupid":scope.item.groupid}).then(function(result){
            if (result.success)
            {
                $('#modalGroup').modal('toggle');
            }else{

            }
        }).error(function(){

        });
    }

    function newGroup(){
        if(scope.groupname!=""){
            handler.services.NewUserGroup({"groupid":scope.groupname}).then(function(result){
                if (result.success)
                {
                    scope.groups.push({"groupid":scope.groupname});
                    $('#modalGroupNew').modal('toggle');
                }else{
    
                }
            }).error(function(){
    
            });
            
        }
    }

    var vueData = {
        methods:{navigate: function(e){
            if(e!=null){
                scope.item=e;
            }else{
                scope.item={};
            }

            //$('#modalImagePopup').modal('show');
            $('#modalImagePopup').modal({backdrop: 'static', keyboard: false});  
        },navigateGroup: function(e){
            if(e!=null){
                scope.item=e;
                $('#modalGroup').modal({backdrop: 'static', keyboard: false}); 
            }else{
                //scope.item={};
            }
             
        },navigateNewGroup: function(){
            
                $('#modalGroupNew').modal({backdrop: 'static', keyboard: false}); 
            
        },
        canceluserForm:function(){
            scope.item={};
            $('#modalImagePopup').modal('toggle');
        },
        cancelgroupForm:function(){
            scope.item={};
            $('#modalGroup').modal('toggle');
        },
        cancelnewgroupForm:function(){
            //scope.item={};
            $('#modalGroupNew').modal('toggle');
        },
        saveUser:saveUser,changeGroup:changeGroup,newGroup:newGroup
        },
        data :{
            items : [],
            item:{},
            submitErrors:[],
            groups:[],
            groupname:""
        },
        onReady: function(s){
            //var countries = ["Afghanistan","Albania","Algeria","Andorra","Angola","Anguilla","Antigua & Barbuda","Argentina","Armenia","Aruba","Australia","Austria","Azerbaijan","Bahamas","Bahrain","Bangladesh","Barbados","Belarus","Belgium","Belize","Benin","Bermuda","Bhutan","Bolivia","Bosnia & Herzegovina","Botswana","Brazil","British Virgin Islands","Brunei","Bulgaria","Burkina Faso","Burundi","Cambodia","Cameroon","Canada","Cape Verde","Cayman Islands","Central Arfrican Republic","Chad","Chile","China","Colombia","Congo","Cook Islands","Costa Rica","Cote D Ivoire","Croatia","Cuba","Curacao","Cyprus","Czech Republic","Denmark","Djibouti","Dominica","Dominican Republic","Ecuador","Egypt","El Salvador","Equatorial Guinea","Eritrea","Estonia","Ethiopia","Falkland Islands","Faroe Islands","Fiji","Finland","France","French Polynesia","French West Indies","Gabon","Gambia","Georgia","Germany","Ghana","Gibraltar","Greece","Greenland","Grenada","Guam","Guatemala","Guernsey","Guinea","Guinea Bissau","Guyana","Haiti","Honduras","Hong Kong","Hungary","Iceland","India","Indonesia","Iran","Iraq","Ireland","Isle of Man","Israel","Italy","Jamaica","Japan","Jersey","Jordan","Kazakhstan","Kenya","Kiribati","Kosovo","Kuwait","Kyrgyzstan","Laos","Latvia","Lebanon","Lesotho","Liberia","Libya","Liechtenstein","Lithuania","Luxembourg","Macau","Macedonia","Madagascar","Malawi","Malaysia","Maldives","Mali","Malta","Marshall Islands","Mauritania","Mauritius","Mexico","Micronesia","Moldova","Monaco","Mongolia","Montenegro","Montserrat","Morocco","Mozambique","Myanmar","Namibia","Nauro","Nepal","Netherlands","Netherlands Antilles","New Caledonia","New Zealand","Nicaragua","Niger","Nigeria","North Korea","Norway","Oman","Pakistan","Palau","Palestine","Panama","Papua New Guinea","Paraguay","Peru","Philippines","Poland","Portugal","Puerto Rico","Qatar","Reunion","Romania","Russia","Rwanda","Saint Pierre & Miquelon","Samoa","San Marino","Sao Tome and Principe","Saudi Arabia","Senegal","Serbia","Seychelles","Sierra Leone","Singapore","Slovakia","Slovenia","Solomon Islands","Somalia","South Africa","South Korea","South Sudan","Spain","Sri Lanka","St Kitts & Nevis","St Lucia","St Vincent","Sudan","Suriname","Swaziland","Sweden","Switzerland","Syria","Taiwan","Tajikistan","Tanzania","Thailand","Timor L'Este","Togo","Tonga","Trinidad & Tobago","Tunisia","Turkey","Turkmenistan","Turks & Caicos","Tuvalu","Uganda","Ukraine","United Arab Emirates","United Kingdom","United States of America","Uruguay","Uzbekistan","Vanuatu","Vatican City","Venezuela","Vietnam","Virgin Islands (US)","Yemen","Zambia","Zimbabwe"];
            //autocomplete(document.getElementById("user-group"),countries);
            handler= exports.getComponent("user-handler");
            scope = s;
            allloadkeyword(undefined,0,100);
            Rini = exports.getShellComponent("soss-routes");
            routeData = Rini.getInputData();
            if(routeData){
                
            }else{
                
            }

        }
    }    

    exports.vue = vueData;
    exports.onReady = function(element){
    }
});
