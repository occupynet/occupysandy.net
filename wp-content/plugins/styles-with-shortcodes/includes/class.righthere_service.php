<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class righthere_service {
	function righthere_service(){
	
	}
	
	function rh_service($url){
		error_reporting(0);
		if( 'true'==ini_get('allow_url_fopen') || true==ini_get('allow_url_fopen') ){
			return $this->get_remote_with_fopen($url);
		}else if(function_exists('curl_init')&&function_exists('curl_setopt')&&function_exists('curl_exec')){
			return $this->get_remote_with_curl($url);
		}else{
			$r = array(
				'R'=>'ERR',
				'MSG'=>'Cannot communicate with remote service; either allow_url_fopen most be set to On in php.ini, or lib_curl most be enabled.'
			);
			return $r;
		}
	}
	
	function get_remote_with_fopen($url){
		$handle=fopen($url,'r');
		if($handle){
			$contents = '';
			while (!feof($handle)) {
			  $contents .= fread($handle, 8192);
			}
			fclose($handle);	
			
			$r = json_decode($contents);
			if(is_object($r)&&property_exists($r,'R')){
				return $r;
			}else{
				return false;
			}			
		}	
		return false;
	}
	
	function get_remote_with_curl($url){
		$ch = curl_init();
		//--
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
		curl_setopt($ch, CURLOPT_REFERER, site_url() ); 
		curl_setopt($ch, CURLOPT_VERBOSE, false ); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false ); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false ); 
		curl_setopt($ch, CURLOPT_HEADER, false ); 
		curl_setopt($ch, CURLOPT_NOBODY, false );
		curl_setopt($ch, CURLOPT_USERAGENT, "PHP CURL" ); 
		//--
		curl_setopt($ch, CURLOPT_POST, 0);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');	  
		curl_setopt($ch, CURLOPT_URL, $url);	    
		$contents = curl_exec($ch);
		curl_close($ch);
		//--parsing	
		$r = json_decode($contents);
		if(is_object($r)&&property_exists($r,'R')){
			return $r;
		}else{
			$r = array(
				'R'=>'ERR',
				'MSG'=>'Bad service response format'
			);
			return $r;
		}			
	}	
}
?>