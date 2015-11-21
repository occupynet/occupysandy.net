<?php
class Slickr_Flickr_Feed{

	const FLICKR_REST_URL = 'https://api.flickr.com/services/rest/?method=%1$s&lang=en-us&format=feed-rss_200&api_key=%2$s%3$s';
	const FLICKR_FEED_URL = 'http://api.flickr.com/services/feeds/%1$s?lang=en-us&format=feed-rss_200%2$s';

	var $args = array(); //arguments
	var $method = ''; //access method
	var $use_rss = true;  //use RSS feed
	var $use_rest = false; //use REST access
	var $extras = 'description,url_o,dims_o'; //extra params to fetch when using API
	var $container = 'photos'; //XML container of photo elements
	var $api_key = ''; //Flickr API Key
	var $user_id = ''; //Flickr NS ID 
	var $cache; //Flickr cache
	var $cache_expiry; //Flickr Cache expiry in seconds
	var $get_dims = false;
	var $error = false; //is an error
	var $available = 0 ; //total photos available
	var $pages = 0; //total pages available
	var $flickr = false; //phpFlickr Object
	var $message = ''; //error message
	var $photos = array(); //results
    
	function get_photos() { return $this->photos; }
	function get_count() { return count($this->photos); }  
	function get_pages() { return $this->pages; }
	function is_error() { return $this->error; }  
	function get_message() { return $this->message; }
 
	function __construct($params) {
		$this->extras .= 'upload'==$params['date_type'] ? ',date_upload' : ',date_taken'; 
    	$this->build_command($params);  //set up method and args
		if (!$this->use_rss) $this->set_php_flickr();
  	}
  
  	function build_command($params) {
  		$tags = strtolower(str_replace(" ","",$params['tag']));
		$this->set_cache ($params['cache'], $params['cache_expiry']);
  		$this->get_dims = array_key_exists('restrict',$params) && ('orientation'==$params['restrict']);
		$this->user_id = $params['id'];	
  		$group = strtolower(substr($params['group'],0,1));
  		if ($params['use_key'] == 'y') {
  		  	$this->use_rest = true;
 			$this->use_rss = $params['use_rss'] != 'n'; 
 			$this->api_key = $params['api_key'];		
			switch($params['search']) {
				case "single": {
					$this->method = "flickr.photos.getInfo";
					$this->args = array("photo_id" => $params['photo_id']);
					$this->container = 'photo';    	         
					$this->use_rss = false;    	         
					break;
    	    	}
            	case "favorites": {
               		$this->method = "flickr.favorites.getPublicList";
    	         	$this->args = array("user_id" => $params['id']);
    	         	break;
    	    	}
    	    	case "groups": {
    	            $this->method = "flickr.groups.pools.getPhotos";
    	            $this->args = array("group_id" => $params['id']);
    	            if (!empty($tags)) $this->args["tags"] = $tags;
    	            break;
    	    	}
        		case "galleries": {
        	        $this->method = "flickr.galleries.getPhotos";
        	        $this->args = array("gallery_id" => $this->verify_gallery_id($params['gallery']));
        	        break;
        		}
    			case "sets": {
        	        $this->method = "flickr.photosets.getPhotos";
        	        $this->args = array('photoset_id' => $params["set"], 'extras' => $this->extras);
					$this->container = 'photoset';
					$this->use_rss = false;
        	        break;
        	  	}
        	  	default: {
                	$this->method = "flickr.photos.search";
                	$id = $group=='y' ? 'group_id' : 'user_id'; 
                	$this->args[$id] = $params['id'];
                	if (!empty($params['license'])) $this->args["license"] = $params['license'];
                	$dates = $this->get_dates($params);
                	if (count($dates)>0) $this->args = $this->args + $dates;
                	if (!empty($params['tagmode'])) $this->args["tag_mode"] = $params['tagmode'];
                	if (!empty($tags)) $this->args['tags'] = $tags;
					if (!empty($params['text'])) $this->args["text"] = trim($params['text']);  
          		}
        	}
   		} else {
        	switch($params['search']) {
        		case "favorites": { $this->method = "photos_faves.gne"; $this->args = array("nsid" => $params['id']); break; }
        		case "groups": { $this->method = "groups_pool.gne"; $this->args = array("id" => $params['id']);  break;}
        		case "friends": { $this->method = "photos_friends.gne"; $this->args = array("id" => $params['user_id'], "display_all" => "1");  break;}
        		case "sets": {$this->method = "photoset.gne"; $this->args = array("nsid" => $params['id'], "set" => $params['set']);  break;}
	        	default: {
		           	$this->method = "photos_public.gne";
        	       	$id = $group=='y' ? 'g' : 'id'; 
            	   	$this->args[$id] = $params['id'];
                	if (!empty($params['tagmode'])) $this->args["tagmode"] = $params['tagmode'];
                	if (!empty($tags)) $this->args['tags'] = $tags;              
           		}
        	}
   		}
      if ('single' != $params['search'])
         $this->args['per_page']= min($params['items'],(int) $params['per_page']);
	}
	
