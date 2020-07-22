WEBDOCK.component().register(function(exports){
    var bindData = {
        i_profile:{},
        InvItems:[{itemid:0,name:"",uom:"",qty:0,price:parseFloat("0").toFixed(2),total:parseFloat("0").toFixed(2),selected:null}],
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

    

    var vueData = {
        onReady: function(){
            initializeComponent();
        },
        data:bindData,
        methods: {
            navigateBack: function(){
                handler1 = exports.getShellComponent("soss-routes");
                handler1.appNavigate("..");
            },
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
  
    var profileHandler;
    var pInstance;

   

    function initializeComponent(){
        pInstance = exports.getShellComponent("soss-routes");
        routeData = pInstance.getInputData();
        profileHandler = exports.getComponent("profile");
        //profileHandler = exports.getShellComponent("soss-data");
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

        if(routeData.xid!=null){
            var menuhandler  = exports.getShellComponent("soss-data");
            var query={query:[{storename:"orderheader_pending",search:"invoiceNo:"+routeData.xid},{storename:"orderdetails_pending",search:"invoiceNo:"+routeData.xid}]};
                 menuhandler.services.qcrossdomain(query)
                    .then(function(r){
                        console.log(JSON.stringify(r));
                        if(r.success){
                            if(r.result.orderheader_pending.length!=0){
                                bindData.InvoiceToSave=r.result.orderheader_pending[0];
                                bindData.InvoiceToSave.InvoiceItems=r.result.orderdetails_pending;
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
        
    }


   

    
});
