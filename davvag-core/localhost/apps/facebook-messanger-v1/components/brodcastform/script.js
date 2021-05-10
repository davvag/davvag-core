WEBDOCK.component().register(function (exports) {
    var scope;
    var bindData = {
        item: { id: 1 },
        items: [],
        filters: ["generic", "button", "open_graph", "media"],
        davvagflows: [],
        p_image: null,
        submitErrors: undefined,
        i18n: undefined,
        template_type: "generic",
        itemid: 1,
        types: ["web_url", "postback", "phone_number", "account_link"],
        buttons: [],
        button: {},
        postback: {},
        button_type: undefined,
        elements: [],
        element: {},
        broadcast: {},
        field: { condition: { Description: "" } },
        fields: ["tag", "first_name", "name", "lasr_name"],
        queryParams: [{ query: "=", name: "Equals to", Description: "Input Value to check with" },
        { query: "<>", name: "Not Equals to", Description: "Input Value exclued from the query" },
        { query: "Like", name: "Contains the value", Description: "Input value to get contains value Ex: %value% or value%" },
        { query: "In", name: "Equals To specific value set", Description: "Input values by seperatiing by commas ex: value 1,value 2" }],
        query: [],
        showBroacaster: true
    };

    function loadall() {
        var handler = exports.getComponent("keyword-handler");

        handler.services.DavvagFlows({ "pageid": "@none" }).then(function (result) {
            bindData.davvagflows = result.result;
            routeData = Rini.getInputData();
            if (routeData.id) {
                var h = exports.getComponent("broadcaster-handler");
                h.services.BroadcastByID({ "id": routeData.id }).then(function (r) {
                    bindData.broadcast = r.result;
                    bindData.items = JSON.parse(r.result.content);
                    bindData.query = JSON.parse(r.result.query);
                }).error(function () {

                });
            }
        }).error(function () {

        });

    }
    var newFile;
    function createImage(file) {
        newFile = file;
        var image = new Image();
        var reader = new FileReader();
        reader.onload = function (e) {
            bindData.p_image = e.target.result;
            bindData.element.id = bindData.elements.length + 1;
            //bindData.element.File=$.extend(true,{},newFile);
            imagename = bindData.broadcast.id.toString() + "-" + bindData.item.id.toString() + "-" + bindData.element.id.toString();
            uploaderInstance = exports.getShellComponent("soss-uploader");
            bindData.element.image_url = "components/dock/soss-uploader/service/get/fb_broadcast/" + imagename;
            uploaderInstance.services.uploadFile(newFile, "fb_broadcast", imagename)
                .then(function (result2) {
                    $.notify("Image Has been uploaded", "info");
                })
                .error(function () {
                    $.notify("Profile Image Has not been uploaded", "error");
                });
        };

        reader.readAsDataURL(file);
    }

    var vueData = {
        methods: {
            navigate: function (id) {
                handler = exports.getShellComponent("soss-routes");
                handler.appNavigate("../broadcastlist");
            }, selectStore: function (p) {
                //bindData.product=p;
                $('#modalImagePopup').modal('show');
            }, selectStoreClose: function () {
                //bindData.product=p;
                $('#modalImagePopup').modal('toggle');
            }, onFileChange: function (e) {
                var files = e.target.files || e.dataTransfer.files;
                if (!files.length)
                    return;
                createImage(files[0]);
            },
            removeImage: function () { bindData.p_image = ''; },
            removeCard: function (e) {
                console.log(e.id);
                filteredItems = bindData.elements.filter(function (item) {
                    console.log(item.id);
                    console.log(e.id);
                    if (item.id !== e.id)
                        return item;
                });
                console.log(filteredItems);
                bindData.elements = filteredItems == null ? [] : filteredItems;
            },
            addCard: function (e) {
                if(validateAddCard()){
                    e.buttons = bindData.buttons;
                    e.id = bindData.elements.length + 1;
                    bindData.elements.push(JSON.parse(JSON.stringify(e)));
                    //console.log(bindData.elements);
                    bindData.element = {};
                    bindData.buttons = [];
                }
                
            },
            addButton: function (e) {
                try {
                    bindData.submitErrors = [];
                    if (e.title == null) {
                        bindData.submitErrors.push("Please fill in the data");
                    }
                    if (bindData.buttons.length >= 3) {
                        bindData.submitErrors.push("You cannot add more than 3 Buttons");
                    }

                    if (e.title.length > 20) {
                        bindData.submitErrors.push("Button Tile should be less than 20 charactors");
                        return 0;
                    }
                    if (bindData.submitErrors.length != 0) {
                        return;
                    }

                    e.type = bindData.button_type;
                    if (e.type == "postback") {
                        e.payload = e.davvagflow + ":" + e.point + ":";
                    }
                    bindData.buttons.push(JSON.parse(JSON.stringify(e)));
                } catch (error) {
                    console.log(error);
                }

            },
            submit: function () {
                if(validateSubmit()){
                    bindData.item.id = bindData.items.length;
                    //bindData.itemid+1;
                    bindData.item.template_type = bindData.template_type;
                    bindData.item.buttons = bindData.buttons;



                    if (bindData.elements.length > 0) {
                        bindData.item.elements = bindData.elements;
                        bindData.items.push(bindData.item);
                        clearElelemets();
                        $('#modalImagePopup').modal('toggle');
                        //});
                    } else {
                        bindData.items.push(bindData.item);
                        clearElelemets();
                        $('#modalImagePopup').modal('toggle');
                    }
                }


            },
            removeItem: function (e) {
                filteredItems = bindData.items.filter(function (item) {
                    console.log(item.id);
                    console.log(e.id);
                    if (item.id !== e.id)
                        return item;
                });
                //console.log(filteredItems);
                bindData.items = filteredItems == null ? [] : filteredItems;
            },
            addQuery(e) {
                filteredItems = bindData.query.filter(function (item) {
                    if (item.name === e.name)
                        return item;
                });
                if (filteredItems.length == 0) {
                    e.con = e.condition.query;
                    bindData.query.push(JSON.parse(JSON.stringify(e)));
                }
            },
            removeQuery: function (e) {
                filteredItems = bindData.query.filter(function (item) {
                    if (item.name !== e.name)
                        return item;
                });
                bindData.query = filteredItems == null ? [] : filteredItems;
            },
            SaveBroadcast: function () {
                Freez(true);
                bindData.broadcast.status = "new";
                saveBrodcast(function (e) {
                    Freez(false);
                }, function (err) {
                    Freez(false);
                });
            },
            Schedule: function () {
                Freez(true);
                bindData.broadcast.status = "schedule";
                saveBrodcast(function (e) {
                    Freez(false);
                    handler = exports.getShellComponent("soss-routes");
                    handler.appNavigate("../broadcastlist");
                }, function (err) {
                    Freez(false);
                });
            },
            Broadcast: function () {
                Freez(true);
                bindData.broadcast.status = "pending";
                saveBrodcast(function (e) {
                    Freez(false);
                    handler = exports.getShellComponent("soss-routes");
                    handler.appNavigate("../broadcastlist");
                }, function (err) {
                    Freez(false);
                });
            }
        },
        data: bindData,
        onReady: function (s) {
            scope = s;
            Rini = exports.getShellComponent("soss-routes");
            loadall();
            $(".form_datetime").datetimepicker({
                format: "mm-dd-yyyy hh:ii:00",
                autoclose: true,
                todayBtn: true,
                pickerPosition: "bottom-left"
            }).on('changeDate', function (ev) {
                bindData.broadcast.scheduled_date = $('#scheduledate').val();
                //console.log(bindData.broadcast.scheduled_date);
            });

            //loadInventory(undefined,0,100);
        }
    }

    function validateSubmit(){
        bindData.submitErrors = [];
        switch(bindData.template_type){
            case "button":
                if (bindData.item.text == null) {
                    bindData.submitErrors.push("Please Complete filling the data");
                }
                if(bindData.buttons.length==0){
                    bindData.submitErrors.push("Please add atleaset one button");
                }
                if (bindData.item.text.length > 2000 || bindData.item.text == "") {
                    bindData.submitErrors.push("Please enter Title Less than 200 words");
                    //return 0;
                }
                break;
            case "generic":
                if(bindData.elements.length==0){
                    bindData.submitErrors.push("Please Complete filling the data");
                }
                break;
            
        }
        if (bindData.submitErrors.length != 0) {
            return false;
        }else{
            return true;
        }
    }
    function validateAddCard() {
        bindData.submitErrors = [];
        switch(bindData.template_type){
            case "generic":
                if (bindData.element.title == null || bindData.element.subtitle==null) {
                    bindData.submitErrors.push("Please Complete filling the data");
                    return false;
                }
                if (bindData.elements.length >= 4) {
                    bindData.submitErrors.push("You cannot add more than 4 Cards");
                    //return 0;
                }
                if (bindData.element.title.length > 80 || bindData.element.title == "") {
                    bindData.submitErrors.push("Please enter Title Less than 80 words");
                    //return 0;
                }
                if (bindData.element.subtitle.length > 80 || bindData.element.subtitle == "") {
                    bindData.submitErrors.push("Please enter Subtitle Less than 80 words");
                    //return 0;
                }
                break;
        }
        
        if (bindData.submitErrors.length != 0) {
            return false;
        }else{
            return true;
        }
    }
    function clearElelemets() {
        newFile = null;
        bindData.elements = [];
        bindData.element = {};
        bindData.buttons = [];
        bindData.button = {};
        bindData.item = { id: bindData.items.length + 1 };
    }
    function saveBrodcast(cb, err) {
        var handler = exports.getComponent("broadcaster-handler");
        //Freez(true);
        bindData.createdate = fDate(new Date());
        bindData.broadcast.query = JSON.stringify(bindData.query);
        bindData.broadcast.content = JSON.stringify(bindData.items);
        handler.services.SaveBroadcast(bindData.broadcast).then(function (result) {
            $.notify("Saved Sucsessfully", "info");
            ///Freez(false);
            bindData.showBroacaster = false;
            bindData.broadcast = result.result;
            cb(bindData.broadcast);
            //return 
        }).error(function (e) {
            bindData.submitErrors = [JSON.parse(e.responseText).result];
            $.notify("Error", "error");
            console.log(e.responseText);
            //Freez(false);
            err(e);
        });
    }

    function Freez(yes) {
        $("btngonext").prop('disabled', yes);
        $("btnschedule").prop('disabled', yes);
        $("btnsendnow").prop('disabled', yes);
    }

    function uploadFileElement(productId, e, cb) {
        if (!e.length != 0) {
            cb();
            return;
        }
        var imagecount = 0;
        var completed = 0;
        imagecount = e.length;
        uploaderInstance = exports.getShellComponent("soss-uploader");
        for (var i = 0; i < e.length; i++) {
            console.log(e[i].File);
            console.log(e[i].File.name);
            imagename = productId.toString() + e[i].File.name;
            e[i].image_url = "components/dock/soss-uploader/service/get/fb_broadcast/" + imagename;
            uploaderInstance.services.uploadFile(e[i].image, "fb_broadcast", imagename)
                .then(function (result2) {
                    $.notify("Image Has been uploaded", "info");
                    completed++;
                    if (imagecount == completed) {
                        cb(e);
                    }
                })
                .error(function () {
                    completed++;
                    $.notify("Profile Image Has not been uploaded", "error");
                    if (imagecount == completed) {
                        cb(e);
                    }
                });
        }
    }

    function fDate(d) {
        var datestring = (d.getMonth() + 1) + "-" + d.getDate() + "-" + d.getFullYear() + " " + d.getHours() + ":" + d.getMinutes() + ":00";
        return datestring;
    }

    exports.vue = vueData;
    exports.onReady = function (element) {
    }
});
