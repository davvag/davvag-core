<?php
require_once(PLUGIN_PATH . "/sossdata/SOSSData.php");
require_once(PLUGIN_PATH . "/phpcache/cache.php");
require_once(PLUGIN_PATH . "/auth/auth.php");
class ProfileService{
    //public var $appname="profileapp";
    private function updateLedger($ledgertran){
        $Transaction=$ledgertran;
        $result=SOSSData::Insert ("ledger", $ledgertran,$tenantId = null);
        $result = SOSSData::Query ("profilestatus", urlencode("profileid:".$Transaction->profileid.""));
        CacheData::clearObjects("profilestatus");
        CacheData::clearObjects("ledger");
        
        if(count($result->result)!=0){
            $status= $result->result[0];
            $status->outstanding+=$Transaction->amount;
            switch(strtolower($ledgertran->trantype)){
                case "invoice":
                    $status->totalInvoicedAmount+=$Transaction->amount;
                    break;
                case "receipt":
                    $status->totalPaidAmount+=$Transaction->amount;
                    break;
                case "grn":
                    $status->totalGRNAmount+=$Transaction->amount;
                    break;
                case "payment":
                    $status->totalPaymentAmount+=$Transaction->amount;
                    break;
            }
            $result=SOSSData::Update ("profilestatus", $status,$tenantId = null);
        }else{
            $status=new stdClass();
            $status->profileid=$Transaction->profileid;
            $status->outstanding=$Transaction->amount;
            $status->totalInvoicedAmount=0;
            $status->totalPaidAmount=0;
            $status->totalGRNAmount=0;
            $status->totalPaymentAmount=0;
            switch(strtolower($ledgertran->trantype)){
                case "invoice":
                    $status->totalInvoicedAmount+=$Transaction->amount;
                    break;
                case "receipt":
                    $status->totalPaidAmount+=$Transaction->amount;
                    break;
                case "grn":
                    $status->totalGRNAmount+=$Transaction->amount;
                    break;
                case "payment":
                    $status->totalPaymentAmount+=$Transaction->amount;
                    break;
            }
            $result=SOSSData::Insert ("profilestatus", $status,$tenantId = null);
                    
        }
    }

    

    public function postInvoiceSave($req,$res){
        
        $Transaction=$req->Body(true);
        $user= Auth::Autendicate("profile","postInvoiceSave",$res);
        if(!isset($Transaction->email)){
            $res->SetError ("provide email");
            
        }
        if(!isset($Transaction->contactno)){
            $res->SetError ("provide contact no");
        }
        
        $result = SOSSData::Query ("profile", urlencode("id:".$Transaction->profileId.""));
        $Transaction->status="new";
        //return $result;
        if(count($result->result)!=0)
        {
            $Transaction->preparedByID=$user->userid;
            $Transaction->preparedBy=$user->email;
            $Transaction->PaymentComplete="N";
            $Transaction->balance=$Transaction->total;
            $result = SOSSData::Insert ("orderheader", $Transaction,$tenantId = null);
            CacheData::clearObjects("orderheader");
            if($result->success){
                $Transaction->invoiceNo = $result->result->generatedId;
                $ledgertran =new StdClass;
                $ledgertran->profileid=$Transaction->profileId;
                $ledgertran->tranid=$Transaction->invoiceNo;
                $ledgertran->trantype='invoice';
                $ledgertran->tranDate=$Transaction->invoiceDate;
                $ledgertran->description='Invoice No Has been generated';
                $ledgertran->amount=$Transaction->total;
                $this->updateLedger($ledgertran);   
                
                //return $Transaction;
                if($result->success){
                
                    $profileservices=array();
                    foreach($Transaction->InvoiceItems as $key=>$value){
                        $Transaction->InvoiceItems[$key]->invoiceNo=$Transaction->invoiceNo;
                        if(strtolower($value->invType)=="service"){
                            $serviceitems =new StdClass;
                            $serviceitems->invid=$Transaction->invoiceNo;
                            $serviceitems->profileId=$Transaction->profileId;
                            $serviceitems->itemid=$value->itemid;
                            $serviceitems->name=$value->name;
                            $serviceitems->purchaseddate=$Transaction->invoiceDate;
                            $serviceitems->price=$value->total;
                            $serviceitems->catogory=$value->catogory;
                            $serviceitems->uom=$value->uom;
                            $serviceitems->qty=$value->qty;
                            $serviceitems->status="ToBeActive";
                            
                            array_push($profileservices,$serviceitems);
                        }
                        //var_dump($Transaction->InvoiceItems[$key]->invoiceNo);
                        $this->updateInventry($value,-1);
                    }
                    //return $profileservices;
                    $result = SOSSData::Insert ("orderdetails", $Transaction->InvoiceItems,$tenantId = null);
                    if(count($profileservices)!=0){
                        $result = SOSSData::Insert ("profileservices", $profileservices,$tenantId = null);
                        CacheData::clearObjects("profileservices");
                    }
                    //return $result;
                    
                    CacheData::clearObjects("orderdetails");
                }else{
                    $res->SetError ("Erorr");
                    return $result;
                }
                //unset($value); 
                
                
                return $Transaction;
            }else{
                return $result;
            }
        }else{
           //var_dump($result->response[0]->id);
           //exit();
           $res->SetError ("Invalied Profile");
           exit();
        }
        
        
    }

