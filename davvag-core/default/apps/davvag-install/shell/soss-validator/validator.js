WEBDOCK.component()
.configure(function(exports){
    function Validator(sc){
        var mapping = {};
        return {
            map : function(k,t,m){
                if (!mapping[k])
                    mapping[k] = {required:{val:false, msg:undefined}, type:{}};
                
                if (typeof t === "boolean"){
                    mapping[k].required.val = t;
                    mapping[k].required.msg = m;
                }else {
                    mapping[k].type.val = t;
                    mapping[k].type.msg = m;
                }
            },
            validate: function(){
                var msgStack = [];
                for (var mk in mapping){
                    var key;
                    var obj;
                    if (mk.indexOf(".") == -1){
                      key = mk;
                      obj = sc;
                    }else{
                      var splitData = mk.split(".");

                        obj = sc;
                        for (var i=0;i<=splitData.length -2;i++){
                            obj = obj[splitData[i]];
                            if (!obj)
                                break;
                        }
                        var lastKey = splitData[splitData.length -1];
                        key = lastKey;
                    }

                    if (mapping[mk].required.val==true){
                        if (!obj){
                            msgStack.push(mapping[mk].required.msg);
                        }else{
                            if (!obj[key])
                                msgStack.push(mapping[mk].required.msg);
                        }
                    }

                    if (obj)
                    if (obj[key]){
                      if (mapping[mk].type.val){
                          switch(mapping[mk].type.val){
                            case "numeric":
                                var pattern = /^\d+$/;
                                if(!pattern.test(obj[key]))
                                    msgStack.push(mapping[mk].type.msg);
                                break;
                            case "email":
                                var pattern = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
                                if(!pattern.test(obj[key]))
                                    msgStack.push(mapping[mk].type.msg);
                                break;
                            case "date":
                                var pattern = /^((0|1)\d{1})-((0|1|2|3)\d{1})-((19|20)\d{2})/;
                                if(!pattern.test(obj[key]))
                                    msgStack.push(mapping[mk].type.msg);
                                break;
                            default:
                                if (mapping[mk].type.val !== typeof obj[key])
                                    msgStack.push(mapping[mk].type.msg);
                                break;
                          }
                      }
                    }
                }

                var outData = msgStack.length ==0 ? undefined : msgStack; 
                return outData;
            }
        }
    };

    exports.newValidator = function(scope){
        return new Validator(scope);
    }
})
.register(function(exports){

});
