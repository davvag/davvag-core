WEBDOCK.component().register(function(exports){
    var bindData = {
        i_profile:{},
        InvItems:[],
        products:[],
        subtotal:0,
        tax:0,
        taxamount:0,
        total:0,
        paidamount:0,
        paymenttype:"Cash",
        date:new Date(),
        duedate:new Date(),
        invoiceSave:false,
        InvoiceToSave:{}
    };

    function calcTotals(){
        bindData.subtotal=parseFloat(0.00);
        bindData.InvItems.forEach(element => {
            bindData.subtotal+=parseFloat(element.balance);
        });
        bindData.subtotal=parseFloat(bindData.subtotal).toFixed(2);
        //bindData.taxamount=parseFloat(parseFloat(bindData.subtotal)*(parseFloat(bindData.tax)/100)).toFixed(2);
        bindData.total= parseFloat(parseFloat(bindData.subtotal)-parseFloat(bindData.paidamount)).toFixed(2);
       
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
                    /*
                    if(item.itemid==0){
                        bindData.products.forEach(function(value,i){
                            if(value.itemid==item.selected.itemid){
                                bindData.products.splice(i,1);
                                return;
                            }
                        });
                    }*/
                    
                    item.itemid=item.selected.itemid;
                    item.name=item.selected.name;
                    item.qty=parseFloat(1);
                    item.price=parseFloat(item.selected.price).toFixed(2);
                    item.uom=item.selected.uom;
                    item.total=parseFloat(item.price*item.qty).toFixed(2);
                    
                }else{
                    item.itemid=0;
                    item.name="";
                    item.qty=0;
                    item.price=0;
                    item.uom="";
                    item.total=0;
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
        },
        filters: {
            currency: function (value) {
              if (!value) return ''
              value = value.toString()
              return parseFloat(value).toFixed(2);
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
        profileHandler = exports.getComponent("profile");
        uploaderInstance = exports.getComponent ("soss-uploader");
        pInstance = exports.getShellComponent("soss-routes");
        routeData = pInstance.getInputData();
        if(routeData.tid!=null){
            var query=[{storename:"paymentheader",search:"receiptNo:"+routeData.tid},{storename:"paymentdetails",search:"receiptNo:"+routeData.tid}];
                    profileHandler.services.q(query)
                    .then(function(r){
                        console.log(JSON.stringify(r));
                        if(r.success){
                            if(r.result.paymentheader.length!=0){
                                bindData.InvoiceToSave=r.result.paymentheader[0];
                                bindData.InvoiceToSave.InvoiceItems=r.result.paymentdetails;
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
            receiptNo:0,
            receiptDate:fDate(bindData.date),
            profileId:bindData.i_profile.id,
            email:bindData.i_profile.email,
            contactno:bindData.i_profile.contactno,
            name:bindData.i_profile.name,
            address:bindData.i_profile.address,
            city:bindData.i_profile.city,
            country:bindData.i_profile.country,
            outstandingAmount:bindData.subtotal,
            balanceAmount:bindData.total,
            paymentType:bindData.paymenttype,
            paymentAmount:bindData.paidamount,
            status:"Approved",
            detailsString:null,
            InvoiceItems:[]
        }
        bindData.InvItems.forEach(element => {
            if(element.itemid!=0){
                bindData.InvoiceToSave.InvoiceItems.push(
                    {
                        receiptNo:0,
                        transactionid:element.invoiceNo,
                        tranType:"Invoice",
                        description:"Invoice #"+element.invoiceNo+" Invoiced On " +element.invoiceDate,
                        DueAmount:element.balance,
                        PaidAmout:element.paidamount,
                        Balance:0
                    }
                )
            }
        });

        bindData.InvItems.detailsString=JSON.stringify(bindData.InvoiceToSave.InvoiceItem);
        bindData.invoiceSave=true;
    }
    function saveInvoice(){
        console.log(JSON.stringify(bindData.InvoiceToSave));
        //return;
        profileHandler.services.PaymentSave(bindData.InvoiceToSave)
        .then(function(response){
            console.log(JSON.stringify(response));
            
            if(response.success){
                //console
                bindData.InvoiceToSave=response.result;
                
            }else{
                alert (response.result.error);
            }
        })
        .error(function(error){
            alert (error.responseJSON.result);
            console.log(error.responseJSON);
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
                    bindData.i_profile=response.result[0];
                    bindData.p_image = 'components/dock/soss-uploader/service/get/profile/'+bindData.i_profile.id;
                    var query=[{storename:"orderheader",search:"profileid:"+id+",PaymentComplete:N"}];
                    profileHandler.services.q(query)
                    .then(function(r){
                        console.log(JSON.stringify(r));
                        if(r.success){

                            bindData.InvItems=r.result.orderheader;
                            calcTotals();
                            
                        }
                    })
                    .error(function(error){
                        console.log(error.responseJSON);
                    });
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
