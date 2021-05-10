<?php
require_once (PLUGIN_PATH . "/sossdata/SOSSData.php");
require_once (PLUGIN_PATH . "/phpcache/cache.php");
require_once (PLUGIN_PATH . "/auth/auth.php");
class OrderService {
    
    
    public function getAllPendingOrders($req,$res){
        $user= Auth::Autendicate("rider-orders","getAllPendingOrders",$res);
        //$data =Auth::CrossDomainAPICall(MAIN_STORE_DOMAIN,"/components/raha/order-handler/service/AllPendingOrder?tname=".HOST_NAME);
        $data = SOSSData::Query ("rider_orders_pending", urlencode("rideremail:".$user->email.""));
        if($data->success){
            return $data->result;
        }else{
            return null;
        }
    }

    

    public function postApproveOrder($req,$res){
        $user= Auth::Autendicate("profile","postInvoiceSave",$res);
        $body =$req->Body(true);
        $postbody=new stdClass();
        $postbody->id=$body->invoiceNo;
        $postbody->order=$body;
        $postbody->status="taken";
        
        $data =Auth::CrossDomainAPICall(MAIN_STORE_DOMAIN,"/components/raha/order-handler/service/OrderChange","POST",$postbody);
        if($data->success){
            $Invoice=$data->result;   
            $Invoice->tid=$Invoice->invoiceNo;
            $Invoice->tenant=MAIN_STORE_DOMAIN;
            unset($Invoice->invoiceNo);
            $Invoice->status="pending";
            $profile =new stdClass();
            $profile->email=$Invoice->email;
            $profile->contactno=$Invoice->contactno;
            $profile->address=$Invoice->address;
            $profile->city=$Invoice->city;
            $profile->country=$Invoice->country;
            $profile->name=$Invoice->name;
            $profile->status='pending';
            $profile=$this->createProfile($profile);
            if(!isset($profile->id)){
                $res->SetError("Profile Creation Error");
                $Invoice->profileId=0;
                //return $profile;
                
            }else{
                $Invoice->profileId=$profile->id;
            }
            
            $result=$this->InvoiceSave($Invoice,$user);
                //rider-orders_pending
            if(isset($result->invoiceNo)){
                if(isset($body->rideremail)){
                    // save rider orders
                    $riderorder=new stdClass();
                    $riderorder->invoiceNo=$result->invoiceNo;
                    $riderorder->rideremail=$body->rideremail;
                    $riderorder->tid=$result->tid;
                    $riderorder->tenant=$result->tenant;
                    $riderorder->profileId=$result->profileId;
                    $riderorder->name=$result->name;
                    $riderorder->email=$result->email;
                    $riderorder->city=$result->city;
                    $riderorder->address=$result->address;
                    $riderorder->country=$result->country;
                    $riderorder->lon=$result->lon;
                    $riderorder->lat=$result->lat;
                    $riderorder->remarks=$result->remarks;
                    $riderorder->invoiceDate=$result->invoiceDate;
                    $riderorder->invoiceDueDate=$result->invoiceDueDate;
                    $riderorder->total=$result->total;
                    $riderorder->paidamount=$result->paidamount;
                    $riderorder->balance=$result->balance;
                    $riderorder->preparedByID=$user->userid;
                    $riderorder->preparedBy=$user->email;
                    $r=SOSSData::Insert ("rider_orders_pending", $riderorder);
                    
                }
                return $result;
            }else{
                $res->SetError("Error Processing...");
                return $result;
            }
            
                
        }else{
            return null;
        }
    }

    public function postRejectOrder($req,$res){
        $user= Auth::Autendicate("profile","postInvoiceSave",$res);
        $body =$req->Body(true);
        $postbody=new stdClass();
        $postbody->id=$body->invoiceNo;
        $postbody->order=$body;
        $postbody->status="cancel";
        
        $data =Auth::CrossDomainAPICall(MAIN_STORE_DOMAIN,"/components/raha/order-handler/service/OrderChange","POST",$postbody);
        if($data->success){
            return $data;
        }else{
            return null;
        }
    }

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

    private function createProfile($profile){
        $result = SOSSData::Query ("profile", urlencode("email:".$profile->email.""));
        if($result->success){
            if(count($result->result)!=0)
            {
                return $result->result[0];
            }else{
                $result = SOSSData::Insert ("profile", $profile,$tenantId = null);
                if($result->success){
                    $profile->id = $result->result->generatedId;
                    return $profile;
                }else{
                    return null;
                }

            }
        }else{
            return null;
        }
    }

    private function InvoiceSave($Transaction,$user){
        
        
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
                        
                        $this->updateInventry($value,-1);
                    }
                    //return $profileservices;
                    $result = SOSSData::Insert ("orderdetails", $Transaction->InvoiceItems,$tenantId = null);
                    
                    //return $result;
                    
                    CacheData::clearObjects("orderdetails");
                }else{
                    //$res->SetError ("Erorr");
                    return $result;
                }
                //unset($value); 
                
                
                return $Transaction;
            }else{
                return $result;
            }
       
        
        
    }

    private function updateInventry($value,$s){
        //if(strtolower($value->invType)=="inventry"){
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
        //}
    }
}

?>