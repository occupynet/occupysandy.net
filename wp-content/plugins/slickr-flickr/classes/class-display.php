<?php

class Slickr_Flickr_Display {

	private $pages = 1;
	private $id;
	private $params;

	function __construct() {}

	function show($attr) {
		Slickr_Flickr_Public::note_active();
  		$this->params = shortcode_atts( Slickr_Flickr_Options::get_options(), $attr ); //apply plugin defaults    
  		foreach ( $this->params as $k => $v ) if (($k != 'id') && ($k != 'options') && ($k != 'galleria_options') && ($k != 'attribution') && ($k != 'flickr_link_title')) $this->params[$k] = strtolower($v); //set all params as lower case
  		$this->params['tag'] = str_replace(' ','',$this->params['tag']);
		if (strpos($this->params['tag'],',-') !==FALSE) $this->params['tagmode'] = 'bool';
  		if (empty($this->params['id']) && empty($this->params['text']) ) return "<p>Please specify a Flickr ID for this ".$this->params['type']."</p>";
 		if ( ('single'==$this->params['search']) && empty($this->params['photo_id'])) return "<p>Please specify the photo id of the single photo you want to display.</p>";
  		if ( (!empty($this->params['tagmode'])) && empty($this->params['tag']) && ($this->params['search']=="photos")) return "<p>Please set up a Flickr tag for this ".$this->params['type']."</p>";
  		if (empty($this->params['api_key']) && ($this->params['use_key'] == "y")) return "<p>Please add your Flickr API Key in Slickr Flickr Admin settings to fetch more than 20 photos.</p>";
		$this->set_api_required();
  		if (empty($this->params['use_key'])) $this->force_api_key(); //set api_key if required by other parameters
  		$rand_id = rand(1,10000);
  		$this->id = empty($this->params['element_id']) ? $this->get_unique_id($attr,$rand_id) : $this->params['element_id'];

      $photos = $this->fetch_photos();
      if (! is_array($photos)) return $this->no_photos($photos); //return if an array of photos is not returned. then show error message if not in silent mode

  		$divclear = '<div style="clear:both"></div>';
  		$attribution = empty($this->params['attribution'])?"":('<p class="slickr-flickr-attribution align'.$this->params['align'].'">'.$this->params['attribution'].'</p>');
  		$bottom = empty($this->params['bottom'])?"":(' style="margin-bottom:'.$this->params['bottom'].'px;"');
  		$lightboxrel = $thumb_scale = $pagination = $s = '';
  		switch ($this->params['type']) {
    		case "slightbox": {
	    		if (empty($this->params['ptags'])) $this->params['ptags'] = "on"; //paragraph tags arounds titles	
        		if (empty($this->params['thumbnail_size'])) $this->params['thumbnail_size'] = 'medium'; //set default slideshow size as Medium
        		$this->set_lightboxrel($rand_id);
        		$divstart = sprintf('%1$s<div class="slickr-flickr-slideshow%2$s %3$s %4$s %5$s%6$%7$s" %8$s>',
        			$attribution, 
					$this->params['lightbox'] == 'sf-lightbox' ? ' sf-lightbox' : '',
        			$this->params['orientation'], $this->params['thumbnail_size'],
        			$this->params['descriptions']=='on' ? 'descriptions ' : '',
        			$this->params['captions']=='off' ? 'nocaptions ' : '',
        			$this->params['align'], $bottom);

        		$divend = '</div>'.$this->set_options( array_merge (
        			$this->slideshow_options(), 
        			$this->lightbox_options($this->prepare_lightbox_data($photos))));
        		break;
       	 	}
   		case "slideshow": {
	    	if (empty($this->params['ptags'])) $this->params['ptags'] = "on"; //paragraph tags arounds titles	
    		$divstart = $attribution.'<div class="slickr-flickr-slideshow '.$this->params['orientation'].' '.$this->params['size'].($this->params['responsive']=="on" ? " responsive" : "").($this->params['descriptions']=="on" ? " descriptions" : "").($this->params['captions']=="off" ? " nocaptions " : " ").$this->params['align'].'"'.$bottom.'>';
 			$divend = '</div>'.$this->set_options($this->slideshow_options());
        	break;
        }
   		case "galleria": {
    		if (empty($this->params['thumbnail_size'])) $this->params['thumbnail_size'] = 'square'; //set default thumbnail size as Square
    		if ($this->params['galleria'] == 'galleria-original') {
				$this->params['galleria_theme'] = 'original'; //set a default value
				if (empty($bottom))
					$style = ' style="visibility:hidden;"';
        	    else
        	    	$style = substr($bottom,0,strlen($bottom-2)).'visibility:hidden;"';
        	    $startstop = $this->params['pause']== 'off' ? '' : ('| <a href="#" class="startSlide">start</a> | <a href="#" class="stopSlide">stop</a>');
 			    $nav = <<<NAV
<p class="nav {$this->params['size']}"><a href="#" class="prevSlide">&laquo; previous</a> {$startstop} | <a href="#" class="nextSlide">next &raquo;</a></p>
NAV;
				$data = false;
			} else {		
				$style = $bottom;
				$nav= '';
				$data = $this->prepare_galleria_data($photos);
			}
			switch ($this->params['nav']) {
				case "above": { $nav_below = ''; $nav_above = $nav; break; }
				case "below": { $nav_below = $nav; $nav_above = ''; break; }
				case "none": { $nav_below = ''; $nav_above = ''; break; } 	
				default: { $nav_below = $nav; $nav_above = $nav; break; }
			}
    	    $divstart = '<div class="slickr-flickr-galleria '.$this->params['orientation'].' '.$this->params['size'].' '.$this->params['align'].' '.$this->params['galleria_theme'].'"'.$style.'>'.$attribution.$nav_above;
    	    $divend = $divclear.$attribution.$nav_below.'</div>'.$this->set_options($this->galleria_options($data));
			Slickr_Flickr_Public::add_galleria_theme($this->params['galleria_theme']); //add count of gallerias on page		
    	    break;
    	    }
   		default: {
    	    $this->set_thumbnail_params();
    	    $this->set_lightboxrel($rand_id);
    	    $divstart = sprintf('<div class="slickr-flickr-gallery%1$s"%2$s>%3$s', 
    	    	$this->params['lightbox']=='sf-lightbox' ? ' sf-lightbox' : '', $bottom, $attribution);
    	    $divend = '</div>'.$this->set_options($this->lightbox_options($this->prepare_lightbox_data($photos)));
    	    }
  		}

   		if (($this->params['type']=='galleria') && ($this->params['galleria'] == 'galleria-latest')) 
   			$content = '';
   		else 
   			$content = $this->wrap_photos($photos);
		$class= $this->params['class'] ? sprintf(' class="%1$s"',$this->params['class']) : ''; 
		return sprintf('<div id="%1$s"%2$s>%3$s%4$s%5$s%6$s%7$s</div>', 
			$this->id, $class, $divstart, $content, $divend, $pagination, $divclear);
	}
	
