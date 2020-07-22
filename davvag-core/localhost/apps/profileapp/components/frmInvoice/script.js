WEBDOCK.component().register(function(exports){
    var bindData = {
        i_profile:{},
        InvItems:[{itemid:0,name:"",uom:"",qty:0,price:parseFloat("0").toFixed(2),total:parseFloat("0").toFixed(2),selected:null,invtype:"",catogory:""}],
        products:[],
        subtotal:0,
        tax:0,
        taxamount:0,
        total:0,
        date:new Date(),
        duedate:new Date(),
        invoiceSave:false,
        InvoiceToSave:{}
    };

    function calcTotals(){
        removerow();
        bindData.subtotal=parseFloat(0.00);
        bindData.InvItems.forEach(element => {
            bindData.subtotal+=parseFloat(element.total);
        });
        bindData.subtotal=parseFloat(bindData.subtotal).toFixed(2);
        bindData.taxamount=parseFloat(parseFloat(bindData.subtotal)*(parseFloat(bindData.tax)/100)).toFixed(2);
        bindData.total= parseFloat(parseFloat(bindData.subtotal)+parseFloat(bindData.taxamount)).toFixed(2);
       
    }

    function removerow(){
        var additem=true;
        var arr = [];

        bindData.InvItems.forEach(element => {
            if(element.itemid==0){
             additem=false;
            }else{
                arr.push(element);
            }
        });
        bindData.InvItems=arr;
        bindData.InvItems.push({itemid:0,name:"",uom:"",qty:0,price:parseFloat("0").toFixed(2),total:parseFloat("0").toFixed(2),selected:null,invtype:"",catogory:""});
        //console.log(arr);
    }
    var vueData = {
        onReady: function(){
            initializeComponent();
        },
        data:bindData,
        methods: {
            save:saveInvoice,
            savePreview:savePreview,
            savePreviewCancel:function(){bindData.invoiceSave=false;},
            onFileChange: function(e) {
                var files = e.target.files || e.dataTransfer.files;
                if (!files.length)
                    return;
                this.createImage(files[0]);
            },
            navigateBack: function(){
                handler1 = exports.getShellComponent("soss-routes");
                handler1.appNavigate("..");
            },
            itemselect:function(item){
                console.log(JSON.stringify(item));
                if(item.selected!==""){ 
                    item.itemid=item.selected.itemid;
                    item.name=item.selected.name;
                    item.qty=parseFloat(1);
                    item.price=parseFloat(item.selected.price).toFixed(2);
                    item.uom=item.selected.uom;
                    item.total=parseFloat(item.price*item.qty).toFixed(2);
                    item.invtype=item.selected.invType;
                    item.catogory=item.selected.catogory;
                    
                }else{
                    item.itemid=0;
                    item.name="";
                    item.qty=0;
                    item.price=0;
                    item.uom="";
                    item.total=0;
                    item.invtype="";
                    item.catogory="";
                }

               calcTotals();
                
            },
            itemsQtyChange:function(item){
                
               item.total=parseFloat(item.price)*parseFloat(item.qty);
               calcTotals();
                
            }
            ,
            taxChange:calcTotals,
            print:function(){
                var prtContent=document.getElementById("printcontent");
                var WinPrint = window.open('', '', 'left=0,top=0,width=800,height=900,toolbar=0,scrollbars=0,status=0');
                WinPrint.document.open('text/html');
                WinPrint.document.write('<link href="//netdna.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css"><div style="margin: 30px;"> '+prtContent.innerHTML+'</div>');
                WinPrint.document.close();
                WinPrint.focus();
                setTimeout(function(){ WinPrint.print();WinPrint.close(); }, 3000);
            }
        }
    }
    
    exports.vue = vueData;
    exports.onReady = function(element){
    }
    var productHandler;
    var profileHandler;
    var uploaderInstance;
    var pInstance;

   

    function initializeComponent(){
        pInstance = exports.getShellComponent("soss-routes");
        routeData = pInstance.getInputData();
        profileHandler = exports.getComponent("profile");
        sossdata = exports.getShellComponent("soss-data");
        uploaderInstance = exports.getComponent ("soss-uploader");
        if(routeData.tid!=null){
            var query=[{storename:"orderheader",search:"invoiceNo:"+routeData.tid},{storename:"orderdetails",search:"invoiceNo:"+routeData.tid}];
                    profileHandler.services.q(query)
                    .then(function(r){
                        console.log(JSON.stringify(r));
                        if(r.success){
                            if(r.result.orderheader.length!=0){
                                bindData.InvoiceToSave=r.result.orderheader[0];
                                bindData.InvoiceToSave.InvoiceItems=r.result.orderdetails;
                                bindData.invoiceSave=true;
                            }
                            return;
                            //calcTotals();
                            
                        }
                    })
                    .error(function(error){
                        console.log(error.responseJSON);
            });
            //getProfilebyID(routeData.id)
        }
        var query=[{storename:"products",search:"showonstore:Y"}];
        //productHandler = exports.getComponent("product");
        sossdata.services.q(query)
                    .then(function(r){
                        console.log(JSON.stringify(r));
                        if(r.success){
                            if(r.result.products.length!=0){
                                bindData.products=r.result.products;
                               
                            }
                            return;
                            //calcTotals();
                            
                        }
                    })
                    .error(function(error){
                        console.log(error.responseJSON);
            });
        
        
        
        if(routeData.id!=null){
            getProfilebyID(routeData.id)
        }
        console.log(routeData);
    }

    

    

    

    function fDate(d){
        var datestring = (d.getMonth()+1)  + "-" + d.getDate() + "-" + d.getFullYear() + " " + d.getHours() + ":" + d.getMinutes() + ":00";
        return datestring;
    }

    function savePreview(){
        //var d = ;

        bindData.InvoiceToSave={
            invoiceNo:0,
            invoiceDate:fDate(bindData.date),
            invoiceDueDate:fDate(bindData.duedate),
            profileId:bindData.i_profile.id,
            email:bindData.i_profile.email,
            contactno:bindData.i_profile.contactno,
            name:bindData.i_profile.name,
            address:bindData.i_profile.address,
            city:bindData.i_profile.city,
            country:bindData.i_profile.country,
            subtotal:bindData.subtotal,
            total:bindData.total,
            tax:bindData.tax,
            taxamount:bindData.taxamount,
            paidamount:0,
            status:"Approved",
            detailsString:null,
            InvoiceItems:[]
        }
        bindData.InvItems.forEach(element => {
            if(element.itemid!=0){
                console.log(JSON.stringify(element));
                bindData.InvoiceToSave.InvoiceItems.push(
                    {
                        invoiceNo:0,
                        itemid:element.itemid,
                        name:element.name,
                        uom:element.uom,qty:element.qty,
                        price:element.price,
                        total:element.total,
                        invType:element.invtype,
                        catogory:element.catogory
                    }
                )
            }
        });

        bindData.InvItems.detailsString=JSON.stringify(bindData.InvoiceToSave.InvoiceItem);
        console.log(JSON.stringify(bindData.InvoiceToSave));
        bindData.invoiceSave=true;
    }
    function saveInvoice(){
        $('#send').prop('disabled', true);
        //console.log(JSON.stringify(bindData.InvoiceToSave));
        //return;
        profileHandler.services.InvoiceSave(bindData.InvoiceToSave)
        .then(function(response){
            //console.log(JSON.stringify(response));
            
            if(response.success){
                //console
                $.notify("invoice Has been generated", "success");
                bindData.InvoiceToSave=response.result;
                
            }else{
                $.notify("Error! Savining Error", "error");
                console.log(JSON.stringify(response.result));
                $('#send').prop('disabled', false);
                //alert (response.result.error);
            }
        })
        .error(function(error){
            $.notify("Error! Savining Error please check your intenet connection", "error");
            console.log(JSON.stringify(error));
            $('#send').prop('disabled', false);
        });
    }

    function getProfilebyID(id){
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

    
});
