<?php
if(isset($_COOKIE["Location"]))
{
    require_once (dirname(__FILE__) . "/launcher.html");
}else{
    require_once (dirname(__FILE__) . "/selectmap.php");
}
?>