	function no_photos($error) {
      return $this->params['silent'] ? '' : ($this->params['message'] ? $this->params['message'] : $error);
	}

	function wrap_photos ($photos) {
		$s = $format = $element = $element_style = $gallery_style = $gallery_class = '';
  		switch ($this->params['type']) {
			case "slideshow":		
			case "slightbox":
				if ($this->params['responsive'] == 'on') {	
	 				$format= '<ul class="rslides">%1$s</ul>';
					$element = 'li'; 				
				} else {
					$element = 'div'; 
				}
				break;
			case "gallery":
				$element_style = $this->params['thumbnail_style'];
				$gallery_style = $this->params['gallery_style'];
				$gallery_class = $this->params['gallery_class'];
 			default: 
	 			$format= '<ul%2$s%3$s>%1$s</ul>';
				$element = 'li'; 
  		}
		$start = $this->get_start(count($photos));
	  	$i = 0;
		foreach ( $photos as $photo ) {
			$i++;
			$s .= sprintf('<%2$s%3$s%4$s>%1$s</%2$s>', $this->get_image($photo), 
				$element, $element_style, $start==$i?' class="active"': '');
		}
	  	return empty($format) ? $s : sprintf($format,  $s, $gallery_class, $gallery_style);
	}

	function prepare_lightbox_data($photos) {
		if ($this->params['lightbox'] != 'sf-lightbox') return false;
		$data = array();		
		foreach ( $photos as $photo ) {
			$image = $this->prepare_image($photo);
			$item = array();
		    $item['thumb'] = $image['thumb_url'];
    		$item['src'] = $image['full_url'];
    		$item['caption'] = $this->params['flickr_link']=='on' ?
	    		sprintf('<a %1$s title="%2$s" href="%3$s">%4$s</a>', 
	    			empty($this->params["flickr_link_target"]) ? '' : sprintf('target="%1$s"',$this->params["flickr_link_target"]),
	    			$this->params["flickr_link_title"], $image['link'], $image['title']) : $image['title'];
    		if (in_array($this->params["descriptions"], array('on','lightbox'))) $item['desc'] = strip_tags($image['description'],'<a><br>');    			    
			$data[] = $item;
		}	
		return $data;	
	}

