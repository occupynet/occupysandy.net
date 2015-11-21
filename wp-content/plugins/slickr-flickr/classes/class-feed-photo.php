<?php
class Slickr_Flickr_Photo {

  var $url;
  var $width;
  var $height;
  var $orientation;
  var $title;
  var $description;
  var $date;
  var $link;
  var $original;

  function __construct($item) {
    $this->title = $this->cleanup($item->get_title());
    $this->date = $item->get_date('U');
    $this->description = '';


    $data = $item->get_description();
    preg_match_all('/<img src="([^"]*)"([^>]*)>/i', $data, $m);
    $this->url = set_url_scheme($m[1][0], 'https');

    preg_match_all('/<a href="([^"]*)"([^>]*)>/i', $data, $m);
    $this->link = $m[1][1];

    preg_match_all('/width="([^"]*)"([^>]*)>/i', $data, $m);
    $this->width = $m[1][0];

    preg_match_all('/height="([^"]*)"([^>]*)>/i', $data, $m);
    $this->height = $m[1][0];

    $this->orientation = $this->height > $this->width ? "portrait" : "landscape" ;


    if ($enclosure = $item->get_enclosure()) {
  		$this->original = $enclosure->get_link();
    	$this->description = html_entity_decode($enclosure->get_description());    
    	if ($thumbs = $enclosure->get_thumbnails()) {
			$last = count($thumbs)-1;
	    	$this->original = $thumbs[$last];
		}
	}
    if (empty($this->description) 
    && preg_match_all('!<p>(.+?)</p>!sim',$data,$m,PREG_PATTERN_ORDER)
    && is_array($m) 
    && count($m[1]) >2) 
    	$this->description = "<p>".$m[1][2]."</p>";
  }

  function get_url() { return $this->url; }
  function get_width() { return $this->width; }
  function get_height() { return $this->height; }
  function get_orientation() { return $this->orientation; }
  function get_title() { return $this->title; }
  function get_description() { return $this->description; }
  function get_date() { return $this->date; }
  function get_link() { return $this->link; }
  function get_original() { return $this->original; }

  /* Function that removes all quotes */
  function cleanup($s = null) {
    return $s?str_replace("\n", "<br/>",$s):'';
  }
  
  /* Function that returns the correctly sized photo URL. */
  function resize($size) {

    $url_array = explode('/', $this->url);
    $photo = array_pop($url_array); //strip the filename

    switch($size)  {
      case 'square': $suffix = '_s.';  break;
      case 'thumbnail': $suffix = '_t.';  break;
      case 'small': $suffix = '_m.';  break;
      case 'm640': $suffix = '_z.';  break;
      case 'm800': $suffix = '_c.';  break;
      case 's320': $suffix = '_n.';  break; 
      case 's150': $suffix = '_q.';  break;           
      case 'large': $suffix = '_b.';  break;
      default:  $suffix = '.';  break; // Medium
      }

    $url_array[] =  preg_replace('/(_(s|t|c|n|q|m|b|z))?\./i', $suffix, $photo); //
    return implode('/', $url_array);
  }

}
