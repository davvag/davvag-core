WEBDOCK.component().register(function(exports){
    var scope,Rini,handler;
    
    function allloadkeyword(category, skip, take){
        //var handler = exports.getComponent("user-handler");
        handler.services.UserGroups().then(function(r){
            scope.groups=r.result;
            loadAccess();
        }).error(function(){

        });

        
    }

    function loadAccess(){
        $("#form-signup :input").prop("disabled", true);
        handler.services.allApplications({"Group":scope.Group}).then(function(result){
            scope.items = result.result;
            //$('#tree1').treed({openedClass:'glyphicon-folder-open', closedClass:'glyphicon-folder-close'});
            $("#form-signup :input").prop("disabled", false);
        }).error(function(){
            $("#form-signup :input").prop("disabled", false);
        });
    }
    
    function saveAccess (){
        var self = this;
        scope.submitErrors=[];
        scope.submitInfo=[];
                $("#form-signup :input").prop("disabled", true);

            handler.services.SetAccess({"groupid":scope.Group,"data":scope.items}).then(function(result){
                scope.isBusy = false;
                $("#form-signup :input").prop("disabled", false);

                if (result.success)
                {
                    if(result.result.error!=null){
                        scope.submitErrors.push(result.result.message);
                    }else{
                        console.log(result);
                        //scope.items.push(result.result);
                    }
                    
                }else {
                    scope.submitErrors.push(result.result);;
                }
    
            }).error (function(result){
                $("#form-signup :input").prop("disabled", false);

                scope.submitErrors.push(result.result);
                //scope.isBusy = false;
            });;
        //}
    };


    var vueData = {
        methods:{navigate: function(e){
            if(e!=null){
                scope.item=e;
            }else{
                scope.item={};
            }

            $('#modalImagePopup').modal('show');
        },
        canceluserForm:function(){
            scope.item={};
            $('#modalImagePopup').modal('toggle');
        },
        saveAccess:saveAccess,
        check:function(e){
            //alert(e);
        },loadAccess:loadAccess},
        data :{
            items : [],
            item:{},
            submitErrors:[],
            Group:"anonymous",
            groups:[]
        },
        onReady: function(s){
            //$('#tree1').treed();

            

            //$('#tree3').treed({openedClass:'glyphicon-chevron-right', closedClass:'glyphicon-chevron-down'});
            handler= exports.getComponent("app-handler");
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