	function set_cache($cache, $cache_expiry) {
		switch ($cache) {
			case 'db': $this->cache = 'db'; break;
			case 'fs': $this->cache = 'fs'; break;
			case 'off': $cache_expiry = 0; 
			default: $this->cache = 'on';
		}
		$this->cache_expiry = max($cache_expiry,60); //max refresh rate is 60 seconds
	}

	function set_php_flickr() {
		if ($this->flickr) return true; //set up already
		if (empty($this->api_key)) return false; //no key so can't set it up
		require_once(dirname(__FILE__).'/class-phpFlickr.php');				
		$this->flickr = new phpFlickr ($this->api_key); 	
		switch ($this->cache) {
			case 'db': 
				global $table_prefix;
 	   			$prefix = $table_prefix ? $table_prefix : 'wp_';				
				$this->flickr->enableCache ('db', 'mysql://'.DB_USER.':'.DB_PASSWORD.'@'.DB_HOST.'/'.DB_NAME, 
					$this->cache_expiry, $prefix . Slickr_Flickr_Cache::FLICKR_CACHE_TABLE); 
				break;
			
			case 'fs': 
				$uploads = wp_upload_dir();
				$cache_dir = $uploads['basedir'].'/'.Slickr_Flickr_Cache::FLICKR_CACHE_FOLDER;
				if (!file_exists($cache_dir)) @mkdir($cache_dir);
				if (file_exists($cache_dir)) $this->flickr->enableCache('fs', 
					$cache_dir, $this->cache_expiry); 
				break;
				
			default: 
				$this->flickr->enableCache ('custom', 
					array( 'Slickr_Flickr_Cache::get_cache','Slickr_Flickr_Cache::set_cache'),  $this->cache_expiry); 			
		}
		return true; 
  	}

  	function get_dates($params) {
	    $args= array();
	    $date_type = $params['date_type']=='upload'?"upload":"taken";
	    $sort_type = $params['date_type']=='upload'?"posted":"taken";
	    $min_param = 'min_'.$date_type.'_date';
	    $max_param = 'max_'.$date_type.'_date';
	    if (empty($params['date'])) {
	    	$after = $this->convert_date_to_timestamp($params['after']);
	    	if ($after)  $args[$min_param] = $after;
	   		$before = $this->convert_date_to_timestamp($params['before'],false);
	    	if ($before) { 
	    		$args[$max_param] = $before;
	    		$args['sort'] = 'date-'.$sort_type.'-desc';
	    	} else {
	    		$args['sort'] = 'date-'.$sort_type.'-asc';
				}
	    } else {
	    	if ($params['date']=='publish') {
				global $post;
				$date = substr($post->post_date,0,10);
	    		$after = $this->convert_date_to_timestamp($date);
	    		if ($after) $before = $after+(24*60*60)-1;
	 		} else {
	    		$after = $this->convert_date_to_timestamp($params['date']);
	    		if ($after) $before = $after+(24*60*60)-1;
	    	}
	    	if ($after && $before) {
	    		$args[$min_param] = $after;
	    		$args[$max_param] = $before;
			}
	    }
		return $args;
 	 }