	function prepare_galleria_data($photos) {
		$data = array();
		foreach ( $photos as $photo ) {
			$image = $this->prepare_image($photo);
			$item = array();
		    $item['thumb'] = $image['thumb_url'];
    		$item['image'] = $image['full_url'];
    		$item['title'] = $image['captiontitle'];
    		if ($this->params["descriptions"] =='on') $item['description'] = $image['description'];
     		if ($this->params["flickr_link"]=="on") $item['link'] = $image['link'];    			    
			$data[] = $item;
		}	
		return $data;	
	}

	function get_unique_id($attr, $rand_id) {
		if (is_array($attr) && array_key_exists('random',$attr)) {
	  		$unique_id = md5(serialize($attr));
		} else  {
			$unique_id = !empty( $this->params['tag'] ) ? $this->params['tag'] : (
	               	!empty( $this->params['set'] ) ? $this->params['set'] : (
              		!empty( $this->params['gallery'] ) ? $this->params['gallery'] : 'recent'));
            $unique_id = strtolower(preg_replace("{[^A-Za-z0-9_]}",'',$unique_id)).'_'.$rand_id; //strip spaces, backticks, dashes and commas
		}
		return 'flickr_'.$unique_id; 
	}

	function force_api_key() {
	  if (empty($this->params['use_key']) 
	  && ! empty($this->params['api_key']) 
	  && (($this->params['items'] > 20 ) || ($this->params['api_required'] == 'y'))) 
	   	$this->params['use_key'] = 'y'; // set use_key if API key is available and is either required or request is for over 20 photos
	}

	function set_api_required() {
		$this->params['api_required'] = (($this->params['use_rss'] == 'n')
			|| (! empty($this->params['license'])) || (! empty($this->params['text'])) || ($this->params['search'] == 'single')
			|| (! empty($this->params['date'])) || (! empty($this->params['before'])) || (! empty($this->params['after']))
			|| (! empty($this->params['private'])) || ($this->params['page'] > 1) || ($this->params['search'] == 'galleries') 
			|| ( !empty($this->params['tag']) && ($this->params["search"]=="groups"))) ? 'y' : 'n'; 
	}

	function set_slideshow_onclick() {
	  $link='';
	  if (empty($this->params['link']))
	    if ($this->params['pause'] == "on")
	        $link = "toggle" ;
	     else
	        $link = $this->params['type'] == "slightbox" ? "" : "next";
	  else
	    $link = $this->params['link'];
	  return $link;
	}

