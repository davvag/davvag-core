WEBDOCK.component().register(function(exports){
    var scope;   

    var bindData = {
        allUsers : [],
        p_image:[],
        image:'',
        product:{},
        files:null
    };

    var newfiles = [];

    function loadAllUsers(){
        var handler = exports.getComponent("settings-handler");
        handler.transformers.allUsers()
        .then(function(result){
            vueData.data.allUsers = result.result;
        })
        .error(function(error){
            alert (error);
        });
    }

    function createImageMulti(files) {
        //console.log(JSON.stringify(files));
        //if(!newfiles){
        newfiles=[];
        //}
        for (var i = 0; i < files.length; i++) {
            newfiles.push(files[i]);
            getImage(i,files[i]);
            //console.log();
        }
        

        console.log(JSON.stringify(bindData.p_image));
    }

    function getImage(index,file){
        var reader = new FileReader();
            reader.onload = function (e) {
                //console.log(e);
                //console.log(newfiles);
                newfiles[index].scr=e.target.result;
                
                bindData.p_image.push({id:0,name:newfiles[index].name,scr:e.target.result,file:file,selected:false});
                console.log(newfiles);
            };
        reader.readAsDataURL(file);
    }

    var vueData = {
        methods:{
            navigate: function(id){
                handler = exports.getShellComponent("soss-routes");
                handler.appNavigate(id ? "/user?userid=" + id : "/user");
            },
            onFileMultiChange: function(e) {
                var files = e.target.files || e.dataTransfer.files;
                if (!files.length)
                    return;
                createImageMulti(files);
            },
            removeImage:function(e) {
                bindData.image = '';
            },
            createImage: function (file) {
                newFile = file;
                var image = new Image();
                var reader = new FileReader();
        
                reader.onload = function (e) {
                    bindData.image = e.target.result;
                };
        
                reader.readAsDataURL(file);
            },
            selectImage: function(){
                $('#modalImagePopup').modal('show');
            }
        },
        data : bindData,
        onReady: function(s){           
            scope = s;
            loadAllUsers();
        }
    }    

    exports.vue = vueData;
    exports.onReady = function(element){
    }
});
