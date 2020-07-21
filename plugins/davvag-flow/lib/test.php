<?php
require_once(PLUGIN_PATH."/davvag-flow/lib/vendor/autoload.php");
class test{
    //use A2Design\AIML;
    public function gohome($s,$d){
        return $s.$d;
    }

    public function sendfail(){
        throw new Exception('fail function');
    }

    public function fail($name){
        return $name;
    }

    public function getMessage($s){
        /*$aimlFilePath = PLUGIN_PATH.'/davvag-flow/lib/aiml-en-us-foundation-alice/alice.aiml';
        $images = preg_grep('~\.(aiml)$~', scandir(PLUGIN_PATH.'/davvag-flow/lib/aiml-en-us-foundation-alice/'));
        $chat = new A2Design\AIML\AIML();
        //var_dump($images);
        foreach ($images as $key => $value) {
            # code...
            $chat->addDict(PLUGIN_PATH.'/davvag-flow/lib/aiml-en-us-foundation-alice/'.$value);
        }*/
        

        return $s;
    }

    
}
?>