	function set_thumbnail_params() {
	    $thumb_rescale= false;
	    switch ($this->params["thumbnail_size"]) {
	      case "thumbnail": $thumb_width = 100; $thumb_height = 75; $thumb_rescale = true; break;
	      case "s150": $thumb_width = 150; $thumb_height = 150; $thumb_rescale = true; break;
	      case "small": $thumb_width = 240; $thumb_height = 180; $thumb_rescale = true; break;
	      case "s320": $thumb_width = 320; $thumb_height = 240; $thumb_rescale = true; break;
	      case "medium": $thumb_width = 500; $thumb_height = 375; $thumb_rescale = true; break;
	      case "m640": $thumb_width = 640; $thumb_height = 480; $thumb_rescale = true; break;
	      case "m800": $thumb_width = 800; $thumb_height = 640; $thumb_rescale = true; break;
	      case "large": $thumb_width = 1024; $thumb_height = 768; $thumb_rescale = true; break;	      
	      default: $thumb_width = 75; $thumb_height = 75; $this->params["thumbnail_size"] = 'square';
	    }
	    if ($this->params["orientation"]=="portrait" ) { $swp = $thumb_width; $thumb_width = $thumb_height; $thumb_height = $swp; }

	    if ($this->params["thumbnail_scale"] > 0) {
	        $thumb_rescale = true;
	        $thumb_width = round($thumb_width * $this->params["thumbnail_scale"] / 100);
	        $thumb_height = round($thumb_height * $this->params["thumbnail_scale"] / 100);
	    }
    	$this->params['image_style'] = $thumb_rescale ? (' style="height:'.$thumb_height.'px; max-width:'.$thumb_width.'px;"') : '';

    	if (($this->params['type'] == "gallery") && ($this->params['photos_per_row'] > 0)) {
    	    $li_width = ($thumb_width + 10);
    	    $li_height = ($thumb_height + 10);
    	    $gallery_width = 1 + (($li_width + 4) *  $this->params['photos_per_row']);
    	    $this->params['gallery_style'] = ' style=" width:'.$gallery_width.'px"';
    	    $this->params['thumbnail_style'] = ' style="width:'.$li_width.'px; height:'.$li_height.'px;"';
    	} else {
    	    $this->params['gallery_style'] = '';
    	    $this->params['thumbnail_style'] = '';
    	}
    	$this->params['gallery_class'] = $this->params['align'] ? (' class="'.$this->params['align'].'"'):'';
	}

	function prepare_image($photo) {
	    $image = array();
	    $image['link'] = $photo->get_link();
	    $oriented = $photo->get_orientation();
	    $title = $photo->get_title();
	    $description = $photo->get_description(); 
	    if ($description == '<p></p>') $description = '';
	    $image['border'] = $this->params['border']=='on'?' class="border"':'';
		$ptags = ('on'==$this->params['ptags']); //paragraph tags around title?
		//separator is required if title and description end up together on the same line
	    $sep = (($this->params["descriptions"] =='on') && ($this->params["type"] !='galleria') && ! $ptags) ? '.&nbsp;' : ''; 
	    $ptitle = empty($title) ? '' : sprintf(($ptags ? '<p%2$s>%1$s</p>' : '<span%2$s>%1$s</span>').$sep ,$title, $image['border']);
		$link_target = empty($this->params["flickr_link_target"]) ? '' : sprintf('target="%1$s"',$this->params["flickr_link_target"]);
	    $plink = sprintf($ptags ? '<p>%1$s</p>' : '%1$s' , 
	    	sprintf('<a title="%1$s" %2$s href="%3$s">%4$s</a>%5$s', $this->params["flickr_link_title"], $link_target, $image['link'], $title, $sep));
	    $image['captiontitle'] = $this->params["flickr_link"]=="on" ? ($this->params["lightbox"]=="none" ? $title : $plink) :$ptitle;
	    $image['alt'] = $this->params["descriptions"]=="on"? ($ptags ? $description : strip_tags($description,'<a>')) : "";
		$image['full_url'] = $this->params['size']=="original" ? $photo->get_original() : $photo->resize($this->params['size']);
	    $image['thumb_url'] = $photo->resize($this->params['thumbnail_size']);
	    $image['title'] = $title;
	    $image['description'] = $description;
		return $image;
	}

