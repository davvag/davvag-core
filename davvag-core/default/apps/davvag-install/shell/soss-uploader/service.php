<?php

class UploaderService {

    private function getPostBody() {
        $rawInput = fopen('php://input', 'r');
        $tempStream = fopen('php://temp', 'r+');
        stream_copy_to_stream($rawInput, $tempStream);
        rewind($tempStream);
        return stream_get_contents($tempStream);
    }


    public function __handle($req, $res){
        Carbite::Reset();
        Carbite::SetAttribute("reqUri",$req->Params()->handlerName .$req->Params()->route);
        Carbite::SetAttribute("no404",true);

        Carbite::GET("/test",function($req,$res){
            $res->Set("Hello World");
        });

        Carbite::GET("/get/@ns/@name",function($req,$res){
            header("Cache-Control: private, max-age=10800, pre-check=10800");
            header("Pragma: private");
            header("Expires: " . date(DATE_RFC822,strtotime("+2 day")));
            $ns = $req->Params()->ns;
            $name = $req->Params()->name;
            $folder = MEDIA_FOLDER . "/".  $_SERVER["HTTP_HOST"] . "/$ns";
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
            $folder = MEDIA_FOLDER . "/".  $_SERVER["HTTP_HOST"] . "/$ns";
            
            if (!file_exists($folder))
                mkdir($folder, 0777, true);
            
            file_put_contents("$folder/$name", $this->getPostBody());
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