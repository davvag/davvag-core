<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="">
  <link rel="icon" href="assets/dock/images/favicon.ico" type="image/png">
      
  <title>DAVAAG Application Dock</title>

  <link href="assets/dock/css/style.default.css" rel="stylesheet">
  <link href="assets/dock/css/dockanimation.css" rel="stylesheet">
</head>

<body>
<!-- Preloader -->
<div id="preloader">
    <div id="status"><i class="fa fa-spinner fa-spin"></i></div>
</div>

<section>
    
  <div class="mainpanel">
    <div id="idLeftPanel" class="leftpanel" webdock-component="left-menu">
        </div><!-- leftpanel -->
    <div class="headerbar" webdock-component="headerbar" id="id-headerbar">
    </div><!-- headerbar -->
    
    <div class="pageheader" webdock-component="navigation-title" id="id-navigation-title">
    </div>
    <div class="contentpanel" webdock-component="soss-routes">
    </div><!-- contentpanel -->
    
  </div><!-- mainpanel -->
</section>

<script src="lib/jquery.js"></script>
<script src="lib/webdock.js" webdockapp="dock"></script>
<script type="text/javascript" src="//maps.googleapis.com/maps/api/js?key=<?php echo MAPS_APIKEY; ?>"></script>
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
