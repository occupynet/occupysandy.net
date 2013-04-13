(function() {
    tinymce.create('tinymce.plugins.sws_code', {
        init : function(ed, url) {			
			var t = this;			
			t._handle_sws_code(ed, url);
			
        },
        createControl : function(n, cm) {
            return null;
        },
        getInfo : function() {
            return {
                longname : "SWS code",
                author : 'Alberto Lau',
                authorurl : 'http://plugins.righthere.com/',
                infourl : 'http://plugins.righthere.com/',
                version : "1.0.0"
            };
        },
		_handle_sws_code : function(ed, url) {
			var codeHTML;
			codeHTML = '<img alt="{id}" src="' + url + '/images/code.jpg" class="mceSWScode mceItemNoResize" title="Double click to edit" />';
			
			ed.onPostRender.add(function() {
				if (ed.theme.onResolveName) {
					ed.theme.onResolveName.add(function(th, o) {
						if (o.node.nodeName == 'IMG') {
							if ( ed.dom.hasClass(o.node, 'mceSWScode') )
								o.name = 'swscode';
						}
					});
				}
			});
			
			ed.onBeforeSetContent.add(function(ed, o) {
				if ( o.content ) {
					o.content = o.content.replace(/<!--swscode(.*?)-->/g, function (m) {
						brr = m.match(/<!--swscode(.*?)-->/);
						return codeHTML.replace('{id}',brr[1]);
					});
				}
			});

			ed.onPostProcess.add(function(ed, o) {
				if (o.get)
					o.content = o.content.replace(/<img[^>]+>/g, function(im) {
						if (im.indexOf('class="mceSWScode') !== -1){
							im = '<!--swscode'+jQuery(im).attr('alt')+'-->';
						}
						return im;
					});
			});
			
			ed.onDblClick.add(function(ed, e) {
				var n = e.target;
				if( n.nodeName === 'IMG' && ed.dom.hasClass(n, 'mceSWScode') ){
					var post_id = jQuery('#post_ID').val();
					if(post_id>0){
						w = ed.windowManager.open({
						        file : url + '/sws_code.php?code_id=' + n.getAttribute('alt') + '&post_id=' + post_id,
						        width : 500 + ed.getLang('example.delta_width', 0),
						        height : 350 + ed.getLang('example.delta_height', 0),
						        inline : 1,
								resizable: true,
								maximizable: true
								
						}, {
						        sws_code : n.getAttribute('alt')
						});					
					}
				}				
			});		
				
		}		
    });
    tinymce.PluginManager.add('sws_code', tinymce.plugins.sws_code);
})();