	function get_image($photo) {
		$image = $this->prepare_image($photo);
	    switch ($this->params['type']) {
	       case "slideshow": {	
	       		$format = $this->params['responsive'] == 'on' ? '<span class="caption">%1$s%2$s</span>' : '%1$s%2$s';
	            $caption = $this->params['captions']=='off' ? '' : sprintf($format,$image['captiontitle'],$image['alt']);
	            return  sprintf('<img src="%1$s" title="%2$s" alt="%3$s" %4$s />%5$s',
	            $image['full_url'], htmlspecialchars($image['title']), htmlspecialchars($image['alt']), $image['border'], $caption);
	        }
	       case "slightbox": {
	            $desc = $this->params["descriptions"]=="on" || $this->params["descriptions"]=="slideshow" ? $image['description'] : "";
	            $alt = $this->params["descriptions"]=="on" || $this->params["descriptions"]=="lightbox" ? $image['description'] : "";
	            $caption = $this->params['captions']=="off"?"":($image['captiontitle'].$desc);
	            $lightbox_title = $image['captiontitle'] . $alt;
	            return sprintf('<a %1$s href="%2$s" title="%3$s"><img src="%4$s" title="%5$s" alt="%6$s" %7$s /></a>%8$s',
	            	$this->params['lightboxrel'], $image['full_url'], htmlspecialchars($lightbox_title), 
	            	$image['thumb_url'], htmlspecialchars($image['title']), htmlspecialchars($alt), 
	            	$image['border'], $caption);
    	    }
    	   case "galleria": {
    	   		$caption = $this->params['captions']=="off"?"":$image['captiontitle'];
    	   		return sprintf('<a href="%1$s"><img src="%2$s" title="%3$s" alt="%4$s" /></a>',
    	   				$image['full_url'], $image['thumb_url'], htmlspecialchars($caption), htmlspecialchars($image['alt']));
    	    }
    	    default: {
				return $this->get_lightbox_html ($image);
    	    }
    	}
	}

	function get_lightbox_html ($image) {
    	if ($this->params['lightbox']=="none") { //if no lightbox then maybe link directly to Flickr
    		$image['full_url'] = !empty($this->params['link']) ?  $this->params['link'] : ('on'==$this->params['flickr_link'] ? $image['link'] : '') ; 
		}
    	$thumbcaption = $this->params['thumbnail_captions']=="on"?('<br/><span class="slickr-flickr-caption">'.$image['title'].'</span>'):"";
    	$full_caption= ($this->params["captions"]=="off" ? '' : $image['captiontitle']) . ($this->params["descriptions"]=="on" ? $image['alt'] : "");
		$img_title = empty($image['title']) ? '' : sprintf('title="%1$s"',htmlspecialchars($image['title']));
		$img_alt = empty($image['alt']) ? '' : sprintf('alt="%1$s"',htmlspecialchars($image['alt']));
		$title = ''; 
		if (! empty($full_caption)) switch ($this->params['lightbox']) {
	      case "sf-lightbox":  break; //no title 
	      case "fancybox":  $title = sprintf('title="%1$s"', htmlspecialchars($full_caption)); break; //use title
	      case "thickbox": $title = sprintf('title=\'%1$s\'', str_replace("'","&acute;",$full_caption)); break; //avoid thickbox issue with apostrophes
		  default: $title = sprintf('title="%1$s"', htmlspecialchars($full_caption));	
		}
		if (empty($image['full_url']))
    		return sprintf('<img src="%1$s" %2$s %3$s %4$s />%5$s',
				$image['thumb_url'], $this->params['image_style'], $img_alt, $img_title, $thumbcaption);
    	else	
    		return sprintf('<a href="%1$s" %2$s %3$s><img src="%4$s" %5$s %6$s %7$s />%8$s</a>',
				$image['full_url'], $this->params['lightboxrel'], $title, 
				$image['thumb_url'], $this->params['image_style'], $img_alt, $img_title, $thumbcaption);
	}

