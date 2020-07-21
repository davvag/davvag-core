jQuery(window).load(function(){
    
    "use strict";
  
    var container = document.querySelector('#bloglist');
    var msnry = new Masonry( container, {
      // options
      columnWidth: '.col-xs-6',
      itemSelector: '.col-xs-6'
    });
    
    // check on load
    if(jQuery(window).width() <= 480 )
        msnry.destroy();

    // check on resize
    jQuery(window).resize(function(){
        if(jQuery(this).width() <= 480 )
            msnry.destroy();
    });
    
    // relayout items when clicking chat icon
    jQuery('#chatview, .menutoggle').click(function(){
       msnry.layout();
    });

    

  });

 