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
    
    var profileHandler;
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
        
    }

    

    

    



    

    
});
