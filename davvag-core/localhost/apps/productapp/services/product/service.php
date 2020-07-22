<?php
require_once (PLUGIN_PATH . "/sossdata/SOSSData.php");
require_once (PLUGIN_PATH . "/phpcache/cache.php");
require_once (PLUGIN_PATH . "/auth/auth.php");
class ProductService {
    
    private function saveAttributes($product){
        if(isset($product->attributes)){
            $attributes = $product->attributes;
            //$attributes->itemid=$product->itemid;
            $r=null;
            if(isset($product->attributes->itemid))
                $r=SOSSData::Update ("products_attributes", $attributes,$tenantId = null);
            else{
                $attributes->itemid=$product->itemid;
                $r=SOSSData::Insert ("products_attributes", $attributes,$tenantId = null);
            }
            if($r->success){
                $product->attributes=$attributes;
            }else{
                $product->attributes=null;
            }
            return $product;

        }else{
            return $product;
        }
    }

    public function postSave($req,$res){
        
        $product=$req->Body(true);
        $user= Auth::Autendicate("product","save",$res);
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
                $summery->id=$result->result->generatedId;
                $product=$this->saveAttributes($product);
                //$summery->imgname=$result->result->generatedId;
                SOSSData::Insert ("d_all_summery", $summery,$tenantId = null);
                //return $product;
            }else{
                $res->SetError ("Error Saving.");
                //exit();
                return $res;
            }
        }else{
            $result=SOSSData::Update ("products", $product,$tenantId = null);
            $summery->id=$product->itemid;
            if($result->success){
                $product=$this->saveAttributes($product);
                SOSSData::Update ("d_all_summery", $summery,$tenantId = null);
            }else{
                $res->SetError ("Error Saving.");
                //exit();
                return $res;
            }
        }
        CacheData::clearObjects("products");
        CacheData::clearObjects("d_all_summery");
        CacheData::clearObjects("products_attributes");
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

    function getproductid($req){
        //echo "imain";
        $data =null;
        if(isset($_GET["q"])){
            $result= CacheData::getObjects_fullcache(md5("id:".$_GET["q"]),"d_all_summery");
            if(!isset($result)){
                $result = SOSSData::Query ("d_all_summery",urlencode("id:".$_GET["q"]));
                if($result->success){
                    //$f->{$s->storename}=$result->result;
                    if(isset($result->result[0])){
                        $data= $result->result[0];
                        CacheData::setObjects(md5("id:".$_GET["q"]),"d_all_summery",$result->result);
                    }
                }
            }else{
                $data= $result[0];
            }
            //$result = SOSSData::Query ("d_cms_artical_v1",urlencode("id:".$_GET["q"]));
            //var_dump($result);
            //echo "imain";
            if(isset($data)){
                
                
                echo '<!DOCTYPE html>
                <html>
                <head>
                    <meta charset="utf-8" />
                    <meta name="description" content="'.urldecode($data->summery).'">
                    <meta name="tags" content="'.urldecode($data->tags).'">
                    <meta name="og:title" content="'.urldecode($data->title).'">
                    <meta name="og:description" content="'.urldecode($data->summery).'">
                    <meta name="og:tags"  content="'.urldecode($data->tags).'">
                    <meta name="og:image"  content="http://'.$_SERVER["HTTP_HOST"].'/components/davvag-cms-davvag/soss-uploader/service/get/d_cms_artical/'.$_GET["q"]."-".$data->imgname.'">
                    <title>'.urldecode($data->title).'</title>
                    
                </head>
                <body>
                    loading.....
                    <script type="text/javascript">
                        setTimeout(function(){ window.location = "/#/app/davvag-cms-generalapps/a?id='.$_GET["q"].'"; }, 1000);
                        
                    </script>    
                </body>
                </html>';
                exit();      

            }
        }
    }
    
    public function getAllProducts($req){
        if (isset($_GET["page"]) && isset($_GET["size"])){
            require_once (PLUGIN_PATH . "/sossdata/SOSSData.php");
            $mainObj = new stdClass();
            $mainObj->parameters = new stdClass();
            $mainObj->parameters->page = $_GET["page"];
            $mainObj->parameters->size = $_GET["size"];
            $mainObj->parameters->search = isset($_GET["q"]) ?  $_GET["q"] : "";
            $resultObj = SOSSData::ExecuteRaw("nearproducts", $mainObj);
            return $resultObj->result;
        } else {
            
            $mainObj = new stdClass();
            $mainObj->error="Invalied Query";
            return $mainObj;
        }
    }

    public function postProductToStore($req){
        //MAIN_STORE_DOMAIN
        $product=$req->Body(true);
        $product->tname=HOST_NAME;
        $product->tenant=HOST_NAME;
        $product->itemid=null;
        $data =Auth::CrossDomainAPICall(MAIN_STORE_DOMAIN,"/components/raha/product-handler/service/ProductToStore","POST",$product);
        if($data->success){
            $Transaction=$data->result;
            $result = SOSSData::Query ("product_published", urlencode("itemid:".$Transaction->itemid.""));
            if(count($result->result)!=0){
                //$Transaction->itemid=$result->result[0]->itemid;
                $result=SOSSData::Update ("product_published", $Transaction,$tenantId = null);
                if($result->success){
                    return $Transaction;
                }else{
                    return null;
                }
            }else{
                $result = SOSSData::Insert ("product_published", $Transaction,$tenantId = null);
                if($result->success){
                    //$Transaction->itemid=$result->result->generatedId;
                    return $Transaction;
                }else{
                    return null;
                }
            }
            return $data->result;
        }else{
            return null;
        }

    }

    
}

?>