	function set_lightboxrel($rand_id) {
		$ptags = "off";
	    switch ($this->params['lightbox']) {
	      case "sf-lightbox": 	$lightboxrel = ''; break;
	      case "evolution": 	$lightboxrel = sprintf('rel="group%1%s" class="lightbox"',$rand_id);  break;
	      case "fancybox": 		$lightboxrel = sprintf('rel="fancybox_%1$s" class="fancybox"',$rand_id);  break;
	      case "slimbox":		$lightboxrel = sprintf('rel="lightbox-%1$s"',$rand_id);  break;
	      case "shutter":  		$lightboxrel = sprintf('class="shutterset_%1$s"',$rand_id);  break;
	      case "thickbox": 		$lightboxrel = sprintf('rel="thickbox-%1$s" class="thickbox"',$rand_id); break;
	      case "none":
	      case "norel": $lightboxrel = '' ; break;      
	      default:	$lightboxrel = 'rel="lightbox['.$rand_id.']"';  break;
	    }
		$this->params['lightboxrel'] = $lightboxrel;
 		$this->params['lightbox_id'] = $rand_id;
		if (empty($this->params['ptags'])) $this->params['ptags'] = $ptags; //paragraph tags arounds titles?
	}

	function get_start($numitems) {
	  $r = 1;
	  if ($numitems > 1) {
	     if ($this->params['start'] == "random")
	        $r = rand(1,$numitems);
	     else
	        $r = is_numeric($this->params['start']) && ($this->params['start'] < $numitems) ? $this->params['start'] : $numitems;
	     }
	   return $r;
	}

	function sort_photos ($items, $sort, $direction) {
		$do_sort = ($sort=="date") || ($sort=="title") || ($sort=="description");
	    $direction = strtolower(substr($direction,0,3))=="des"?"descending":"ascending";
	    if ($sort=="date") { foreach ($items as $item) { if (!$item->get_date()) { $do_sort = false; break; } } }
	    if ($sort=="description") { foreach ($items as $item) { if (!$item->get_description()) { $do_sort = false; break; } } }
	    $ordered_items = $items;
	    if ($do_sort) usort($ordered_items, array($this,'sort_by_'.$sort.'_'.$direction));
	    return $ordered_items;
	}

	function sort_by_description_descending($a, $b) { return strcmp($b->get_description(),$a->get_description()); }
	function sort_by_description_ascending($a, $b) { return strcmp($a->get_description(),$b->get_description()); }
	function sort_by_title_descending($a, $b) { return strcmp($b->get_title(),$a->get_title()); }
	function sort_by_title_ascending($a, $b) { return strcmp($a->get_title(),$b->get_title()); }
	function sort_by_date_ascending($a, $b) { return ($a->get_date() <= $b->get_date()) ? -1 : 1; }
	function sort_by_date_descending($a, $b) { return ($a->get_date() > $b->get_date()) ? -1 : 1; }

	function set_options($options) {
	    if (count($options) > 0) {
	    	$s = sprintf('jQuery("#%1$s").data("options",%2$s);', $this->id, json_encode($options) ); 
			if ( Slickr_Flickr_Options::get_option('scripts_in_footer')) {
	    		Slickr_Flickr_Public::add_jquery($s); //save for later
			} else {
				return sprintf('<script type="text/javascript">%1$s</script>', $s); //output it now
			}
		}
		return '';
	}
	
	function parse_json_options($json) {
		$options = array();
		$options_list = str_replace(';;',';',trim($json).';');
    	$more_options = array();
		if ((preg_match_all("/([^:\s]+):([^;]+);/i", $options_list, $pairs)) && (count($pairs)>2)) $more_options = array_combine($pairs[1], $pairs[2]);
		foreach ($more_options as $key => $value) {
			if (is_numeric($value)) {
				$options[$key] = $value + 0;
			} else {
			    $val = strtolower(trim($value));
				switch ($val) {
					case "false": { $options[$key] = false; break; }
					case "true": { $options[$key] = true; break; } 
					default:  $options[$key] = $val;
        	    }
			}
		}
		return $options; 
	}

