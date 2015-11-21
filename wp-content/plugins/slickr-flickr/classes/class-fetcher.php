<?php
class Slickr_Flickr_Fetcher {

	protected $id;
	protected $params;
	protected $pages;
	protected $message;
	protected $feed;

	function get_message() { return $this->message;}
	function get_pages() { return $this->pages;}
			
	function __construct($id, $params) {  
		$this->id = $id;
		$this->params = $params;
		$this->message = '';
		$this->pages = 0;
	}

	function fetch_photos() {
		$photos = $this->fetch_single_source() ;
	  	if ($photos && !empty($this->params['restrict'])) $photos = $this->restrict_photos($photos);
		return $photos;
	}

	function fetch_single_source() {
		$this->pages = 0;
        $this->feed = new Slickr_Flickr_Feed($this->params);
		$page=$this->params['page'];
        $photos = $this->feed->fetch_photos($page);
		$this->pages = $this->feed->get_pages();
  	  	if ((count($photos) == 0) || $this->feed->is_error()) {
  	  		$this->message = $this->feed->get_message();
			return false;
		} else {
  	  		return $photos; //return array of photos
		}
	}

	function restrict_photos ($items) {
	    $filtered_items = array();
	    if ($this->params['restrict']=='orientation') {
	    	$orientation = $this->params['orientation'];
	    	foreach ($items as $item)  if ($item->get_orientation()==$orientation) $filtered_items[] = $item;
	    	return $filtered_items;
		} else {
		    return $items;
		}
	}

}