  	function convert_date_to_timestamp($date, $start=true) {
		if (empty($date)) return false;
		if (strpos($date,':') === FALSE) {
			return strtotime($date. ($start?' 00:00:00':' 23:59:59'));
		} else {
			return strtotime($date);
		}
  }

  	function implode_args($args) {
        $return = '';
        foreach ($args as $k => $v) {
            $return .= '&' . $k . '=' . $v;
        }
        return $return;
    }
    
  	function verify_gallery_id($gallery) { //replace short gallery id by full gallery_id
		if (strpos($gallery,'-') === false) {
			if ($this->set_php_flickr()) {
				$resp = $this->flickr->urls_lookupGallery('https://www.flickr.com/photos/'.$this->user_id.'/galleries/'.$gallery);
				if ($resp) {
					$result = $resp['gallery'];
	    			$gallery = $result['id'];
				} else {
					$this->message = $this->flickr->error_msg ;
    				$this->error = true;
 				}
    		}
    	}
    	return $gallery;
    }

	function feed_cache_expiry($content, $url) {
  		return $this->cache_expiry;
	}

	function get_feed_url() { 
  		if ($this->use_rest) 
  			return sprintf(self::FLICKR_REST_URL, $this->method, $this->api_key ,$this->implode_args($this->args));
    	else
  			return sprintf(self::FLICKR_FEED_URL, $this->method, $this->implode_args($this->args));
  	}

	function fetch_feed() {
		if ($this->cache_expiry != Slickr_Flickr_Options::get_default('cache_expiry')) 
 	 		add_filter('wp_feed_cache_transient_lifetime', array(&$this,'feed_cache_expiry'),10,2);
 		$rss = fetch_feed($this->get_feed_url());  //use WordPress simple pie feed handler 
        if ( is_wp_error($rss) ) {
        	$this->message = "<p>Error fetching Flickr photos: ".$rss->get_error_message()."</p>";  
			$this->error = true;
		} else {
	    	$numitems = $rss->get_item_quantity($this->args["per_page"]);
	        if ($numitems == 0)  {
	        	$this->message = '<p>No photos available right now.</p><p>Please verify your settings, clear your RSS cache on the Slickr Flickr Admin page and check your <a target="_blank" href="'.$this->get_feed_url().'">Flickr feed</a></p>';
			} else {
				if ($numitems > ($this->args["per_page"] - 5)) $this->pages++;
	        	$rss_items = $rss->get_items(0, $numitems);
	    		foreach ( $rss_items as $item ) {
	    	    	$this->photos[] = new Slickr_Flickr_Photo($item);  //feed items and load into object
	    	    }
	    	}
		}
	}
  
	function call_flickr_api() {
		$this->photos = array();
    	if (($resp = $this->flickr->call($this->method, $this->args)) 
    	&& ($results = $resp[$this->container])
    	&& is_array($results)) {
         if ($this->container == 'photo') {
    		   $this->available = 1;
    		   $this->pages = 1;         
				$this->photos[] = new Slickr_Flickr_Api_Photo($this->user_id,$results,$this->get_dims);            
         } else {
    		   $this->available = array_key_exists('total', $results) ? $results['total'] : 0;
			   $this->pages = array_key_exists('pages', $results) ? $results['pages'] : 0;
			   if (array_key_exists('photo', $results) && is_array($results['photo'])) {
				  foreach ($results['photo'] as $photo) 
				     $this->photos[] = new Slickr_Flickr_Api_Photo($this->user_id,$photo,$this->get_dims);
			   } else {
				  $this->message = 'No photos found.';
				  $this->error = true;
			   }
         }
    	} else {
    		$this->message = $this->flickr->error_msg ;
    		$this->error = true;
 		}
	}
	

	function fetch_photos($page=0) {
		$this->photos = array();
		if ($page > 1) $this->args['page'] = $page ;
		if ($this->use_rss) {
 	 		$this->fetch_feed();
 		} else {
 			$this->call_flickr_api();
 			if (($page > 1) && $this->error) $this->error = false;  //suppress error on additional calls
    	}
    	return $this->photos;
	}

}
