<?php

class DavvagFlow {
    public static function Log(&$logObject,$logtype,$message){
        if(!isset($logObject->excutionStack->{$logtype})){
            $logObject->excutionStack->{$logtype}=array();
        }
        $m=array(
            "date"=>date("Y-m-d h:i:sa"),
            "message"=>$message
        );
        array_push($logObject->excutionStack->{$logtype},$m);
    }
    
    public static function Execute($ns,$flowid,$inputData,$step=null,$excuteData=null,$workflow=null){
        if(!isset($ns)){
            $filename= TENANT_RESOURCE_LOCATION."/davvag-flow/".$flowid.".json";
        }else{
            $filename= TENANT_RESOURCE_LOCATION."/davvag-flow/".$ns."/".$flowid.".json";
        }
        //$excuteData=$scopData;
        if(!isset($excuteData)){
            $excuteData=new stdClass();
        }
        if(!isset($workflow)){
            if(file_exists($filename)){
                $data=file_get_contents($filename);
                $workflow =json_decode($data);
                $excuteData->excutionStack=new stdClass();
                $excuteData->outData=new stdClass();
                $excuteData->excutionStack->workFlowId=uniqid();
                $inputData->workflowid=$excuteData->excutionStack->workFlowId;
                DavvagFlow::Log($excuteData,"debugLog","workflow Initaited id:".$excuteData->excutionStack->workFlowId);
            }else{
                throw new Exception('workflow not found.');
            }
        }
        if(!isset($step) || $step===""){
            $step=$workflow->start_up_node;
        }   

        if($workflow!=null){
                if(!isset($workflow->{$step})){
                    throw new Exception("[$step] This step is not configured");
                }
                $objNode=$workflow->{$step};
                DavvagFlow::Log($excuteData,"debugLog","Step exuction ".$step);
                try{
                    if($objNode->method->return){
                        DavvagFlow::Log($excuteData,"debugLog","invoke Method [".$objNode->method->name. "] with return value [".$objNode->method->returnobj ."] [Node]" );
                        $excuteData->outData->{$objNode->method->returnobj}= DavvagFlow::ExcuteNode($inputData,$excuteData,$objNode);
                    }else{
                        DavvagFlow::Log($excuteData,"debugLog","invoke Method [".$objNode->method->name. "] with out return value [Node]");

                        DavvagFlow::ExcuteNode($inputData,$excuteData,$objNode);
                    }
                    if(isset($objNode->success)){
                        $excuteData=DavvagFlow::Execute($ns,$flowid,$inputData,$objNode->success,$excuteData,$workflow);
                    }else{
                         //$excuteData;
                    }
                }catch(Exception $e){
                    if(isset($objNode->fail)){
                        DavvagFlow::Log($excuteData,"errorLog","invoke Method [".$objNode->method->name. "] failed Error [".$e->getMessage() ."] going to fail Step[".$objNode->fail);

                        $excuteData=DavvagFlow::Execute($ns,$flowid,$inputData,$objNode->fail,$excuteData,$workflow);
                    }else{
                        DavvagFlow::Log($excuteData,"errorLog","invoke Method [".$objNode->method->name. "] failed Error [".$e->getMessage() ."] Exit ");
                        throw $e;
                    }
                }

           
        }else{
            throw new Exception('Davvag Flow Decode failed.');
        }

        $excuteData->inputData=$inputData;
        return $excuteData;
    }

    public static function ExcuteNode($inputData,$scopData,$node){
        switch($node->urntype){
            case "class":
                if(file_exists(PLUGIN_PATH."/davvag-flow/lib/".$node->file)){  
                    require_once(PLUGIN_PATH."/davvag-flow/lib/".$node->file);
                    $arr = array();
                    foreach ($node->method->params as $para) {
                        if(isset($para->inputData)){
                            if(isset($inputData->{$para->inputData})){
                                array_push($arr,$inputData->{$para->inputData});
                            }else{
                                if($para->inputData===""){
                                    array_push($arr,$inputData);
                                }else{
                                    DavvagFlow::Log($scopData,"errorLog","invoke Method [".$node->method->name. "] input Parameter not found  [".$para->inputData ."]");
                                    throw new Exception('Requested input Parameter not found {'.$para->inputData.'}');
                                }
                            }
                        }else if(isset($para->scopData)){
                            if(isset($scopData->{$para->scopData})){
                                array_push($arr,$scopData->{$para->scopData});
                            }else{
                                if($para->scopData===""){
                                    array_push($arr,$scopData);
                                }else{
                                    DavvagFlow::Log($scopData,"errorLog","invoke Method [".$node->method->name. "] input Parameter not found  [".$para->scopData ."]");
                                    throw new Exception('Requested input Parameter not found {'.$para->scopData.'}');
                                }
                            }
                        }else{
                            array_push($arr,$para);
                        }
                    }
                    try{
                        //var_dump($arr);
                        return call_user_func_array(array($node->class,$node->method->name),$arr);
                    }catch (Exception $e1){
                        throw $e1;
                    }
                }else{
                    throw new Exception('Activity Not Found.');
                }
            break;
            default:
                throw new Exception('URN Type is not implemented');
                break;
        }
       
    }

}
?>