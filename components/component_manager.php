<?php
        class ComponentManager {

            function __construct(){

            }

            private function getComponentDescriptor($req, $res, $asObject = true, $includeLocation = false){
                //echo TENANT_RESOURCE_LOCATION;
                $s=checkAccess($res,$req->Params()->appCode);
                //echo $req->Params()->appCode;
                if(!$s){
                    //echo "heree";
                    writeResponse($res, false, array("message"=>"UnAutherized call. this user group do not have permision ".GROUPID));
                    return null;
                }
                if(!BYPASS){
                    $appacess=$GLOBALS["appAccess"]->{$req->Params()->appCode};
                }else{
                    $appacess=null;
                }
                //echo json_encode($appacess);
                $componentName = $req->Params()->componentName;
                $appFile = TENANT_RESOURCE_LOCATION . "/apps/{$req->Params()->appCode}/app.json";
                $outObj;$success=false;

                if (file_exists($appFile)){
                    $appObj = json_decode(file_get_contents($appFile));
                    
                    if (isset($appObj)){
                        if (isset($appObj->components)){
                        if (isset($appObj->components->$componentName)){
                            $componentType = $appObj->components->$componentName->location;
                            $componentDescriptor = TENANT_RESOURCE_LOCATION . "/apps/{$req->Params()->appCode}/$componentType/$componentName/component.json";

                            if (file_exists($componentDescriptor)){
                                if ($asObject){
                                    $componentObj = json_decode(file_get_contents($componentDescriptor));
                                    if ($includeLocation)
                                    $componentObj->location = TENANT_RESOURCE_LOCATION . "/apps/{$req->Params()->appCode}/$componentType/$componentName";
                                }
                                else    
                                    $componentObj = file_get_contents($componentDescriptor);

                                return $componentObj;
                            }else {
                                $outObj =  Resources::$COMPONENT_DESCRIPTOR_NOT_FOUND . " $componentDescriptor";
                            }
                            
                        }else {
                            $outObj = Resources::$APP_DESCRIPTOR_COMPONENT_NOT_FOUND;  
                        }
                        }else{
                            $outObj = Resources::$APP_DESCRIPTOR_COMPONENT_NOT_FOUND;
                        }
                    }else {
                        $outObj = Resources::$APP_DESCRIPTOR_INVALID_JSON;
                    }
                
                }else {
                    $outObj = Resources::$APP_DESCRIPTOR_NOT_FOUND;
                }

                writeResponse($res, $success, $outObj);
            }

            public function HandleFile($req,$res){
                $this->getFileInComponent($req, $res);
            }

            public function HandleTransformer($req,$res){
                require_once (__DIR__ . "/carbitetransform.php");

                $descObj = $this->getComponentDescriptor($req,$res, true, true);
                if ($descObj){
                    $outObj;$success = false;
                    if (isset($descObj->transformers)){
                        Carbite::Reset();
                        Carbite::SetAttribute("reqUri",$req->Params()->route);
                        Carbite::SetAttribute("no404",true);
                        
                        foreach ($descObj->transformers as $tk => $ts) {
                            
                            if($ts->destUrl=="SOSSData"){
                                $r=explode("/@",$ts->route);
                                $route=count($r)>0?$r[0]:$req->Params()->route;
                                if(substr($req->Params()->route,0,strlen($route))==substr($ts->route,0,strlen($route))){
                                    $rawInput = fopen('php://input', 'r');
                                    $tempStream = fopen('php://temp', 'r+');
                                    stream_copy_to_stream($rawInput, $tempStream);
                                    rewind($tempStream);
                                    $data= json_decode(stream_get_contents($tempStream));
                                    switch($ts->destMethod){
                                        case "insert":
                                            $outObj=SOSSData::Insert($ts->namespace,$data);
                                            break;
                                        case "delete":
                                            $outObj =SOSSData::Delete($ts->namespace,$data);
                                            break;
                                        case "update":
                                            $outObj =SOSSData::Update($ts->namespace,$data);
                                            break;
                                        case "query":
                                            $query=isset($_GET["query"])?$_GET["query"]:"";
                                            $lastId=isset($_GET["lastID"])?$_GET["lastID"]:null;
                                            $sorting=isset($_GET["sorting"])?$_GET["sorting"]:"asc";
                                            $pagesize=isset($_GET["pageSize"])?$_GET["pageSize"]:20;
                                            $frompage=isset($_GET["fromPage"])?$_GET["fromPage"]:0;
                                            //$lastId=isset($_GET["lastID"])?$_GET["lastID"]:null;
                                            if(count($r)>1){
                                                $query=$ts->query;
                                                $rsr=explode("/",substr($req->Params()->route,strlen($route),strlen($req->Params()->route)-strlen($route)));
                                                for ($i=1; $i <count($r) ; $i++) { 
                                                    # code...
                                                    $query=str_replace("@".$r[$i],$rsr[$i],$query);
                                                    
                                                }
                                                //$query=rtrim($query,",");
                                            }
                                            $outObj =SOSSData::Query($ts->namespace,$query,$lastId,$sorting,$pagesize,$frompage);
                                            break;
                                    }
                                }

                            }else{
                                CarbiteTransform::RESTROUTE($ts->method,$ts->route, 
                                $ts->destMethod, 
                                $ts->destUrl,
                                (isset($ts->bodyTemplate) ? new PostBodyTemplate($ts->bodyTemplate): null), 
                                (isset($ts->destHeaders) ? $ts->destHeaders :null),
                                null);
                                $resObj = Carbite::Start();
                            }
                            
                        }

                        if (isset($outObj)){
                            writeResponse($res, $outObj->success, $outObj->result);
                            return;
                        }

                        if (!isset($resObj))
                            $outObj == Resources::$COMPONENT_TRANSFORMER_UNKNOWN;
                        } else
                            $outObj = Resources::$COMPONENT_TRANSFORMER_NOT_FOUND;
                    
                    if (isset($outObj))
                        writeResponse($res, $success, $outObj);
                }
            }

            public function HandleService($req,$res){
                //echo json_encode($req->Params());
                $s=checkAccess($res,$req->Params()->appCode,"service",$req->Params()->componentName,$req->Params()->handlerName);
                if(!$s){
                    writeResponse($res, false, array("message"=>"UnAutherized call. this user group do not have permision ".GROUPID));
                    return null;
                }
                $descObj = $this->getComponentDescriptor($req,$res, true, true);
                if ($descObj){
                    $outObj;$success = false;
                    if(isset($descObj->dependency)){
                        foreach (isset($descObj->dependency->plugins)?$descObj->dependency->plugins:[] as $key => $value) {
                            # code...
                            if($value->type=="php"){
                                switch(isset($value->plugin_location)?$value->plugin_location:"other"){
                                    case "global":
                                        if(file_exists(PLUGIN_PATH . $value->location)){
                                            require_once(PLUGIN_PATH . $value->location);
                                        }else{
                                            writeResponse($res, false, array("message"=>"Error Loading Global Plugin ".PLUGIN_PATH . $value->location));
                                            return false;
                                        }
                                        break;
                                    case "local":
                                        if(file_exists(PLUGIN_PATH_LOCAL . $value->location)){
                                            require_once(PLUGIN_PATH_LOCAL . $value->location);
                                        }else{
                                            writeResponse($res, false, array("message"=>"Error Loading Local Plugin ".PLUGIN_PATH . $value->location));
                                            return false;
                                        }
                                        break;
                                    default:
                                        if(file_exists($value->location)){
                                            require_once($value->location);
                                        }else{
                                            writeResponse($res, false, array("message"=>"Error Loading Other Plugin ".PLUGIN_PATH . $value->location));
                                            return false;
                                        }
                                        break;
                                }
                            }
                        
                            
                        }
                    }
                    if (isset($descObj->serviceHandler)){
                            $handler = $descObj->serviceHandler;
                        if (isset($handler->file)){
                            if (isset($handler->class)){
                                $handlerFile = "$descObj->location/" .$handler->file;
                                if (file_exists($handlerFile)){
                                    require_once($handlerFile);
                                    $class = $handler->class;
                                    if (class_exists($class)){
                                        $obj = new $class(array());
                                        $handlerName = $req->Params()->handlerName;
                                        $methodName = strtolower($_SERVER["REQUEST_METHOD"]). ucwords($handlerName);
                                        if(!method_exists($obj, $methodName))
                                            $methodName = "__handle";

                                        if(method_exists($obj, $methodName)){
                                            $outObj = $obj->$methodName($req, $res);
                                            $errorObj = $res->GetError();
                                            if (isset($errorObj)){
                                                $existingCode = http_response_code();
                                                if ($existingCode == 200)
                                                    http_response_code(500);
                                                $outObj = $errorObj;
                                                $success = false;
                                            }else {
                                                $success = true;
                                            }
                                        }else 
                                            $outObj = Resources::$COMPONENT_SERVICE_HANDLER_METHOD_NOT_FOUND_PHP;
                                    }else 
                                        $outObj = Resources::$COMPONENT_SERVICE_HANDLER_CLASS_NOT_FOUND_PHP;
                                    
                                } else 
                                    $outObj = Resources::$COMPONENT_SERVICE_HANDLER_FILENOT_FOUND;  
                            }else 
                                $outObj = Resources::$COMPONENT_SERVICE_HANDLER_CLASSNOT_FOUND;
                        }else
                            $outObj = Resources::$COMPONENT_SERVICE_HANDLER_FILENOT_FOUND_DESCRIPTOR;                
                    }else 
                        $outObj = Resources::$COMPONENT_SERVICE_HANDLER_NOT_FOUND;


                    writeResponse($res, $success, $outObj);
                }
            }

            public function HandleComponent($req,$res){
                $isObjectMode = false;
                $methodName;

                $outObj;$success = false;

                if (isset($_GET)){
                    if (isset($_GET['object'])){
                        $methodName = "getObject" . ucwords($_GET['object']);
                    }
                }

                if (!isset($methodName))
                    $methodName = "handleComponentOperation";

                if (method_exists($this, $methodName)){
                    $outObj = $this->$methodName($req,$res);
                    $success = true;
                }else {
                    $outObj = Resources::$UNKNOWN_OPERATION;
                }

                if (isset($outObj)){
                    $this->setCacheHeaders();
                    writeResponse($res, $success, $outObj);
                }
            }
            
            private function getMimeType($fileName){
                $path_info = pathinfo($fileName);
                $ext = $path_info['extension'];
                
                switch (strtolower($ext)){
                    case "css":
                        return "text/css";
                }
            }

            private function setCacheHeaders(){
                /*
                $seconds = 60*60 *2;
                header("Cache-Control: private, max-age=$seconds");
                header("Expires: " .gmdate('D, d M Y H:i:s', time()+$seconds).'GMT');
                */
                header("Cache-Control: private, max-age=10800, pre-check=10800");
                header("Pragma: private");
                header("Expires: " . date(DATE_RFC822,strtotime("+2 day")));
            }

            private function getFileInComponent($req, $res){
                $descObj = $this->getComponentDescriptor($req,$res, true, true);
                $location = $descObj->location;
                $filePath = $req->Params()->filePath;
                $fileName = "$location/$filePath";


                $outObj;$success=false;
                if (file_exists($fileName)){
                    $mimeType = $this->getMimeType($fileName);
                    if ($mimeType)
                    header("Content-Type: $mimeType");
                    $outObj = file_get_contents($fileName);
                    $this->setCacheHeaders();
                    echo $outObj;
                    exit();
                }else {
                    $outObj = Resources::$COMPONENT_FILE_NOT_FOUND;
                }
                
                writeResponse($res, $success, $outObj);   
            }

            public function GetTenantDescriptor($req, $res, $asObject=false){

            }

            public function GetAppIcon($req, $res, $asObject=false){
                $iconLocation = TENANT_RESOURCE_LOCATION . "/{$req->Params()->appCode}/app.png";
                $outObj;$success=false;

                if (file_exists($iconLocation)){
                    header("Content-type: image/png");
                    echo(file_get_contents($iconLocation));
                    exit();
                }else {
                    $outObj = Resources::$APP_ICON_NOT_FOUND;
                }

                if ($asObject === false)
                    writeResponse($res, $success, $outObj);            
            }

            public function GetAllApps($req, $res, $asObject=false){
                
                $descriptorLocation = TENANT_RESOURCE_LOCATION . "/".GROUPID.".json" ;
                $outObj;$success=false;

                if (file_exists($descriptorLocation)){
                    $jsonFile = file_get_contents($descriptorLocation);
                    $descObj = json_decode($jsonFile);
                    $outObj = $descObj->apps;

                    $newApps = new stdClass();
                    foreach ($outObj as $appCode => $appData) {
                        $appLocation = TENANT_RESOURCE_LOCATION . "/apps/$appCode/app.json" ;
                        if (file_exists($appLocation)){
                            $jsonObj = json_decode(file_get_contents($appLocation));
                            if (isset($jsonObj)){

                                if (isset ($req->Query()->tags)){
                                    $tags = $req->Query()->tags;
                
                                    $hasTag = false;
                                    if (isset($jsonObj->tags)){
                                        foreach ($jsonObj->tags as $tag){
                                            if (strcmp($tags, $tag) === 0) {
                                                $hasTag = true;
                                                break;
                                            }
                                        }
                                    }
            
                                    if ($hasTag){
                                        $newApps->$appCode = $jsonObj->description;
                                        if (isset($jsonObj->configuration))
                                            $newApps->$appCode->config = $jsonObj->configuration;
                                    }
                                            
                
                                    $outObj = $newApps;
                                }else {
                                    $newApps->$appCode = $jsonObj->description;
                                }
                            }
                        }
                    }

                    $outObj = $newApps;
                    $success = true;
                }else {
                    $outObj = Resources::$TENANT_DESCRIPTOR_NOT_FOUND;
                }

                if ($asObject === false)
                    writeResponse($res, $success, $outObj);
            }

            public function GetAppDescriptor($req, $res, $asObject=false){
                //echo TENANT_RESOURCE_LOCATION;
                $appLocation = TENANT_RESOURCE_LOCATION . "/apps/{$req->Params()->appCode}/app.json" ;
                $outObj;$success=false;
                
                if (file_exists($appLocation)){
                    $jsonFile = file_get_contents($appLocation);
                    $_SESSION["appDescriptor"] = $jsonFile;
                    $outObj = json_decode($jsonFile);
                    $success = true;
                    $this->setCacheHeaders();
                }
                else {
                    $outObj = Resources::$APP_DESCRIPTOR_NOT_FOUND;
                }
                
                if ($asObject === false)
                    writeResponse($res, $success, $outObj);
            }

            private function handleComponentOperation($req,$res){
                $descObj = $this->getComponentDescriptor($req,$res, true, true);
                if (isset($descObj)){
                    if (isset($descObj->handler)){
                        
                    }else {
                        writeResponse($res, false, Resources::$COMPONENT_HANDLER_NOT_FOUND);
                    }
                } 
            }

            private function getObjectDesc($req,$res){
                return $this->getComponentDescriptor($req,$res);
            }

            private function getObjectResource($req,$res){
                $resourceType = isset($_GET["resource"]) ? $_GET["resource"] : '';
                
                switch ($resourceType){
                    case "attributes":
                        header ("Content-type: application/json");
                        $fileName = isset($_GET["file"]) ? $_GET["file"] : '';
                        if (isset($fileName)){
                            $schemaFile = SCHEMA_PATH . "/attributes/$fileName.json";
                            if (file_exists($schemaFile)){
                                $schemaData = file_get_contents($schemaFile);
                                $schemaObj = json_decode($schemaData);
                                writeResponse($res, true, $schemaObj);
                            }else {
                                writeResponse($res, false, "Attribute file doesn't exist $schemaFile");
                            }
                        }else {
                            writeResponse($res, false, "No attribute file specified $fileName");
                        }
                        break;
                    default:
                        writeResponse($res, false, "Unknown resource type");
                        break;
                }

            }
        }

?>