    private function updateInventry($value,$s){
        if(strtolower($value->invType)=="inventry"){
            $resultitems = SOSSData::Query ("product_inventrymaster", urlencode("itemid:".$value->itemid.""));//SOSSData::Insert ("", $Transaction,$tenantId = null);
            if(count($resultitems->result)!=0){
                $itemInv=$resultitems->result[0];
                if($s<0){
                    $itemInv->qty=$itemInv->qty-$value->qty;
                }else{
                    $itemInv->qty=$itemInv->qty+$value->qty;
                }
                SOSSData::Update ("product_inventrymaster", $itemInv,$tenantId = null);
            }else{
                $itemInv =new StdClass;
                $itemInv->itemid=$value->itemid;
                $itemInv->uom=$value->uom;
                if($s<0){
                    $itemInv->qty=-1*$value->qty;
                }else{
                    $itemInv->qty=$value->qty;
                }
                SOSSData::Insert ("product_inventrymaster", $itemInv,$tenantId = null);
            }
        }
    }

    public function postPOSave($req,$res){
        
        $Transaction=$req->Body(true);
        $user= Auth::Autendicate("profile","postPOSave",$res);
        if(!isset($Transaction->email)){
            $res->SetError ("provide email");
            
        }
        if(!isset($Transaction->contactno)){
            $res->SetError ("provide contact no");
        }
        
        $result = SOSSData::Query ("profile", urlencode("id:".$Transaction->profileId.""));
        
        //return $result;
        if(count($result->result)!=0)
        {
            $Transaction->preparedByID=$user->userid;
            $Transaction->preparedBy=$user->email;
            $Transaction->Complete="N";
            $Transaction->balance=$Transaction->total;
            $result = SOSSData::Insert ("poheader", $Transaction,$tenantId = null);
            CacheData::clearObjects("poheader");
            if($result->success){
                $Transaction->tranNo = $result->result->generatedId;
                if($result->success){
                    
                    $profileservices=array();
                    foreach($Transaction->InvoiceItems as $key=>$value){
                        $Transaction->InvoiceItems[$key]->tranNo=$Transaction->tranNo;
                        //var_dump($Transaction->InvoiceItems[$key]->tranNo);
                    }
                    $result = SOSSData::Insert ("podetails", $Transaction->InvoiceItems,$tenantId = null);
                    
                    CacheData::clearObjects("podetails");
                }else{
                    $res->SetError ("Erorr");
                    return $result;
                }
                
                return $Transaction;
            }else{
                return $result;
            }
        }else{
           //var_dump($result->response[0]->id);
           //exit();
           $res->SetError ("Invalied Profile");
           exit();
        }
        
        
    }

