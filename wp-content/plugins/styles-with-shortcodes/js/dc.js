Array.prototype.in_array=function(elem){
    return ("#"+this.join("#")+"#").indexOf("#"+elem+"#") > -1;
} 

function get_bundles(){
	jQuery('#install-message').empty().append('<div class="row-message"><span>Requesting downloadable content list, please wait...</span></div>');
	jQuery('#installing').fadeIn();
		
	var args = {
		action:'rh_get_bundles_'+rh_download_panel_id
	};
	
	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: args,
		success: function(data){
			jQuery('#installing').hide();
			if(data.R=='OK'){
				rh_bundles  = data.BUNDLES;		
				populate_bundles(rh_bundles);
			}else if(data.R=='ERR'){
				jQuery('#messages').removeClass('updated').addClass('error').html(data.MSG);
			}else{
			
			}	
		},
		error: function(jqXHR, textStatus, errorThrown){
			jQuery('#installing').hide();
			jQuery('#messages').removeClass('updated').addClass('error').html('Service not available please try again later. Error status: '+textStatus+', and error message: '+errorThrown);
		},
		dataType: 'json'
	});		
}

var populating_bundle = false;
function populate_bundles(bundles){
	if(bundles.length>0 && !populating_bundle){
		jQuery(document).ready(function($){		
			populating_bundle = true;
			$('#bundles').empty();
			var filtered_bundle = [];
			//--
			if(rh_filter=='new'){
				$.each(bundles,function(i,o){
					if( o.recent==1 ){
						filtered_bundle[ filtered_bundle.length ] = o;
					}
				});
			}else if(rh_filter=='downloaded' && rh_downloaded.length>0){
				$.each(bundles,function(i,o){
					if( rh_downloaded.in_array(o.id) ){
						filtered_bundle[ filtered_bundle.length ] = o;
					}
				});
			}else{
				filtered_bundle = bundles;
			}
			add_bundle(filtered_bundle);
		});
	}
}

function add_bundle(bundles){
	if(bundles.length>0){
		jQuery(document).ready(function($){
			o=bundles.shift();
			var tr = $('<tr></tr>')
				.css('opacity','0')
				.append(
					$('<td></td>')
						.addClass('dc-name')
						.append('<strong>'+o.name+'</strong>')
						.append(
							$('<div class="action-links"></div>')
								.append(
									$('<a>Download</a>')
										.attr('rel',o.id)
										.attr('href','javascript:void(0);')
										.click(function(e){download_bundle(e);})
								)
								.append('<span> | </span>')
								.append(
									$('<a>Visit site</a>')
										.attr('target','_blank')
										.attr('href',o.url)
								)
						)
				)
				.append($('<td></td>').addClass('dc-version').html(o.version))
				.append($('<td></td>').addClass('dc-filesize').html(readablizeBytes(o.filesize)))
				.append($('<td></td>').addClass('dc-description').html(o.description))
				.appendTo($('#bundles'))
				.animate({opacity:1},500,'linear',function(){
					add_bundle(bundles);
				});
		});
	}else{
		populating_bundle = false;
	}		
}

function download_bundle(e){
	jQuery(document).ready(function($){
		$('#install-message').empty().append('<div class="row-message"><span>Downloading content...</span></div>');
		$('#installing').fadeIn();
		var args = {
			action:'rh_download_bundle_'+rh_download_panel_id,
			id:jQuery(e.target).attr('rel')
		};
		
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: args,
			success: function(data){
				$('#installing').hide();
				if(data.R=='OK'){
					$('#messages').removeClass('error').addClass('updated').html(data.MSG);
				}else if(data.R=='ERR'){
					$('#messages').removeClass('updated').addClass('error').html(data.MSG);
				}else{
					$('#messages').removeClass('updated').addClass('error').html('Invalid ajax response, please try again.');	
				}
			},
			error: function(jqXHR, textStatus, errorThrown){
				$('#installing').hide();
				$('#messages').removeClass('updated').addClass('error').html('Operation returned error status: '+textStatus+', and error message: '+errorThrown);
			},
			dataType: 'json'
		});	
	});	
}

function readablizeBytes(bytes){
	if(bytes==null||bytes=='')return'0 bytes';
	try{
	    var s = ['bytes', 'kb', 'MB', 'GB', 'TB', 'PB'];
	    var e = Math.floor(Math.log(bytes)/Math.log(1024));
	    return (bytes/Math.pow(1024, Math.floor(e))).toFixed(2)+" "+s[e];	
	}catch(e){}
	return '';
}