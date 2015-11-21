var slickr_flickr_slideshow_timer;
var slickr_flickr_slideshow_timer_on = false;

jQuery.noConflict();

function slickr_flickr_next_slide(obj) {
    var j = jQuery(obj);
    if (j.children('div').length == 1)  return ;
    options = j.parent().data("options");    
    if (('autoplay' in options) && (options['autoplay'] == false)) return;   
    var $active = j.children('div.active');
    if ( $active.length == 0 ) $active = j.children('div:last');
    var $next =  $active.next().length ? $active.next() : j.children('div:first');

    $active.addClass('last-active');
    $next.css({opacity: 0.0})
        .addClass('active')
        .animate({opacity: 1.0}, options['transition'], function() {
            $active.removeClass('active last-active');
        });
}

function slickr_flickr_next_slides() {
   jQuery('.slickr-flickr-slideshow').each(function(index){
   		s=jQuery(this);
        if (! s.hasClass('responsive')) slickr_flickr_next_slide(s) ;
   });
}

function slickr_flickr_set_slideshow_height(slideshow,imgheight,divheight) {
    var s = jQuery(slideshow);
    s.find('div img').css("max-height",imgheight+"px");
    s.css("height", divheight+"px");
}    

function slickr_flickr_set_slideshow_width(slideshow,width) {
    var s = jQuery(slideshow);
    s.find('div img').css("max-width",width+"px");
    s.css("width",width+"px");
} 

function slickr_flickr_set_slideshow_click(slideshow,link,target) {
    var s = jQuery(slideshow);
    if (link=='next') 
    	s.unbind('click').click( function() {  slickr_flickr_next_slide(s) ; });
	else if (link=='toggle') 
		s.unbind('click').click( function() {  slickr_flickr_toggle_slideshows() ; });
	else 
		if (target == "_self")
			s.unbind('click').click( function() {  window.location = link.replace(/\\/g, ''); }); 
		else
			s.unbind('click').click( function() {  window.open(link.replace(/\\/g, ''),target); }); 		
}

function slickr_flickr_toggle_slideshows() {
   if (slickr_flickr_slideshow_timer_on)
       slickr_flickr_stop_slideshows();
   else
       slickr_flickr_start_slideshows();
}

function slickr_flickr_stop_slideshows() {
    clearTimeout(slickr_flickr_slideshow_timer);
    slickr_flickr_slideshow_timer_on = false;
}

function slickr_flickr_start_slideshows() {
    var mindelay = 0;
    var mintimeout = 0;
    jQuery('.slickr-flickr-slideshow').each(function(index){
     var s =jQuery(this);
   	 options = s.parent().data('options');
   	 if (options) {
 	    if (s.hasClass('responsive')) {
    		if (('timeout' in options) && (options['timeout'] != '')) {
    			timeout = options['timeout'];
    		    if ((!(timeout == undefined)) && ((mintimeout == 0) || (timeout < mintimeout))) mintimeout = timeout;
    		} 		
    	} else {
    		if (('link' in options) && (options['link'] != ''))  slickr_flickr_set_slideshow_click(s,options['link'],options['target']);
    		if (('width' in options) && (options['width'] != ''))  slickr_flickr_set_slideshow_width(s,options['width']);
     		if (('height' in options) && (options['height'] != ''))  {
     			imgheight = parseInt(options['height']);
     			divheight = imgheight+ (s.hasClass("nocaptions") ? 0 : 30);
     			if (s.hasClass("descriptions")) divheight += 50;
 	    		slickr_flickr_set_slideshow_height(s,imgheight,divheight);
 			}
    		if (('delay' in options) && (options['delay'] != '')) {
    			delay = options['delay'];
    		    if ((!(delay == undefined)) && ((mindelay == 0) || (delay < mindelay))) mindelay = delay;
    		} 		 
		}
	  }
    });
	if (mindelay > 0) {
		slickr_flickr_stop_slideshows();
	    slickr_flickr_slideshow_timer = setInterval("slickr_flickr_next_slides()",mindelay);
	    slickr_flickr_slideshow_timer_on = true;
    }
	if (mintimeout > 0) {
	  jQuery('.slickr-flickr-slideshow.responsive').each(function(index){
    	var s =jQuery(this);
   		options = s.parent().data('options');
   		if (options) {
    		options['timeout'] = mintimeout;
			s.find('ul').responsiveSlides(options);
		}
	  });
    }

}

function slickr_flickr_start() {    
    if (jQuery('.slickr-flickr-galleria').size() > 0) {
    	jQuery(".slickr-flickr-galleria").each(function(index){
    	    var $options = jQuery(this).parent().data("options");
    	    var lazy = ('thumbnails' in $options) && ($options['thumbnails'] == 'lazy');
    	    jQuery(this).galleria($options);
			if (lazy) Galleria.ready( function(options) { this.lazyLoadChunks( 10, 200); } );
    	});
    } 
    
    jQuery(".slickr-flickr-gallery").find('img').hover( 
		function(){ jQuery(this).addClass('hover');},
		function(){ jQuery(this).removeClass('hover');}); 	
		
    jQuery(".slickr-flickr-gallery").each( function (index) {	
        $options = jQuery(this).parent().data("options");
  		if ($options && ('border' in $options) && ($options['border'] != '')) {
  			$id = jQuery(this).parent().attr('id');
	 		jQuery('<style type="text/css">#'+$id+' img.hover{ background-color:'+$options['border']+'; }</style>').appendTo('head');
 		}
 		if (jQuery(this).hasClass('sf-lightbox')) {
  			jQuery(this).find('a').each( function (ind) { 
  				jQuery(this).click( function(e) { 
					e.preventDefault();
					e.stopPropagation();
 	        		var lg = jQuery(this).closest('.sf-lightbox').parent();
 	        		var options = lg.data("options");
					options['start'] = ind; 
  					lg.lightGallery(options); 
  				} );
  			});
 		}
 	});
 	 
    slickr_flickr_start_slideshows();

}