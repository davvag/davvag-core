<?php
    require_once(PLUGIN_PATH . "/phpcache/cache.php");
    require_once(PLUGIN_PATH . "/auth/auth.php");
    $domain = Auth::GetDomainAttributes();
    
    if(isset($domain->name)){
        define("DOMAIN",$domain->domain);
        define("DOMAINNAME",$domain->name);

    }else{
        define("DOMAIN","NotReg-405");
        define("DOMAINNAME","This Domain is not registered please contact davvag.com for information");
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="">
  <link rel="icon" href="assets/davvag-cms/images/favicon.png" type="image/png">
  <title><?php echo DOMAINNAME; ?></title>
  <link href="assets/davvag-cms/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/davvag-cms/css/dockanimation.css" rel="stylesheet">
  <link href="assets/davvag-cms/css/custom.css" rel="stylesheet">
  <link href="assets/davvag-cms/css/style.loaders.css" rel="stylesheet">
 
  <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha256-3dkvEK0WLHRJ7/Csr0BZjAWxERc5WH7bdeUya2aXxdU= sha512-+L4yy6FRcDGbXJ9mPG8MT/3UCDzwR9gPeyFNMCtInsol++5m3bk2bXWKdZjvybmohrAsn3Ua5x8gfLnbE1YkOg==" crossorigin="anonymous">
</head>

<body>
<div id="preloader">
    <div id="status"><i class="fa fa-spinner fa-spin"></i></div>
</div>

<div class="headerbar" webdock-component="headerbar" id="id-headerbar">
    </div>
<div id="idLeftPanel" class="leftpanel" webdock-component="left-menu" style="display:none">

    </div><!-- leftpanel -->
<div  webdock-component="soss-routes" class="id-soss-routes">
    </div><!-- contentpanel -->

<div  webdock-component="footer-bar" id="id-footer-bar">
    </div>
<script src="assets/davvag-cms/js/bootstrap.min.js"></script>
<script src="assets/davvag-cms/js/moments.js"></script>
<script src="assets/davvag-cms/js/masory.min.js"></script>
<script src="lib/jquery.js"></script>
<script src="lib/webdock.js" webdockapp="davvag-cms"></script>


<script type="text/javascript">
    
    
    
    WEBDOCK.onReady (function(){
        jQuery('#preloader').delay(350).fadeOut(function(){
            jQuery('body').delay(350).css({'overflow':'visible'});
        });

        jQuery('.menutoggle').click(function(){
        
        var body = jQuery('body');
        var bodypos = body.css('position');
        
        if(bodypos != 'relative') {
            
            if(!body.hasClass('leftpanel-collapsed')) {
                body.addClass('leftpanel-collapsed');
                jQuery('.nav-bracket ul').attr('style','');
                
                jQuery(this).addClass('menu-collapsed');
                
            } else {
                body.removeClass('leftpanel-collapsed chat-view');
                jQuery('.nav-bracket li.active ul').css({display: 'block'});
                
                jQuery(this).removeClass('menu-collapsed');
                
            }
        } else {
            
            if(body.hasClass('leftpanel-show'))
                body.removeClass('leftpanel-show');
            else
                body.addClass('leftpanel-show');
            
            adjustmainpanelheight();         
        }
    
        });
    });  
</script>

</body>
</html>
