<?php
require_once(PLUGIN_PATH . "/auth/auth.php");
require_once(PLUGIN_PATH . "/sossdata/SOSSData.php");
class OrderService {
    public function getAllPendingOrder($req,$res){
        if(isset($_GET["tname"])){
            $result = SOSSData::Query ("orderheader_pending", urlencode("tenant:".$_GET["tname"].""));
            if($result->success){
                return $result->result;
            }else{
                $res->SetError ("Order Not Found to Approve");
                return null;
            }
        }else{
            $res->SetError ("Wrong Request.");
            return null;
        }
    }

    public function postOrderChange($req,$res){
        $data=$req->Body(true);
        $user= Auth::AutendicateDomain($req->headers()->rhost,$req->headers()->sosskey,"OrderChange","psot");
        if(isset($user->userid)){
            switch($data->status){
                case "taken":
                    return $this->orderTaken($data->id,$data->order,$req->headers()->rhost,$user);
                    break;
                case "cancel":
                    return $this->orderCancelled($data->id,$data->order,$req->headers()->rhost,$user);
                    break;
                default:
                    return null;
                    break;
            }
            
        }

    }

    private function pendingOrders($id,$tenant){
        $result = SOSSData::Query ("orderheader_pending", urlencode("invoiceNo:".$id.""));
        if($result->success){
            if(count($result->result)!=0){
                $dbOrder=$result->result[0];

                $detailResults=SOSSData::Query ("orderdetails_pending", urlencode("invoiceNo:".$id.""));
                if($detailResults->success){
                    $dbOrder->Item=$detailResults->result;
                }
                if($tenant==$dbOrder->tenant){
                    return $dbOrder;
                }else{
                    return null;
                }

            }else{
                return null;
            }
        }else{
            return null;
        }
    }
    private function orderCancelled($id,$order,$tenant,$user){
        
        $dbOrder=$this->pendingOrders($id,$tenant);
        if(isset($dbOrder)){
            $order=$dbOrder;
            $order->preparedByID=$user->userid;
            $order->preparedBy=$user->email;
            $order->invoiceNo=0;

            $result = SOSSData::Insert ("orderheader_rejected", $order,$tenantId = null);   
            if($result->success){
                $order->invoiceNo = $result->result->generatedId;
                foreach($order->Item as $key=>$value){
                    $order->Item[$key]->invoiceNo=$order->invoiceNo;
                    //$order->Item[$key]->itemid=$order->Item[$key]->tid;
                    
                }
                $result = SOSSData::Insert ("orderdetails_rejected", $order->Item,$tenantId = null);
                $result = SOSSData::Delete ("orderdetails_pending", $dbOrder->Item,$tenantId = null);
                $result = SOSSData::Delete ("orderheader_pending", $dbOrder,$tenantId = null);
                return $order;
            }else{
                return "Error Saving";
            }
        }else{
            return "Can't Cancel this from your Tanant";
        }
    }

    private function orderTaken($id,$order,$tenant,$user){
        
                $dbOrder=$this->pendingOrders($id,$tenant);
                if(isset($dbOrder)){
                    //$this->pendingOrders($id,$tenant);
                    $order->preparedByID=$user->userid;
                    $order->preparedBy=$user->email;
                    $order->PaymentComplete="N";
                    $order->balance=$order->total;
                    $order->invoiceNo=0;

                    $result = SOSSData::Insert ("orderheader_accepted", $order,$tenantId = null);   
                    if($result->success){
                        $order->invoiceNo = $result->result->generatedId;
                        foreach($order->InvoiceItems as $key=>$value){
                            $order->InvoiceItems[$key]->invoiceNo=$order->invoiceNo;
                            $order->InvoiceItems[$key]->itemid=$order->InvoiceItems[$key]->tid;
                            
                        }
                        $result = SOSSData::Insert ("orderdetails_accepted", $order->InvoiceItems,$tenantId = null);
                        $result = SOSSData::Delete ("orderdetails_pending", $dbOrder->Item,$tenantId = null);
                        $result = SOSSData::Delete ("orderheader_pending", $dbOrder,$tenantId = null);
                        return $order;
                    }else{
                        return "Error Saving";
                    }
                }else{
                    return "Cann't Aprove this from your Tanant";
                }
    }
}

?>