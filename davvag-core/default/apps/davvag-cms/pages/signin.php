<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="">
  <link rel="icon" href="assets/dock/images/favicon.ico" type="image/png">

  <title>Login to DAVVAG Dock</title>

  <link href="assets/dock/css/style.default.css" rel="stylesheet">

  <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!--[if lt IE 9]>
  <script src="js/html5shiv.js"></script>
  <script src="js/respond.min.js"></script>
  <![endif]-->
</head>

<body class="signin">


<section>
  
    <div class="signinpanel">
        
        <div class="row">
            
            <div class="col-md-7">
                
                <div class="signin-info">
                    <div class="logopanel">
                        <h1><span>[</span> DAVVAG <span>]</span></h1>
                    </div><!-- logopanel -->
                
                    <div class="mb20"></div>
                
                    <h5><strong>New Features in DAVVAG</strong></h5>
                    <ul>
                        <li><i class="fa fa-arrow-circle-o-right mr5"></i> Access your applications </li>
                        <li><i class="fa fa-arrow-circle-o-right mr5"></i> Create Profiles </li>
                        <li><i class="fa fa-arrow-circle-o-right mr5"></i> Create Products and Services</li>
                        <li><i class="fa fa-arrow-circle-o-right mr5"></i> Invoice & Collect payments</li>
                        <li><i class="fa fa-arrow-circle-o-right mr5"></i> View statistics </li>
                    </ul>
                    <div class="mb20"></div>
                    <!-- <strong>Not a member? <a href="signup.html">Sign Up</a></strong> -->
                </div><!-- signin0-info -->
            
            </div><!-- col-sm-7 -->
            
            <div class="col-md-5">
                
                <form method="post" action="">
                    <h4 class="nomargin">Sign In</h4>
                    
                    <?php if (isset($_GET["success"])) if ($_GET["success"] === "false") {?>
                        <p class="mt5 mb20">Invalid username/password</p>
                    <?php }?>

                    <p class="mt5 mb20">Login to access your applications.</p>
                
                    <input type="text" class="form-control uname" placeholder="Email" name="username" />
                    <input type="password" class="form-control pword" placeholder="Password" name="password"/>
                    <!-- <a href=""><small>Forgot Your Password?</small></a> -->
                    <button class="btn btn-success btn-block">Sign In</button>
                    
                </form>
            </div><!-- col-sm-5 -->
            
        </div><!-- row -->
        
        <div class="signup-footer">

        </div>
        
    </div><!-- signin -->
  
</section>

</body>
</html>
