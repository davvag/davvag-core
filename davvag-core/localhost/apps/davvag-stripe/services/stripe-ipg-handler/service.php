<?php
require_once(PLUGIN_PATH . "/sossdata/SOSSData.php");
require_once(PLUGIN_PATH_LOCAL . "/profile/profile.php");
require_once(PLUGIN_PATH_LOCAL . "/stripe/init.php");

class Stripe_IPG {

    function __construct(){
        
    } 

    
    public function addCustomer($customerDetailsAry)
    {
        
        $customer = new Customer();
        
        $customerDetails = $customer->create($customerDetailsAry);
        
        return $customerDetails;
    }

    public function postchargeAmountFromCard($req,$res)
    {
        $userprofile=Profile::getUserProfile();
        if($userprofile->profile){
            $apiKey = "STRIPE_SECRET_KEY";
            $stripeService = new \Stripe\Stripe();
            $stripeService->setVerifySslCerts(false);
            $stripeService->setApiKey($apiKey);
            
            $customerDetailsAry = array(
                'email' => $userprofile->profile->email,
                'source' => $userprofile->profile->id
            );
            $customer = new Customer();
            $customerResult = $customer->create($customerDetailsAry);
            $charge = new Charge();
            $cardDetailsAry = array(
                'customer' => $customerResult->id,
                'amount' => $cardDetails['amount']*100 ,
                'currency' => $cardDetails['currency_code'],
                'description' => $cardDetails['item_name'],
                'metadata' => array(
                    'order_id' => $cardDetails['item_number']
                )
            );
            $result = $charge->create($cardDetailsAry);

            return $result->jsonSerialize();
        }
        
    }


}

?>