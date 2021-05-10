<?php

class UploaderService {

    private function getPostBody() {
        $rawInput = fopen('php://input', 'r');
        $tempStream = fopen('php://temp', 'r+');
        stream_copy_to_stream($rawInput, $tempStream);
        rewind($tempStream);
        return stream_get_contents($tempStream);
    }

    private function compress($source, $destination, $quality) {

        $info = mime_content_type($source);
        file_put_contents($destination, $source);
        //imagejpeg($source, $destination, $quality);
        /*
        switch($info){
            case "image/jpeg":
                $image = imagecreatefromgif($source);
                imagejpeg($image, $destination, $quality);
                
            break;
            case "image/gif":
                $image = imagecreatefromgif($source);
                imagejpeg($image, $destination, $quality);
            break;
            case "image/png":
                $image = imagecreatefrompng($source);
                imagejpeg($image, $destination, $quality);
            break;
            default:
                file_put_contents($destination, $source);
            break;
        }*/
      
    }

    function compressImage($source, $destination, $quality) {

        $info = getimagesize($source);
      
        if ($info['mime'] == 'image/jpeg') 
          $image = imagecreatefromjpeg($source);
      
        elseif ($info['mime'] == 'image/gif') 
          $image = imagecreatefromgif($source);
      
        elseif ($info['mime'] == 'image/png') 
          $image = imagecreatefrompng($source);
      
        imagejpeg($image, $destination, $quality);
      
    }

    public function __handle($req, $res){
        Carbite::Reset();
        Carbite::SetAttribute("reqUri",$req->Params()->handlerName .$req->Params()->route);
        Carbite::SetAttribute("no404",true);

        Carbite::GET("/test",function($req,$res){
            $res->Set("Hello World");
        });

        Carbite::GET("/get/@ns/@name",function($req,$res){
            $ns = $req->Params()->ns;
            $name = $req->Params()->name;
            $folder = MEDIA_FOLDER . "/".  DATASTORE_DOMAIN . "/$ns";
            //echo "im here";
            //echo "$folder/$name";
            if(!file_exists("$folder/$name")){
                $name="0";
                //echo "im here in no file";
                //return 0;
            }
            if(file_exists("$folder/$name")){
                $type=mime_content_type("$folder/$name");
                header("Content-Type: $type");
                echo file_get_contents("$folder/$name");
                exit();
            }else{
                return "Error Procesing";
            }
            
        });

        Carbite::POST("/upload/@ns/@name",function($req,$res){
            $ns = $req->Params()->ns;
            $name = $req->Params()->name;
            $folder = MEDIA_FOLDER . "/".  DATASTORE_DOMAIN . "/$ns";
            
            if (!file_exists($folder))
                mkdir($folder, 0777, true);
            
            $this->compress($this->getPostBody(),"$folder/$name",60);
            //file_put_contents("$folder/$name", $this->getPostBody());
            //$this->compressImage($_FILES['imagefile']['tmp_name'],"$folder/$name",60);
            $resObj = new stdClass();
            $resObj->sucess = true;
            $resObj->message = "Successfully Uploaded!!!";
            $res->Set($resObj);
        });

        $resObj = Carbite::Start();
        exit();
    }
}

?>