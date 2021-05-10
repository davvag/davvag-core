<?php
require_once(PLUGIN_PATH . "/sossdata/SOSSData.php");


class appService {

    function __construct(){
        
    } 

    public function postLike($req,$res){
        $p=$req->Body(true);
        $result = SOSSData::Query ("products", urlencode("itemid:".($p->itemid==null?0:$p->itemid).""));
        if($result->success){
            if(count($result->result)!=0){
               
                //$r = SOSSData::Query ("products_likes", urlencode("itemid:".$p->itemid.",pid:".$p->pid));
                //if(count($p->result)!=0){
                if($p->liked){
                    SOSSData::Insert("products_likes",$p);
                }else{
                    //$r = SOSSData::Query ("products_likes", urlencode("itemid:".$p->itemid.",pid:".$p->pid));
                    //return $r;
                    SOSSData::Delete("products_likes",$p);
                    return $p;
                }
                $result = SOSSData::Update("products", $result->result[0]);
                return $p;
            }else{
                $res->SetError ("invalied Like.");
                return;
            }
        }else{
            $res->SetError ($result);
            return;
        }
    }


    public function postComment($req,$res){
        $p=$req->Body(true);
        $result = SOSSData::Query ("products", urlencode("itemid:".($p->itemid==null?0:$p->itemid).""));
        if($result->success){
            if(count($result->result)!=0){
                $r=SOSSData::Insert("products_comments",$p);
                if($r->success){
                    $p->id= $r->result->generatedId;
                    return $p;
                }else{
                    $res->SetError ($r);
                    return;
                }
                 
            }else{
                $res->SetError ("invalied Favorite.");
                return;
            }
        }else{
            $res->SetError ($result);
            return;
        }
        
    }

    public function getAllComments($req,$res){
        $result = SOSSData::Query ("products_comments", urlencode("itemid:".($_GET["id"]==null?0:$_GET["id"]).""));
        if($result->success){
            return $result->result;
        }else{
            $res->SetError ($result);
            return;
        }
    }

    public function postFavorite($req,$res){
        $p=$req->Body(true);
        $result = SOSSData::Query ("products", urlencode("itemid:".($p->itemid==null?0:$p->itemid).""));
        if($result->success){
            if(count($result->result)!=0){
               
                //$r = SOSSData::Query ("products_favorites", urlencode("itemid:".$p->itemid.",pid:".$p->pid));
                //if(count($p->result)!=0){
                if($p->liked){
                    SOSSData::Insert("products_favorites",$p);
                }else{
                    SOSSData::Delete("products_favorites",$p);
                }
                $result = SOSSData::Update("products", $result->result[0]);
                return $p;
            }else{
                $res->SetError ("invalied Favorite.");
                return;
            }
        }else{
            $res->SetError ($result);
            return;
        }
    }

    

    public function postSave($req,$res){
        $profile=$req->Body(true);
        $user= Auth::Autendicate("profile","postSave",$res);
        if($user->email!=$profile->email){
            $res->SetError ("You do not have permission to update this profile.");
            return;
        }
        //return $profile;
        if(!isset($profile->email)){
            //http_response_code(500);
            $res->SetError ("provide email");
            return;
        }
        if(!isset($profile->contactno)){
            //http_response_code(500);
            $res->SetError ("provide contact no");
            return;
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

    public function postSaveProduct($req,$res){
        
        $product=$req->Body(true);
        //return $product;
        //$user= Auth::Autendicate("product","save",$res);
        $summery =new stdClass();
        $summery->summery=substr($product->caption,0,500);
        $summery->title=$product->name;
        
        //if(isset())
        $summery->imgname=isset($product->imgurl)? $product->imgurl : '';
        //echo "im in"
        if(!isset($product->itemid)){
            $result=SOSSData::Insert ("products", $product,$tenantId = null);
            //return $result;
            //var_dump($result);
            if($result->success){
                $product->itemid = $result->result->generatedId;
                //$summery->id=$result->result->generatedId;
                //$product=$this->saveAttributes($product);
                
            }else{
                $res->SetError ("Error Saving.");
                return $res;
            }
        }else{
            $result=SOSSData::Update ("products", $product,$tenantId = null);
            $summery->id=$product->itemid;
            if($result->success){
                //$product=$this->saveAttributes($product);
                
            }else{
                $res->SetError ("Error Saving.");
                //exit();
                return $res;
            }
        }
        CacheData::clearObjects("products");
        CacheData::clearObjects("d_all_summery");
        CacheData::clearObjects("products_attributes");
        if(count($product->RemoveImages)>0){
            $product->removedStatus=SOSSData::Delete("products_image",$product->RemoveImages);
        }

        foreach($product->Images as $key=>$value){
            $product->Images[$key]->articalid=$product->itemid;
            if($product->Images[$key]->id==0){
                $result2=SOSSData::Insert ("products_image", $product->Images[$key],$tenantId = null);
                if($result2->success){
                    $product->Images[$key]->id = $result2->result->generatedId;
                }

            }else{
                $result2=SOSSData::Update ("products_image", $product->Images[$key],$tenantId = null);
            }
            
            //var_dump($invoice->InvoiceItems[$key]->invoiceNo);
        }
        CacheData::clearObjects("products_image");
        return $product;
        
    }

    public function getAllProducts($req,$res){
        if (isset($_GET["page"]) && isset($_GET["size"])){
            require_once (PLUGIN_PATH . "/sossdata/SOSSData.php");
            $mainObj = new stdClass();
            $mainObj->parameters = new stdClass();
            $mainObj->parameters->page = $_GET["page"];
            $mainObj->parameters->size = $_GET["size"];
            $mainObj->parameters->search = isset($_GET["q"]) ?  $_GET["q"] : "";
            $mainObj->parameters->pid = $_GET["pid"];

            $resultObj = SOSSData::ExecuteRaw("products_stelup_1", $mainObj);
            if($resultObj->success){
                if($mainObj->parameters->page==0 && $mainObj->parameters->search==""){
                    if(count($resultObj->result)==0){
                        $m = new stdClass();
                        $m->itemid=0;
                        $m->pid=0;
                        SOSSData::Insert("products_likes",$m);
                        SOSSData::Insert("products_favorites",$m);
                    }
                }
                return $resultObj->result;
            }else{
                $res->SetError ($resultObj);
                return $resultObj;
            }
        } else {
            
            $mainObj = new stdClass();
            $mainObj->error="Invalied Query";
            return $mainObj;
        }
    }

    


}

?>