	function galleria_options($data=false) {
	    $options = array();
		if ($this->params['galleria'] == 'galleria-original') {
			$options['delay'] = $this->params['delay'] * 1000;
			$options['autoPlay'] = $this->params['autoplay']=='on'?true:false;
			$options['captions'] = $this->params['captions']=='off'?false:true;
			$options['descriptions'] = $this->params['descriptions']=='on'?true:false;
	    } else {
			if (!empty($this->params['options'])) $options = $this->parse_json_options($this->params['options']);
			if (!empty($this->params['galleria_options'])) $options = array_merge($options,$this->parse_json_options($this->params['galleria_options']));
			if ($this->params['flickr_link_target']=='_blank') $options['popupLinks'] = true;
			if ($this->params['flickr_link_target']=='_self') $options['popupLinks'] = false;
    		if (!array_key_exists('autoplay',$options)) $options['autoplay'] = $this->params['delay']*1000; 
  		  	if (!array_key_exists('transition',$options)) $options['transition'] = 'fade';
  		  	if (!array_key_exists('transitionSpeed',$options)) $options['transitionSpeed'] = $this->params['transition']*1000;
  		  	if (!array_key_exists('showInfo',$options)) $options['showInfo'] = $this->params['captions']=='off' ? false: true;
  		  	if (!array_key_exists('imageCrop',$options)) $options['imageCrop'] = true;
  		  	if (!array_key_exists('carousel',$options)) $options['carousel'] = true;    	
  		  	if (!array_key_exists('responsive',$options)) $options['responsive'] = true;  
			if (!array_key_exists('debug',$options)) $options['debug'] = false;  
			if (!array_key_exists('height',$options)) $options['height'] = $this->params['orientation']=="portrait" ? 1.333 : 0.75;	    
			$options['theme'] = $this->params['galleria_theme'];
			if ($data && is_array($data) && (count($data)>0)) $options['dataSource']=$data;
    	}
		return $options;
	}

	function slideshow_options() {
		$options = array();
		if ($this->params['responsive'] == 'on') {
	    	$options['timeout'] = $this->params['delay'] * 1000;
	    	$options['auto'] = $this->params['autoplay']=="off"?false:true;
	    	$options['pause'] = $this->params['pause'] == "on";
	    	$options['maxwidth'] =  isset($this->params['width']) ? $this->params['width'] : '';
	    	$options['speed'] = isset($this->params['transition']) ? ($this->params['transition'] * 1000) : 500; 
		} else {		
    		$options['delay'] = $this->params['delay'] * 1000;
    		$options['autoplay'] = $this->params['autoplay']=="off"?false:true;
    		$options['transition'] = 500;
    		$options['link'] = $this->set_slideshow_onclick();
    		$options['target'] = $this->params['target'];    
    		if (isset($this->params['width'])) $options['width'] = $this->params['width'];
    		if (isset($this->params['height'])) $options['height'] = $this->params['height'];
    		if (isset($this->params['transition'])) $options['transition'] = $this->params['transition'] * 1000; 
   		}
    	return $options;
	}

	function lightbox_options($data = false) {
    	$options = array();
    	if (($this->params['lightbox'] == "sf-lightbox")) {
			if (!empty($this->params['options'])) $options = $this->parse_json_options($this->params['options']);
    		if (!array_key_exists('caption',$options)) $options['caption'] = $this->params['captions'] == 'off' ? false:true;
     		if (!array_key_exists('desc',$options)) $options['desc'] = (in_array($this->params['descriptions'], array('on', 'lightbox'))) ? true:false;
    		if (!array_key_exists('auto',$options)) $options['auto'] = $this->params['autoplay']=='on'?true:false;
   			if (!array_key_exists('pause',$options)) $options['pause'] = $this->params['delay'] * 1000;
   			if (!array_key_exists('speed',$options)) $options['speed'] = $this->params['transition']*1000;
  			if (!array_key_exists('mode',$options)) $options['mode'] = 'fade';
			if ($data && is_array($data) && (count($data)>0)) {
				$options['dynamic'] = true;
				$options['dynamicEl'] = $data;
			}
		}
    	if (array_key_exists('thumbnail_border',$this->params) 
		&& !empty($this->params['thumbnail_border'])) 
			$options['border'] = $this->params['thumbnail_border']; 
		return $options;
	}

	function fetch_photos() {
      	$fetcher = new Slickr_Flickr_Fetcher($this->id, $this->params) ;
      	$photos = $fetcher->fetch_photos() ;
      	if (!is_array($photos)) return $fetcher->get_message(); //return error
	  	if (!empty($this->params['sort'])) $photos = $this->sort_photos ($photos, $this->params['sort'], $this->params['direction']);
	  	return $photos; //return array of photos
	}

}
