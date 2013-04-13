jQuery(document).ready(function($){
	if( $('#sws-insert-tool-trigger').length > 0 && $('#sws-insert-tool').length > 0){
		$('#sws-insert-tool-trigger').click(function(e){
			$('#sws-insert-tool').css('top', $(document).scrollTop() );
			$('#sws-insert-tool').fadeIn();		
		});
		
		$('#sws-insert-tool').find('.sws-close-icon-a').click(function(e){
			$('#sws-insert-tool').fadeOut();	
		});
	}
});

/* CSSEditor */
jQuery(document).ready(function($){
	$('#cs_category').change(function(){
		DropdownChildUpdate(this.id,'cs_shortcode');
	});
	
	$('#cs_shortcode').change(function(){
		$('#shortcode-preview').empty();
		var _ID = this.value;
		if(_ID>0){
			$('#css-fields').html('Loading shortcode fields...');
			$('#css-mce-fields-cont').show();
			$('#css-fields').load(ajaxurl,{'action':'mce_list_fields','ID':_ID},function(){
				
			});
		}else{
			if( ''==$('#cs_category').val() ){$('#css-mce-fields-cont').hide();return;}
			$(this).find('option').each(function(i,o){
				var option_value = $(o).attr('value');
				var option_label = $(o).html();
				var preview = $(o).attr('rel');
				if('undefined'!=typeof(preview)&&''!=preview){
					
					$('<div></div>').addClass('sws-preview-item')
					.append('<a></a>')
					.find('a')
						.attr('href','#css-mce-form-anchor').click(function(e){
							$("#cs_shortcode").val(option_value).change();
						})
						.append('<img src="'+preview+'"></img>')
						.append('<div class="sws-preview-caption">'+option_label+'</div>')
					.end()
					.appendTo('#shortcode-preview');	
				}
			});
			$('#css-mce-fields-cont').hide();
		}
	});
	$('#cs_category').val('').change();
	$('#cs_shortcode').val('').change();	
});

function insert_csshortcode(){
	jQuery(document).ready(function($){
    	var win = window.dialogArguments || opener || parent || top;
    	var str = '';
		if($('.mce-item').length>0){
			$('.mce-item').each(function(){
				if( $(this).is(':checkbox') ){
					var _val = $(this).is(':checked')?$(this).val():'';
				}else{
					var _val = $(this).val();
				}
				if( $(this).hasClass('mce-escape') ){
					_val = escape(_val);
				}
				
				if( $(this).hasClass('parse-with-rel') ){
					try {
						if(''!=$(this).attr('rel')){
							eval($(this).attr('rel'));
						}
					}catch(e){
						console.log(e.description);
					}
				}
				
				if( $(this).hasClass('mce-scopentag') ){
					str += '[' + _val;
				}
				
				if( $(this).hasClass('mce-property') ){
					if( $(this).hasClass('mce-skip-blank') && ''==_val){
					
					}else{
						str += ' ' + $(this).attr('name').replace('sws_','') + '=' + '"' + _val +'"';
					}
				}
				
				if( $(this).hasClass('mce-scclose') ){
					str += ']';
				}
			
				if( $(this).hasClass('mce-content') ){
					str += ']' + _val;
				}				
				
				if( $(this).hasClass('mce-scclosetag') ){
					str += ' [/' + _val + '] ';
				}
			});
		}
		
		send_to_editor(str);
		var ed;
		if ( typeof tinyMCE != 'undefined' && ( ed = tinyMCE.activeEditor ) && !ed.isHidden() ) {
			ed.setContent(ed.getContent());
		}
		$('#sws-insert-tool').fadeOut();
	});
}

function csv_to_datatable(csv){
	var arr  = CSVToArray( csv );
	var str  = '<table>';
	var cols = 0;
	if(arr.length>0){
		cols = arr[0].length;
		if(cols>0){
			str+="<thead><tr>";
			for(j=0;j<cols;j++){
				str+="<th>"+arr[0][j]+"</th>";
			}
			str+="</tr></thead><tbody>";		
			if(arr.length>1){
				for(i=1;i<arr.length;i++){
					str+= i%2==0? '<tr>':'<tr class="odd">';
					if(arr[i].length==cols){
						for(j=0;j<arr[i].length;j++){
							str+="<td>"+arr[i][j]+"</td>";
						}					
					}
					str+='</tr>';
				}
			}
			str+='</tbody>';
		}
	}
	str+='</table>';
	return str;
}

function CSVToArray( strData, strDelimiter ){
    strDelimiter = (strDelimiter || ",");
    var objPattern = new RegExp( ("(\\" + strDelimiter + "|\\r?\\n|\\r|^)" + "(?:\"([^\"]*(?:\"\"[^\"]*)*)\"|" + "([^\"\\" + strDelimiter + "\\r\\n]*))"),"gi");
    var arrData = [[]];
    var arrMatches = null;
    while (arrMatches = objPattern.exec( strData )){

            var strMatchedDelimiter = arrMatches[ 1 ];
            if (
                    strMatchedDelimiter.length &&
                    (strMatchedDelimiter != strDelimiter)
                    ){
                    arrData.push( [] );

            }
            if (arrMatches[ 2 ]){
                    var strMatchedValue = arrMatches[ 2 ].replace(new RegExp( "\"\"", "g" ),"\"");
            } else {
                    var strMatchedValue = arrMatches[ 3 ];
            }
            arrData[ arrData.length - 1 ].push( strMatchedValue );
    }
    return( arrData );
}

/* CSSEditor End */

/* mce_list_fields */
function set_helpers(){
	set_ui_icon_helper();
	set_colorpicker_helper();
	set_slider();
}

function set_slider(){
	jQuery(document).ready(function($){
		$( ".sws-rangeinput" ).each(function(i,inp){
			if(!$(inp).data("rangeinput")){
				$(this).parent().find('.slider').remove();//because of clone, remove the already added html element.
				if( undefined==$(this).attr('max') ){
					var arr = $(this).parent().attr('rel').split('|');
					$(this).attr('min',arr[0]).attr('max',arr[1]).attr('step',arr[2]);
				}
				$(this).parent().attr('rel', $(this).attr('min')+'|'+$(this).attr('max')+'|'+$(this).attr('step') );	
				$(this).rangeinput();
			}			
		});
	});
}

function set_colorpicker_helper(){
	jQuery(document).ready(function($){
		if($('.sws-colorpicker').length==0)
			return;
		$('.sws-colorpicker').ColorPicker({
			onSubmit: function(hsb, hex, rgb, el) {
				$(el).val(hex);
				$(el).ColorPickerHide();
			},
			onBeforeShow: function () {
				$(this).ColorPickerSetColor(this.value);
			}
		})
		.bind('keyup', function(e){
			$(this).ColorPickerSetColor(this.value);
			 if (e.keyCode == 27) { $(this).ColorPickerHide(); }
		});
	});
}

function set_ui_icon_helper(){
	jQuery(document).ready(function($){
		$('.helper-ui-icon li').hover(function(){$(this).addClass('ui-state-hover');},function(){$(this).removeClass('ui-state-hover');})
			.click(function(){
				$(this).parent().parent().parent().find('input:first').val( $(this).attr('title') );
			});	
	});
}

/* mce_list_fields END */