    public function postGRNSave($req,$res){
        
        $Transaction=$req->Body(true);
        $user= Auth::Autendicate("profile","postGRNSave",$res);
        
        if(!isset($Transaction->poid)){
            $res->SetError ("PO is not corrrect");
            return;
        }
        $result = SOSSData::Query ("poheader", urlencode("tranNo:".$Transaction->poid.""));
        
        //return $result;
        if(count($result->result)!=0)
        {
            $PO =$result->result[0];
            if($PO->Complete=='Y'){
                $res->SetError ("GRN Already Generated for this PO");
                return;
            }
            $Transaction->preparedByID=$user->userid;
            $Transaction->preparedBy=$user->email;
            $Transaction->Complete="N";
            $Transaction->balance=$Transaction->total;
            
            $result = SOSSData::Insert ("grnheader", $Transaction,$tenantId = null);
            CacheData::clearObjects("grnheader");
            if($result->success){
                $Transaction->tranNo = $result->result->generatedId;
                $ledgertran =new StdClass;
                $ledgertran->profileid=$Transaction->profileId;
                $ledgertran->tranid=$Transaction->tranNo;
                $ledgertran->trantype='GRN';
                $ledgertran->tranDate=$Transaction->tranDate;
                $ledgertran->description='Invoice No Has been generated';
                $ledgertran->amount=-1*$Transaction->total;
                //$result=SOSSData::Insert ("ledger", $ledgertran,$tenantId = null);
                $this->updateLedger($ledgertran);
                if($result->success){
                    
                    $profileservices=array();
                    foreach($Transaction->InvoiceItems as $key=>$value){
                        $Transaction->InvoiceItems[$key]->tranNo=$Transaction->tranNo;
                        $this->updateInventry($value,1);
                        //var_dump($Transaction->InvoiceItems[$key]->tranNo);
                    }
                    $result = SOSSData::Insert ("grndetails", $Transaction->InvoiceItems,$tenantId = null);
                    $PO->Complete='Y';
                    $result=SOSSData::Update ("poheader", $PO,$tenantId = null);
                    CacheData::clearObjects("grndetails");
                }else{
                    $res->SetError ("Erorr");
                    return $result;
                }
                
                return $Transaction;
            }else{
                return $result;
            }
        }else{
           //var_dump($result->response[0]->id);
           //exit();
           $res->SetError ("Invalied PO");
           exit();
        }
        
        
    }

    public function postPaymentSave($req,$res){
        $payment=$req->Body(true);
        $user= Auth::Autendicate("profile","postPaymentSave",$res);
        if(!isset($payment->email)){
            $res->SetError ("provide email");
            
        }
        if(!isset($payment->contactno)){
            $res->SetError ("provide contact no");
        }
        
        $result = SOSSData::Query ("profile", urlencode("id:".$payment->profileId.""));
        $payment->collectedByID=$user->userid;
        $payment->collectedBy=$user->email;
        //return $result;
        if(count($result->result)!=0)
        {
            
            $result = SOSSData::Insert ("paymentheader", $payment,$tenantId = null);
            CacheData::clearObjects("paymentheader");
           
            if($result->success){
                $payment->receiptNo = $result->result->generatedId;
                $ledgertran =new StdClass;
                $ledgertran->profileid=$payment->profileId;
                $ledgertran->tranid=$payment->receiptNo;
                $ledgertran->trantype='receipt';
                $ledgertran->tranDate=$payment->receiptDate;
                $ledgertran->description='Invoice No Has been generated';
                $ledgertran->amount=-1*$payment->paymentAmount;
                //$result=SOSSData::Insert ("ledger", $ledgertran,$tenantId = null);
                //return $payment;
                $this->updateLedger($ledgertran);
                CacheData::clearObjects("ledger");
                if($result->success){
                    $balance=$payment->paymentAmount;
                    $invUpdate=array();
                    foreach($payment->InvoiceItems as &$value){
                        $value->receiptNo=$payment->receiptNo;
                        $paymentComplete='N';
                        if($balance!=0){
                            if($balance>=$value->DueAmount){
                                $value->PaidAmout=$value->DueAmount;
                                $balance-=$value->DueAmount;
                                $value->Balance=0;
                                $paymentComplete='Y';
                            }else{
                                $value->PaidAmout=$balance;
                                $value->Balance=$value->DueAmount-$balance;
                                $balance=0;
                            }
                            $invDetails=new stdClass();
                            $invDetails->invoiceNo=$value->transactionid;
                            $invDetails->paidamount=$value->PaidAmout;
                            $invDetails->balance=$value->Balance;
                            $invDetails->PaymentComplete=$paymentComplete;
                            $result=SOSSData::Update ("orderheader", $invDetails,$tenantId = null);
                            array_push($invUpdate,$invDetails);
                        }
                    }
                    //return $invUpdate;
                    $result = SOSSData::Insert ("paymentdetails", $payment->InvoiceItems,$tenantId = null);
                    CacheData::clearObjects("paymentdetails");
                    CacheData::clearObjects("orderheader");
                    //return $result;
                }else{
                    $res->SetError ("Erorr");
                    return $result;
                }
                unset($value); 
                                
                return $payment;
            }else{
                return $result;
            }
        }else{
           //var_dump($result->response[0]->id);
           //exit();
           $res->SetError ("Invalied Profile");
           exit();
        }
        
        
    }

