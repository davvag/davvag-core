<?php
require_once(PLUGIN_PATH . "/sossdata/SOSSData.php");
require_once(PLUGIN_PATH_LOCAL . "/profile/profile.php");
require_once(PLUGIN_PATH_LOCAL . "/davvag-order/davvag-order.php");
require_once (PLUGIN_PATH_LOCAL . "/profile/profile.php");

class DirectPay_IPG {

    function __construct(){
        
    } 

    public function postSave($req,$res){
      require_once(PLUGIN_PATH_LOCAL . "/davvag-ipg/davvag-ipg.php");
      $data=$req->Body(true);
      $Store_profile= Profile::getProfile(0,0);
      if($Store_profile->profile){
        $data->id=$Store_profile->profile->id;
      }else{
        $data->id=0;
      }
      
      $q=SOSSData::Query("davvag_directpay_lk","id:".$data->id);
      if($q->success){
        if(count($q->result)==0){
          
            //SOSSData::Insert("davvag_ipgs",)
            SOSSData::Insert("davvag_directpay_lk",$data);
            Davvag_IPG::SaveNewIPG("davvag-directpay-lk",$data->id,"Direct Pay","Direct Pay is a Sri Lanka Payment Gateway","http://www.directpay.lk","assets/davvag-directpay-lk/directpay.png");
            return $data;
        }else{
            SOSSData::Update("davvag_directpay_lk",$data);
            Davvag_IPG::SaveNewIPG("davvag-directpay-lk",$data->id,"Direct Pay","Direct Pay is a Sri Lanka Payment Gateway","http://www.directpay.lk","assets/davvag-directpay-lk/directpay.png");
            return $data;
        }
      }else{
        $res->SetError($q);

      }

    }

    public function getExtPaymentRequest($req,$res){
      $handler=new Davvag_Order();
      $request=$handler->ExtPaymentRequest($_GET["id"]);
      if($request!=null){
        $rForm=new stdClass();
        $rForm->email=isset($request->email)!=true?"null@null.com":$request->email;
        $rForm->contactno=isset($request->contactno)!=true?"":$request->contactno;
        $rForm->rProfileId=$request->donorId;
        $rForm->amount=$request->paymentAmount;
        $rForm->currencycode=isset($request->currencycode)!=true?"USD":$request->currencycode;
        $rForm->refId=$request->id;
        $rForm->name=$request->name;
        $rForm->ExtReq=$request;
        $rForm->ExtType='extern_reciept';
        $rForm->appkey="ddde7400a6bce53296bfe8e41b2b18fd9691abc2585927d1e5a1501339ec6fd2";
        $rForm->mainkey="OD04597";
        $keys=$this->DirectPaykeys((isset($request->supplier_profileId)?$request->supplier_profileId:0));
        ///return $keys;
        if(isset($keys)){
          $rForm->mainkey= $keys->mainkey;
          $rForm->appkey= $keys->appkey;
        }
        return $rForm;

      }else{
        return null;
      }
    }

    public function postPayment($req,$res){
      $body=$req->Body(true);
      $handler=new Davvag_Order();
      switch($body->ExtType){
        case "extern_reciept":
            $results=$body->ExtResults;
            //return $results;
            $payment= $body->ExtReq;
            $payment->paymentType="Onine Payment";
            $payment->OnlinePotal="Direct Pay";
            $payment->OnlineTranID=$results->data->transactionId;
            $payment->amount=$results->data->amount;
            $payment->detailsString=json_encode($results);
            
            $r=$handler->ConfirmExtPayment($payment);
            return $r;
        break;
        default:
            $res->SetError("Unautherized ");
        break;
      }
      return $body;

    }

    public function getOrder($req,$res){
      $handler=new Davvag_Order();
      $userprofile=Profile::getUserProfile();
      $order= $handler->getOrder($_GET["id"]);
      if($order==null || $order->profileId!=$userprofile->profile->id){
        $res->SetError ("Error Loading Order");
        return null;
      }else{
        $rForm=new stdClass();
        $rForm->email=isset($order->email)!=true?"null@null.com":$order->email;
        $rForm->contactno=isset($order->contactno)!=true?"":$order->contactno;
        $rForm->rProfileId=$order->profileId;
        $rForm->amount=$order->total;
        $rForm->currencycode=isset($order->currencycode)!=true?"USD":$order->currencycode;
        $rForm->refId=$order->invoiceNo;
        $rForm->name=$order->name;
        $rForm->ExtType='order';
        $rForm->ExtReq=$order;
        $rForm->appkey="ddde7400a6bce53296bfe8e41b2b18fd9691abc2585927d1e5a1501339ec6fd2";
        $rForm->mainkey="OD04597";
        $keys=$this->DirectPaykeys((isset($order->supplier_profileId)?$order->supplier_profileId:0));
        ///return $keys;
        if(isset($keys)){
          $rForm->mainkey= $keys->mainkey;
          $rForm->appkey= $keys->appkey;
        }
        return $rForm;
        
        
      }
    }

    public function getPublicToken($req,$res)
    {
      # code...
      if(isset($_GET["id"])){ 
        $keys=$this->DirectPaykeys($_GET["id"]);
        //return $keys;
        if(isset($keys)){
          return $keys;
        }else{
          return null;
        }
      }else{
        return "dddd";
      }
    }

    private function DirectPaykeys($id){
      $cache=CacheData::getObjects($id,"davvag_directpay_lk");
      if(isset($cache)){
        return $cache;
      }
      $q=SOSSData::Query("davvag_directpay_lk","id:".$id);
      //return $q;
      if($q->success){
        if(count($q->result)>0){
          CacheData::setObjects($id,"davvag_directpay_lk",$q->result[0]);
          return $q->result[0];
        }else{
          return null;
        }
      }else{
        return null;
      }

    }

}

?>