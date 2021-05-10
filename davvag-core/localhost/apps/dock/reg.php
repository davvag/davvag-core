<?php 
$nameError = $emailError = $passWordErrr = $fullnameError=$addressError=$cityError=$countryError=$error="";
$validate=true;
if(isset($_SESSION["regadmin"] )){
    $data=new stdClass();
    foreach ($_POST as $key => $value) {
        $data->{$key}=$value;
    }
    $data->nationalidcardnumber=$data->xxxxxxx;
    if (empty($_POST["email"])) {
        $emailError = "Email is required";
        $validate=false;
      } else {
        $email = $_POST["email"];
        // check if e-mail address is well-formed
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
          $emailError = "Invalid email format";
          $validate=false;
        }
      }

      if (empty($_POST["name"])) {
        $nameError = "Name is required";
        $validate=false;
      }
      if (empty($_POST["address"])) {
        $addressError = "Address is required";
        $validate=false;
      }
      if (empty($_POST["city"])) {
        $cityError = "City is required";
        $validate=false;
      }
      if (empty($_POST["country"])) {
        $countryError = "Country is required";
        $validate=false;
      }
      if (empty($_POST["userfullname"])) {
        $fullnameError = "Name is required";
        $validate=false;
      }
      if (empty($_POST["password"])) {
        $passWordErrr = "Name is required";
        $validate=false;
      }
      if(!$validate){
        $error="Validation Error";
        require_once (dirname(__FILE__) . "/pages/signup.php");
        exit();
      }

    if($_POST["requestid"]==$_SESSION["regadmin"]){
        if($data->password==$data->confirmpassword){
            
            $data->otherdata=new stdClass();
            $data->otherdata->usersname=$data->email;
            $data->otherdata->password=$data->password;
            //echo json_encode($data);
            $r=Auth::NewDomain($data);
            //var_dump($data);
            if($data->domain==$r->domain){
                header("Location: $redirectUrl");
            }else{
                $error="Error Registering.";
                require_once (dirname(__FILE__) . "/pages/signup.php");
                exit();
            }
            //header("Location: $redirectUrl");
        }else{
            $error="Password Dose not Match.";
            require_once (dirname(__FILE__) . "/pages/signup.php");
        }
    }else{
        echo "unautherized";
    }
}else{
    echo "unautherized";
}



?>