    public function postSave($req,$res){
        $profile=$req->Body(true);
        $user= Auth::Autendicate("profile","postSave",$res);
        if(!isset($profile->email)){
            //http_response_code(500);
            $res->SetError ("provide email");
            
        }
        if(!isset($profile->contactno)){
            //http_response_code(500);
            $res->SetError ("provide contact no");
            
        }
        //var_dump($profile);
        //exit();
        $result = SOSSData::Query ("profile", urlencode("id:".($profile->id==null?0:$profile->id).""));
        
        //return urlencode("id:".$profile->id."");
        if(count($result->result)==0)
        {
            $profile->createdate=date_format(new DateTime(), 'm-d-Y H:i:s');
            $profile->userid=$user->userid;
            $profile->status="tobeactivated";
            $result = SOSSData::Insert ("profile", $profile,$tenantId = null);
            if($result->success){
                $profile->id=$result->result->generatedId;
                if(isset($profile->attribute)){
                    $profile->attributes->id=$result->result->generatedId;

                    $r = SOSSData::Insert ("profile_attributes", $profile->attributes);
                }
            }
            CacheData::clearObjects("profile");
            return $profile;
        }else{
            $profile->attributes->id=$profile->id;
            $result = SOSSData::Update("profile", $profile);
            $result = SOSSData::Delete ("profile_attributes", $profile->attributes);
            $result = SOSSData::Insert ("profile_attributes", $profile->attributes);
            CacheData::clearObjects("profile");
            return $profile;
           
        }
        
        
    }

    public function getSearch($req){
        $s  =null;
        if(isset($_GET["q"])){
            $search  =$_GET["q"];
        }
        $result= CacheData::getObjects(md5($search),"profile");
        if(!isset($result)){
            $result = SOSSData::Query ("profile",urlencode($search));
            if($result->success){
                if(isset($result->result)){
                    CacheData::setObjects(md5($search),"profile",$result->result);
                }
            }
            return $result->result;
        }else{
            return $result;
        }
    }

    public function getByID($req){
        $s  =null;
        $search=null;
        if(isset($_GET["id"])){
            $search  = strval($_GET["id"]);
        }
        $profile=new stdClass();
        $result= CacheData::getObjects(md5($search),"profile");
        if(!isset($result)){
            $result = SOSSData::Query ("profile",urlencode("id:".$search));
            if($result->success){
                if(count($result->result)!=0){
                    $profile=$result->result[0];
                    $result = SOSSData::Query ("profile_attributes",urlencode("id:".$search));
                    $profile->attributes=$result->result[0]!=null?$result->result[0]:new stdClass();
                    //return $profile;
                    //$profile->attributes=(count($result->$result)!=0?$result->result[0]:array());
                    CacheData::setObjects(md5($search),"profile",$profile);
                }
            }
            return $profile;
        }else{
            return $result;
        }
    }

    public function postq($req){
        $sall=$req->Body(true);
        $f=new stdClass();
        foreach($sall as $s){
            $result= CacheData::getObjects(md5($s->search),$s->storename);
            if(!isset($result)){
                $result = SOSSData::Query ($s->storename,urlencode($s->search));
                if($result->success){
                    $f->{$s->storename}=$result->result;
                    if(isset($result->result)){
                        CacheData::setObjects(md5($s->search),$s->storename,$result->result);
                    }
                }else{
                    $f->{$s->storename}=null;
                }
            }else{
                $f->{$s->storename}= $result;
            }
            
        }
        return $f;
    }
    
}

?>