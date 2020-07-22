WEBDOCK.component().register(function(exports){
    var scope;

    var bindData =  {
            message : "Works!!!",
            items: [],
            hasOrder: false,
            user:{}
    };

    function processItems(){
        bindData.hasOrder = false;
        for(var i=0;i<bindData.items.length;i++)
        if (bindData.items[i].isOrder){
            bindData.hasOrder = true;
            break;
        }
    }

    function login(){

        if (!scope.user.email || !scope.user.password){
            toastr.error('Invalid Username or Password', 'Login error');
            return;
        }

        var handler = exports.getComponent("login-handler");
        handler.services.login({email: scope.user.email,password:scope.user.password,domain:window.location.hostname})
        .then(function(result){
            $('#idCartLogin').on('hidden.bs.modal', function () {
                location.href ="#/checkout";
            })
            if (result.result)
                result = result.result;
            if (!result.error){
                localStorage.loginData = JSON.stringify(result);
                scope.profile = {address:{gpspoint:""},address2:{},address3:{}};
                //scope.profile.name = scope.user.name;
                scope.profile.email = scope.user.email;
                $('#idCartLogin').modal('hide');  
            }else {
                toastr.error('Invalid Username or Password', 'Login error');    
            }

        }).error(function(){
            toastr.error('Error logging into raha.lk.', 'Login error');
        });
    }

    var vueData =  {
       data : bindData,
       onReady: function(app,renderDiv){ 
           scope = app;      
            if(sessionStorage.items){
                bindData.items=JSON.parse(sessionStorage.items);
                processItems();
            }
       },
       methods:{
            windowClick: function(event){
                event.stopPropagation();
            },
            getItems:function () {
                items=[];
                if(sessionStorage.items){
                    items=JSON.parse(sessionStorage.items);
                }
                return items;
            },
            removeItem: function(item){
                var removeIndex;

                for (var i=0;i<bindData.items.length;i++)
                if (bindData.items[i].itemid == item.itemid){
                    removeIndex = i;
                    break;
                }

                if (removeIndex !== undefined)
                     bindData.items.splice(removeIndex, 1);

                processItems();

                sessionStorage.items = JSON.stringify(bindData.items);
            },
            checkout: function(isToday){
                sessionStorage.deliverToday = isToday;
                // if (!localStorage.loginData){
                //     $('#idCartLogin').modal('show');
                // }else{
                    location.href ="#/app/userapp?u=#/app/davvag-shop/checkout-complete";
                // }
            },
            closeModal: function(){
                $('#idCartLogin').modal('hide');
            },
            login: login,
            registerNow: function(){
                $('#idCartLogin').on('hidden.bs.modal', function () {
                    location.href ="#/user";
                });
                $('#idCartLogin').modal('hide'); 
            }
        },
        computed: {
            total: function() {
                tot=0;
                for(i in this.items){
                    tot+=(this.items[i].qty*this.items[i].price);
                }
                return tot;
            },
            watch:{
                items: function (val, oldVal) {
                    consol.log(val);
                    sessionStorage.items=JSON.stringify(val);
                }
                
            }
        }
    }

    exports.vue = vueData;
    exports.onReady = function(){
    }
});

