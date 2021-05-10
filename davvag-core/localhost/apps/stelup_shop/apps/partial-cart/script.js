WEBDOCK.component().register(function(exports){
    var scope;

    var bindData =  {
            message : "Works!!!",
            items: [],
            itemsGrouped:[],
            hasOrder: false,
            user:{}
    };

    function processItems(){
        bindData.hasOrder = false;
        const grouped = groupBy(bindData.items, i => i.storeid);
        for(var i=0;i<bindData.items.length;i++){
            filteredItems = bindData.itemsGrouped.filter(function(item) {
                if(item.storeid===bindData.items[i].storeid)
                    return item;
            });
            if(filteredItems.length==0){
                bindData.itemsGrouped.push({storeid:bindData.items[i].storeid,storename:bindData.items[i].storename,items:grouped.get(bindData.items[i].storeid),total:0});
            }
            
        }
        for(var i=0;i<bindData.itemsGrouped.length;i++){
            tot=0;
            for(var x=0;x<bindData.itemsGrouped[i].items.length;x++){
                    tot+=(bindData.itemsGrouped[i].items[x].qty*(bindData.itemsGrouped[i].items[x].price-(bindData.itemsGrouped[i].items[x].price*(bindData.itemsGrouped[i].items[x].discountper/100))));
            }
            bindData.itemsGrouped[i].total=tot;
        }
        console.log(JSON.stringify(bindData.itemsGrouped));
            //bindData.itemsGrouped.find(function(items[i].storeid){})
            
            
            
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
            navigateBack:function(){
                handler1 = exports.getShellComponent("soss-routes");
                handler1.appNavigate("..");
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
            checkout: function(store){
                sessionStorage.tmpstorecheckout = JSON.stringify(store);
                location.href ="#/app/userapp?u=#/app/stelup_shop/checkout";
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
                    tot+=(this.items[i].qty*(this.items[i].price-(this.items[i].price*(this.items[i].discountper/100))));
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
    function groupBy(list, keyGetter) {
        const map = new Map();
        list.forEach((item) => {
             const key = keyGetter(item);
             const collection = map.get(key);
             if (!collection) {
                 map.set(key, [item]);
             } else {
                 collection.push(item);
             }
        });
        return map;
    }
    const grouped = groupBy(bindData.items, i => i.storeid);
    console.log(grouped);
    exports.vue = vueData;
    exports.onReady = function(){
    }
});

