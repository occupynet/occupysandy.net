(function($) {
	$.fn.sws_preloadify = function(options){
		
		function get_url_parameter(url,param){
			param = param.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
			var pattern = "[\\?&]"+param+"=([^&#]*)";
		  	var regex = new RegExp( pattern );
			var r = regex.exec( url );
		  	if( r == null )
		    	return "";
		  	else
		    	return unescape(r[1]);
		}		

		function add_parameter_to_url(_url,param){
		    _url += (_url.split('?')[1] ? '&':'?') + param;
		    return _url;
		}

		return this.each(function() {
			
			$(this).preloadify(options);
			
			if( $(this).hasClass('use-lightbox-1') && $(this).find('.frame-zoom-wrap').length==0 ){
				var src = unescape($(this).find('img').attr('rel'));
				var _class = "frame-zoom";
				if('undefined'==typeof(src)||src==''){
					var thumb_src = $(this).find('img').attr('src');
					src = get_url_parameter(thumb_src,'src');	
					src = src==''?thumb_src:src;		
				}else{
					_class = _class+' le-video';
				}	
			
				if($(this).hasClass('sws_image_frame')||$(this).hasClass('sws_image_frame_custom')){

					$(this).find('a')
						.attr('href', src )
						.sws_lightbox();
				}else{
					$(this).prepend(
						$("<a />")
							.attr('href', src )
							.attr('rel', $(this).attr('rel') )
							.addClass('frame-zoom-wrap')
							.css('position','absolute')
							.append(
								$("<div />")
								.addClass(_class)
								.css('opacity',0)
								.unbind('hover')
								.hover(function(){
									$(this).animate({'opacity':0.8},350);
								},function(){
									$(this).animate({'opacity':0},350);
								})				
							)
						).find('.frame-zoom-wrap')
						.sws_lightbox();					
				}
			}

		});
	}
	$.fn.sws_lightbox = function(){
		sws_lightbox = 'undefined'==typeof(sws_lightbox)?{}:sws_lightbox;
		options = {};
		if('undefined'!=typeof(sws_lightbox.modal)){
			options.modal = parseInt(sws_lightbox.modal);
		}
		if('undefined'!=typeof(sws_lightbox.autoresize)){
			options.autoresize = parseInt(sws_lightbox.autoresize);
		}

		if('undefined'!=typeof(sws_lightbox.opacity)){
			$.lightbox().overlay.options.style.opacity = parseFloat(sws_lightbox.opacity);
		}
		if('undefined'!=typeof(sws_lightbox.emergefrom)){
			$.extend($.lightbox().options, {'emergefrom':sws_lightbox.emergefrom});
		}
		

		if('undefined'!=typeof(sws_lightbox.showDuration)){
			$.lightbox().overlay.options.showDuration = parseInt(sws_lightbox.showDuration);
		}
		if('undefined'!=typeof(sws_lightbox.closeDuration)){
			$.lightbox().overlay.options.closeDuration = parseInt(sws_lightbox.closeDuration);
		}

		$(this).lightbox(options);
		return this.each(function() {
					
		});
	}
})(jQuery);
// JavaScript Document

jQuery(function(){

jQuery.fn.preloadify = function(options){
	
	var defaults = {
		             delay:0,
					 imagedelay:0,
					 mode:"parallel",
					 preload_parent:"a",
					 check_timer:200,
					 ondone:function(){ },
					 oneachload:function(image){  },
					 fadein:700 ,
					 force_icon:false
					};
	
	// variables declaration and precaching images and parent container
	 var options = jQuery.extend(defaults, options),
		 parent = jQuery(this),
		 timer,i=0,j=options.imagedelay,counter=0,images = parent.find("img").css({display:"block",visibility:"hidden",opacity:0}),
		 checkFlag = [],
		 imagedelayer = function(image,time){
			
			jQuery(image).css("visibility","visible").delay(time).animate({opacity:1},options.fadein,function(){ jQuery(this).parent().removeClass("preloader");  });
			
			};
		
	// add preloader to parent or wrap anchor depending on option	
	images.each(function(){
		
		if(jQuery(this).parent(options.preload_parent).length==0)
		jQuery(this).wrap("<a class='preloader' />");
		else
		jQuery(this).parent().addClass("preloader");
		
		checkFlag[i++] = false;
				
		});
	
	
	
	
	// convert into image array
	images = jQuery.makeArray(images);
	counter = 0;
	
	// function to show image 
	function showimage(i)
	{
		if(checkFlag[i]==false)
			{
				counter++; 
				options.oneachload(images[i]);
				checkFlag[i] = true;
			}
				
		if(options.imagedelay==0&&options.delay==0)
			jQuery(images[i]).css("visibility","visible").animate({opacity:1},700);
		else if(options.delay==0)
		{
			imagedelayer(images[i],j);
			j += options.imagedelay;
		}
		else if(options.imagedelay==0)
		{
			imagedelayer(images[i],options.delay);
			
		}
		else
		{
			imagedelayer(images[i],(options.delay+j));
			j += options.imagedelay;
		}
				
	}
	
	// 	preload images parallel
	function preload_parallel()
	{
		for(i=0;i<images.length;i++)
		{
			if(images[i].complete==true)
			{
				showimage(i);
			 
			}
		}
	}
	
	// shows images based on index with respect to parent container
	function preload_sequential()
	{
		
			if(images[i].complete==true)
			{
				showimage(i);
				 i++;
			}
	}
	
	i=0;j=options.imagedelay;
	// keep on checking after predefined time, if image is loaded
	function init(){
	timer = setInterval(function(){
		
		if(counter>=checkFlag.length)
		{
			clearInterval(timer);
			options.ondone();
			
			return;
		}
		
		
		if(options.mode=="parallel")
		preload_parallel();
		else
		preload_sequential();
		
		},options.check_timer);
		
	}
	
  if(options.force_icon==true){	
  var src = jQuery(".preloader").css("background-image");
 
	var pattern = /url\(|\)|"|'/g;
	src = src.replace(pattern,'');
	
	
	var icon = jQuery("<img />",{
		
		id : 'loadingicon' ,
		src : src
		
		}).hide().appendTo("body");
	
	timer = setInterval(function(){
		
		if(icon[0].complete==true)
		{
			clearInterval(timer);
			setTimeout(function(){ init(); },options.check_timer);
			 icon.remove();
			return;
		}
		
		},50);
		
	
  }
  else
	init();
	
	
	
	}
	
})


/* ------------------- End of plugin -------------------- */