<?php
require_once(PLUGIN_PATH . "/sossdata/SOSSData.php");
require_once(PLUGIN_PATH . "/phpcache/cache.php");
require_once(PLUGIN_PATH . "/auth/auth.php");
class BroadcastService {

    function __construct(){
        
    } 

    public function getallBroadcast($req,$res){
        $allkeys=CacheData::getObjects("all","fb_broadcast");
        if(isset($allkeys)){
            return $allkeys;
        }else{
            $r = SOSSData::Query ("fb_broadcast", null);
            if($r->success){
                return $r->result;
            }else{
                $res->SetError ("Error Loading data");
                return $r->result; 
            }
        }
    }

    public function getSendMessage($req,$res){
        $user= Auth::Autendicate("broadcaster-handler","getSendMessage",$res);

        if(isset($_GET["id"]) && isset($_GET["page"])){
            $id=$_GET["id"];
            $brodcast =$this->BrodcastbyID($id);
            
            require_once(PLUGIN_PATH . "/davvag-flow/lib/facebook.php");
            if(isset($brodcast)){
                $content= json_decode($brodcast->content);
                $r = SOSSData::Query ("fb_profiles", null);
                //return $content;
                if($r->success){
                    $rep=array();
                    $fb=new facebook();
                    foreach($r->result as $user){
                        foreach ($content as  $value) {
                            # code...
                            //return $value;
                            switch($value->template_type){
                                case "button":
                                    unset($value->id);
                                    $m=array("attachment"=>array("type"=>"template","payload"=>$value));
                                    $reponce =$fb->sendMessage("RESPONSE",$user,$m);
                                    //return $reponce;
                                    array_push($rep,$reponce);
                                break;
                                case "generic":
                                    unset($value->id);
                                    if(isset($value->buttons))
                                    unset($value->buttons);
                                    
                                    for ($i=0; $i < count($value->elements)  ; $i++) { 
                                        # code...
                                        unset($value->elements[$i]->id);
                                        if(isset($value->elements[$i]->image_url)){
                                            $value->elements[$i]->image_url="http://".FBCAPP_DOMAIN.$value->elements[$i]->image_url;
                                        }
                                    }
                                    //return $value;
                                    $m=array("attachment"=>array("type"=>"template","payload"=>$value));
                                    $reponce =$fb->sendMessage("RESPONSE",$user,$m);
                                    array_push($rep,$reponce);
                                    //return $reponce;
                                break;
                            }
                            
                            
                        }
                        
                    }

                    return $rep;
                }
            }else{
                 $res->SetError ("No Broadcast to sends");
            }

        }else{
            $res->SetError ("Invalied Call");
            //return $keyword;
        }
    }

    public function postSaveBroadcast($req,$res){
        $keyword=$req->Body(true);
        $keyword->id=isset($keyword->id)?$keyword->id:0;
        //$arr=explode(",",$keyword->keywords);
        $r = SOSSData::Query ("fb_broadcast", "id:$keyword->id");
        $current_time = new DateTime();
        $keyword->createdate=date_format($current_time, 'm-d-Y H:i:s');
        //return $arr;
        //return $this->validate($arr,$keyword);
        //if(isset($keyword->))
        
        if(count($r->result)==0){
           
            $keyword->status="new";
            $result=SOSSData::Insert ("fb_broadcast", $keyword);
            if($result->success){
                $keyword->id=$result->result->generatedId;
                return $keyword;
            }
        }else{
                
                $result=SOSSData::Update ("fb_broadcast", $keyword);
                if($result->success){
                   // echo $keyword->status;
                    if($keyword->status==="schedule" || $keyword->status==="sendnow"){
                        $r = SOSSData::Query ("schedule_pending", "recid:$keyword->id");
                        if(count($r->result)==0){
                            //echo "im in";
                            $schedule=array("id"=>"0","recid"=>$keyword->id,"createdate"=>date_format($current_time, 'm-d-Y H:i:s'),
                            "scheduled_date"=>isset($keyword->scheduled_date)?$keyword->scheduled_date:null,"app"=>"facebook-messanger-v1","service"=>"broadcaster-handler","method"=>"SendMessage",
                        "postMethod"=>"GET","body"=>"{\"id\":$keyword->id,\"page\":0}","status"=>$keyword->status);
                            $rs=SOSSData::Insert ("schedule_pending", $schedule);
                            //return $rs;
                            if($rs->success){
                                //$schedule->id=$rs->result->generatedId;
                                $keyword->schduleTicket=array("success"=>true,"result"=>$schedule);
                            }
                        }else{
                            $keyword->schduleTicket=array("success"=>false,"message"=>"Already pending schedule is there","result"=>$r->result);
                        }
                    }
                    return $keyword;
                }else{
                    $res->SetError ($result->result);
                    return $keyword;
                }


        }
        $res->SetError ("Not Processed the request");
        //return $keyword;
    }
    private function BrodcastbyID($id){
        $r = SOSSData::Query ("fb_broadcast", "id:".$_GET["id"]);
        if($r->success){
            return count($r->result)==0?null:$r->result[0];
        }else{
            throw new Exception('Error Processing data');
        }
    }
    public function getBroadcastByID($req,$res){
        if(isset($_GET["id"])){
            $r = SOSSData::Query ("fb_broadcast", "id:".$_GET["id"]);
            if($r->success){
                return count($r->result)==0?[]:$r->result[0];
            }else{
                $res->SetError ($r->result);
            }
        }else{
            $res->SetError ("Error Loading data");
            exit();
        }
    }

   

   
    
}

?>