WEBDOCK.component().register(function(exports){
    var scope;
    var settingsHandler;
    var routeInstance, validatorInstance;
    var routeData;
    
    var bindData = {
        user:{},
        submitErrors: undefined
    };

    var validator;
    function loadValidator(){
        validator = validatorInstance.newValidator (scope);
        validator.map ("user.username",true, "You should enter a name");
        validator.map ("user.email",true, "You should enter an email address");
        validator.map ("user.password",true, "You should enter a password");
    }

    function submit(){
        scope.submitErrors = validator.validate(); 
        if (!scope.submitErrors){

            var promiseObj;
            if (routeData.userid) promiseObj = settingsHandler.transformers.updateUser (bindData.user);
            else promiseObj = settingsHandler.transformers.createUser (bindData.user);

            promiseObj
            .then(function(){
                navigateBack();
            })
            .error(function(){
                alert ("Error!!!");
            });
        }
    }

    function loadUser(scope){
        if (routeData.userid)
        settingsHandler.transformers.getUserById(routeData.userid)
        .then(function(result){
            if (result.result.length !=0){
                scope.user = result.result[0];
            }
        })
        .error(function(){
    
        });
    }

    function navigateBack(){
        routeInstance.appNavigate("..");
    }

    var vueData =   {
        methods: {
            submit: submit,
            navigateBack: navigateBack
        },
        data : bindData,
        onReady: function(s){
            scope = s;
        
            settingsHandler = exports.getComponent("settings-handler");
            routeInstance = exports.getShellComponent("soss-routes");
            validatorInstance = exports.getShellComponent ("soss-validator");
            routeData = routeInstance.getInputData();
            
            loadValidator();
            if (routeData)
                loadUser(scope);
        }
    }

    exports.vue = vueData;
    exports.onReady = function(element){
    }
});
