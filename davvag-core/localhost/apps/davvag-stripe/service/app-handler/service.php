<?php
require_once(PLUGIN_PATH . "/sossdata/SOSSData.php");
require_once(PLUGIN_PATH_LOCAL . "/profile/profile.php");
require_once(PLUGIN_PATH_LOCAL . "/stripe/init.php");
require_once(PLUGIN_PATH_LOCAL . "/davvag-order/davvag-order.php");
use \Stripe\Stripe;
use \Stripe\Customer;
use \Stripe\ApiOperations\Create;
use \Stripe\Charge;

class Stripe_IPG {

    function __construct(){
        
    } 

    
    

    public function postChargeAmountFromCard($req,$res)
    {
        $cardDetails=$req->Body(true);
        $userprofile=Profile::getUserProfile();
        if($userprofile->profile){
            $handler=new Davvag_Order();
            $order= $handler->getOrder($cardDetails->id);
            //return $order;
            if($order==null || $order->profileId!=$userprofile->profile->id){
                $res->SetError ("Error Paying the given order");
                return null;
            }
            $apiKey = "sk_test_MnfXwb7xIuh7BedaV3oK9RGO";
            $stripeService = new \Stripe\Stripe();
            $stripeService->setVerifySslCerts(false);
            $stripeService->setApiKey($apiKey);
            
            $customerDetailsAry = array(
                'email' => $userprofile->profile->email,
                'source' => $cardDetails->token
            );
            $customer = new Customer();
            $customerResult = $customer->create($customerDetailsAry);
            //return $customerResult;
            $charge = new Charge();
            $cardDetailsAry = array(
                'customer' => $customerResult->id,
                'amount' => $order->balance*100 ,
                'currency' => $order->currencycode,
                'description' => "test",
                'metadata' => array(
                    'order_id' => $order->invoiceNo
                )
            );
            //return $cardDetailsAry;
            try {
                $result = $charge->create($cardDetailsAry);
                return $handler->PayOrder($order->invoiceNo,$order->balance,"Payed via Stripe. url :[".$result->receipt_url."]","Stripe IPG",$result->id);
                //return $result;
            } catch(\Stripe\Exception\CardException $e) {
                // Since it's a decline, \Stripe\Exception\CardException will be caught
                 
                $res->SetError( $e->getError()->message);
                return null;
              } catch (\Stripe\Exception\RateLimitException $e) {
                $res->SetError( $e->getError()->message);
                return null;
              } catch (\Stripe\Exception\InvalidRequestException $e) {
                $res->SetError( $e->getError()->message);
                return null;
              } catch (\Stripe\Exception\AuthenticationException $e) {
                $res->SetError( $e->getError()->message);
                return null;
              } catch (\Stripe\Exception\ApiConnectionException $e) {
                $res->SetError( $e->getError()->message);
                return null;
              } catch (\Stripe\Exception\ApiErrorException $e) {
                $res->SetError( $e->getError()->message);
                return null;
              } catch (Exception $e) {
                $res->SetError( $e);
                return null;
              }
            
        }
        
    }


}

?>