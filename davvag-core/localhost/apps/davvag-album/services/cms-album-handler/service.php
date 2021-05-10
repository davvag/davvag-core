<?php
require_once(PLUGIN_PATH . "/sossdata/SOSSData.php");
require_once(PLUGIN_PATH . "/phpcache/cache.php");
require_once(PLUGIN_PATH . "/auth/auth.php");
class ArticalService{
    
    public function postDeleteAlbum($req,$res){
        $Artical=$req->Body(true);
        if(isset($Artical->id)){
            $result=SOSSData::Query("d_cms_album_imagev1","articalid:".$Artical->id);
            if($result->success){
                SOSSData::Delete("d_cms_album_imagev1",$result->result);
                $result = SOSSData::Delete("d_cms_album_v1",$Artical);
                CacheData::clearObjects("d_cms_album_v1");
                //CacheData::clearObjects("d_all_summery");
                CacheData::clearObjects("d_cms_album_v1_pod_bycat_paging");
                CacheData::clearObjects("d_cms_album_v1_pod_paging");
                return $result;
            }else{
                $res->SetError ("Error Deleting.");
                return $result;
            }
            

        }else{
            $res->SetError ("Error Deleting.");
            return $Artical;
        }
    }

    public function postSaveAlbum($req,$res){
        
        $Artical=$req->Body(true);
        $user= Auth::Autendicate("profile","postInvoiceSave",$res);
        
        if(!isset($Artical->id)){
            $result=SOSSData::Insert ("d_cms_album_v1", $Artical,$tenantId = null);
            //return $result;
            //var_dump($result);
            if($result->success){
                $Artical->id = $result->result->generatedId; 
                //return $Artical;
            }else{
                $res->SetError ($result);
                //exit();
                return $res;
            }
        }else{
            $result=SOSSData::Update ("d_cms_album_v1", $Artical,$tenantId = null);
        }
        CacheData::clearObjects("d_cms_album_v1");
        CacheData::clearObjects("d_all_summery");
        CacheData::clearObjects("d_cms_album_v1_pod_bycat_paging");
        CacheData::clearObjects("d_cms_album_v1_pod_paging");
        if(count($Artical->RemovedImages)>0){
            $Artical->removedStatus=SOSSData::Delete("d_cms_album_imagev1",$Artical->RemovedImages);
        }
        foreach($Artical->Images as $key=>$value){
            $Artical->Images[$key]->articalid=$Artical->id;
            if($Artical->Images[$key]->id==0){
                $result2=SOSSData::Insert ("d_cms_album_imagev1", $Artical->Images[$key],$tenantId = null);
                if($result2->success){
                    $Artical->Images[$key]->id = $result2->result->generatedId;
                }

            }else{
                $result2=SOSSData::Update ("d_cms_album_imagev1", $Artical->Images[$key],$tenantId = null);
            }
            
            //var_dump($invoice->InvoiceItems[$key]->invoiceNo);
        }
        CacheData::clearObjects("d_cms_album_imagev1");
        return $Artical;
        
    }

    

    function getAlbum($req){
        //echo "imain";
        $data =null;
        if(isset($_GET["q"])){
            //echo "in here";
            $result= CacheData::getObjects_fullcache(md5("id:".$_GET["q"]),"d_cms_album_v1");
            if(!isset($result)){
                //echo "in here";
                $result = SOSSData::Query("d_cms_album_v1",urlencode("id:".$_GET["q"]));
                //return $result;
                if($result->success){
                    //$f->{$s->storename}=$result->result;
                    if(isset($result->result[0])){
                        $data= $result->result[0];
                        CacheData::setObjects(md5("id:".$_GET["q"]),"d_cms_album_v1",$result->result);
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
                    <meta name="tags" content="">
                    <meta name="og:title" content="'.urldecode($data->title).'">
                    <meta name="og:description" content="'.urldecode($data->summery).'">
                    <meta name="og:tags"  content="">
                    <meta name="og:image"  content="http://'.$_SERVER["HTTP_HOST"].'/components/dock/soss-uploader/service/get/d_cms_album/'.$_GET["q"]."-".$data->imgname.'">
                    <title>'.urldecode($data->title).'</title>
                    
                </head>
                <body>
                    loading.....
                    <script type="text/javascript">
                        setTimeout(function(){ window.location = "/#/app/davvag-album/a?id='.$_GET["q"].'"; }, 1000);
                        
                    </script>    
                </body>
                </html>';
                exit();      

            }
        }
    }

    function postsaveSettings($req,$res){
        $path = MEDIA_FOLDER."/".DATASTORE_DOMAIN."/global-setting/";
        $saveObj=$req->Body(true);
        if (!file_exists($path))
              mkdir($path, 0777, true);
        
        $filename=$saveObj->name.".glb";
        $string=json_encode($saveObj->body);
        $path=$path."/".$filename;
        $f = fopen($path, 'w');
        fwrite ($f, $string, strlen($string));
        fclose($f);
    }

    function postSettings($req){
        $path = MEDIA_FOLDER."/".DATASTORE_DOMAIN."/global-setting/";
        $saveObj=$req->Body(true);
        if (!file_exists($path))
              mkdir($path, 0777, true);
        
        $filename=$saveObj->name;
        $path=$path."/".$filename.".glb";
        if (file_exists($path)){
                $f = fopen($path, 'r');
                $buffer = '';
                while(!feof($f)) {
                    $buffer .= fread($f, 2048);
                }
                fclose($f);
                return json_decode($buffer);
            
            }else{
            return null;
        }    
    }

}
?>