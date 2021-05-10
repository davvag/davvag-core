WEBDOCK.component().register(function(exports){
    var bindData = {
        i_profile:{},
        InvItems:[{itemid:0,name:"",uom:"",qty:0,price:parseFloat("0").toFixed(2),total:parseFloat("0").toFixed(2),selected:null}],
        products:[],
        riders:[],
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
            },
            approve:function(){
                Instance = exports.getComponent ("crossdomainorder");
                Instance.services.ApproveOrder(bindData.InvoiceToSave)
                .then(function(response){
                    console.log(JSON.stringify(response));
                    if(response.success){
                        //console
                        //bindData.item.id=response.result.result.generatedId;
                        //bindData.items=[];
                        
                        bindData.InvoiceToSave=response.result;
                        handler1 = exports.getShellComponent("soss-routes");
                        handler1.appNavigate("..");
                        console.log(response);
                        
                    }else{
                        alert (response.error);
                    }
                })
                .error(function(error){
                    alert (error.responseJSON.result);
                    console.log(error.responseJSON);
                });
            },
            cancel:function(){
                Instance = exports.getComponent ("crossdomainorder");
                Instance.services.RejectOrder(bindData.InvoiceToSave)
                .then(function(response){
                    console.log(JSON.stringify(response));
                    if(response.success){
                        bindData.InvoiceToSave=response.result;
                        handler1 = exports.getShellComponent("soss-routes");
                        handler1.appNavigate("..");
                        console.log(response);
                        
                    }else{
                        alert (response.error);
                    }
                })
                .error(function(error){
                    alert (error.responseJSON.result);
                    console.log(error.responseJSON);
                });
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
        

        if(routeData.xid!=null){
            var menuhandler  = exports.getShellComponent("soss-data");
            var query={query:[{storename:"orderheader_pending",search:"invoiceNo:"+routeData.xid},{storename:"orderdetails_pending",search:"invoiceNo:"+routeData.xid}]};
                 menuhandler.services.qcrossdomain(query)
                    .then(function(r){
                        //console.log(JSON.stringify(r));
                        if(r.success){
                            if(r.result.orderheader_pending.length!=0){
                                bindData.InvoiceToSave=r.result.orderheader_pending[0];
                                bindData.InvoiceToSave.InvoiceItems=r.result.orderdetails_pending;
                                bindData.invoiceSave=true;
                                var map = new google.maps.Map(document.getElementById('map'), {
                                    zoom: 8,
                                    center: {lat: bindData.InvoiceToSave.lat, lng: bindData.InvoiceToSave.lon}
                                  });
                                  var geocoder = new google.maps.Geocoder;
                                  var infowindow = new google.maps.InfoWindow;
                                  var latlng = {lat: parseFloat(bindData.InvoiceToSave.lat), lng: parseFloat(bindData.InvoiceToSave.lon)};
                                  geocoder.geocode({'location': latlng}, function(results, status) {
                                    if (status === 'OK') {
                                      if (results[0]) {
                                        map.setZoom(11);
                                        var marker = new google.maps.Marker({
                                          position: latlng,
                                          map: map
                                        });
                                        infowindow.setContent(results[0].formatted_address);
                                        bindData.InvoiceToSave.address=results[0].formatted_address;
                                        bindData.InvoiceToSave.country=results[results.length-1].formatted_address;
                                        infowindow.open(map, marker);
                                        console.log(results);
                                      } else {
                                        window.alert('No results found');
                                      }
                                    } else {
                                      window.alert('Geocoder failed due to: ' + status);
                                    }
                                  });
                            }
                            return;
                            //calcTotals();
                            
                        }
                    })
                    .error(function(error){
                        console.log(error.responseJSON);
            });

             
            query=[{storename:"rider",search:""}];
            menuhandler.services.q(query)
                    .then(function(r){
                        console.log(JSON.stringify(r));
                        if(r.success){
                            bindData.riders=r.result.rider;
                            return;
                        }
                    })
                    .error(function(error){
                        console.log(error.responseJSON);
            });
            //getProfilebyID(routeData.id)
        }
        
    }


   

    
});
