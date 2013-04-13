<?php
/*
* @author Alberto Lau alau@albertolau.com 
*/
class al_importer {
	var $force_new = true;
	var $post_author;
	var $post_author_rewrite = true;
	var $upload_dir;
	function al_importer($args=array()){
		global $wpdb,$userdata;
		$defaults = array(
			'post_types'	=> $wpdb->get_col("SELECT DISTINCT(post_type) FROM `{$wpdb->posts}`",0),
			'meta_files'	=> array(
				(object)array(
					'post_type'=>'attachment',
					'meta_key'=>'_wp_attached_file'
				)
			),
			'import_taxonomies' 	=> true,
			'post_author'			=> $userdata->ID,
			'post_author_rewrite'	=> true,
			'force_new'				=> false
		);
		foreach($defaults as $property => $default){
			$this->$property = isset($args[$property])?$args[$property]:$default;
		}	
		//-----------
		$this->upload_dir = wp_upload_dir();
	}
	
	function read_bundle($filename='bundle.dat'){
		return $this->decode_bundle(file_get_contents($filename));
		//return unserialize(gzuncompress(file_get_contents($filename)));
	}
		
	function decode_bundle($content){
		return unserialize(@gzuncompress($content));
	}
		
	function bundle_have_posts($bundle){
		return is_object($bundle)&&property_exists($bundle,'posts')&&is_array($bundle->posts)&&count($bundle->posts)>0?true:false;
	}
	function get_columns($tablename){
		global $wpdb;
		return $wpdb->get_col("SHOW COLUMNS FROM `$tablename`",0);
	}	
	function import_bundle($bundle){
		global $userdata;
		//-----------------------
		if(!$this->bundle_have_posts($bundle)){
			$this->last_error = 'Bundle is empty.';
			return false;
		}		
		//-----------------------
		$import_args = array(
			'force_new'				=> $this->force_new,
			'posts'					=> $bundle->posts,
			'replace'				=> array()
		);
		//------------------------
		if($this->post_author_rewrite){
			$import_args['replace']['post_author']=$this->post_author;
		}
		//--
		$this->remove_actions();//prevent things like twitting the imported posts.
		
		$this->term_taxonomy = property_exists($bundle,'term_taxonomy')?$bundle->term_taxonomy:array();
		$this->terms = property_exists($bundle,'terms')?$bundle->terms:array();
		
		$this->import($import_args);
		return true;
	}


	
	function import($args){
		global $wpdb;
		$fields = $this->get_columns($wpdb->posts);
		extract($args);
		if(!isset($posts)||!is_array($posts)||count($posts)==0)return false;
		foreach($posts as $post){
			$new_post = array();
			foreach($fields as $field){
				$new_post[$field] = property_exists($post,$field)?$post->$field:null;
			}
			if(isset($replace)&&is_array($replace)&&count($replace)>0){
				foreach($replace as $field => $value){
					$new_post[$field]=$value;
				}
			}		
				
			if($force_new){
				unset($new_post['ID']);
			}else{
				$imported_ids = $this->get_imported_ids($post->ID);
				if(!empty($imported_ids)){
					foreach($imported_ids as $existing_post_id){
						$new_post['ID']=$existing_post_id;
						$this->import_post($args,$new_post,$post);
					}
					continue;
				}else{
					//does not exist locally, create.
					unset($new_post['ID']);
				}
			}
			$this->import_post($args,$new_post,$post);
		}
	}
	
	function import_post($args,$new_post,$post){
		$post_id = wp_insert_post( $new_post , false );
		if($post_id>0){
			$import_key = $post->ID;
			//------------------------
			$post->ID = $post_id;
			//import metadata
			$post = $this->import_metadata($post);	
			//set import key (after updating meta data)
			update_post_meta($post_id,'_import_key',$import_key);
			//import terms
			if($this->import_taxonomies){
				$post = $this->import_terms($post);
			}
			//import children
			if(property_exists($post,'children')&&is_array($post->children)&&count($post->children)>0){
				foreach($post->children as $child_post_type){
					if(property_exists($post,$child_post_type)&&is_array($post->$child_post_type)&&count($post->$child_post_type)>0){
						$child_args = $args;
						$child_args['posts'] = $post->$child_post_type;
						$child_args['replace']['post_parent'] = $post->ID;				
						$this->import($child_args);
					}
				}
			}
		}
	}
	
	function import_terms($post){
		if(property_exists($post,'term_relationships')&&is_array($post->term_relationships)&&count($post->term_relationships)>0){
			$terms=array();
			foreach($post->term_relationships as $tr){
				$term_taxonomy_id = $tr->term_taxonomy_id;
				if(isset($this->term_taxonomy[$term_taxonomy_id])){
					$taxonomy = $this->term_taxonomy[$term_taxonomy_id]->taxonomy;
					if(taxonomy_exists($taxonomy)){
						$term_id = $this->get_term_ids_from_term_taxonomy_id($term_taxonomy_id);
						if(false!==$terms){
							if(!in_array($term_id,$terms)){	
								$terms[$taxonomy][]=intval($term_id);//without casting the int, the term gets duplicated.
							}
						}
					}				
				}
			}
			
			if(count($terms)>0){
				foreach($terms as $taxonomy => $arr){
					wp_set_object_terms(  $post->ID, $arr, $taxonomy );
				}
			}
		}
		return $post;
	}
	
	function get_term_ids_from_term_taxonomy_id($term_taxonomy_id){
		$term_id = $this->term_taxonomy[$term_taxonomy_id]->term_id;
		$name = $this->terms[$term_id]->name;
		$taxonomy = $this->term_taxonomy[$term_taxonomy_id]->taxonomy;
		$parent = 0;
		if($this->term_taxonomy[$term_taxonomy_id]->parent>0){
			$parent = $this->get_term_ids_from_term_taxonomy_id($this->term_taxonomy[$term_taxonomy_id]->parent);
		}
		
		$args = array(
			'description' 	=> $this->term_taxonomy[$term_taxonomy_id]->description,
			'slug'			=> $this->terms[$term_id]->slug,
			'parent'		=> $parent
		);
		
		$e_term=term_exists($name,$taxonomy,$parent);
		if($e_term){
			if(is_array($e_term)){
				return $e_term['term_id'];
			}else if(is_numeric($e_term)){
				return $e_term;
			}else{		
				return 0;
			}
		}
		
		$result = wp_insert_term( $name, $taxonomy, $args );
		return is_array($result)&&isset($result['term_id'])?$result['term_id']:false;
	}
	
	function import_metadata($post){
		if(property_exists($post,'metadata') && is_array($post->metadata) && count($post->metadata)>0){
			foreach($post->metadata as $i => $meta_data){
				if(!empty($this->meta_files)){
					foreach($this->meta_files as $mf){
						if($mf->post_type==$post->post_type && $mf->meta_key==$meta_data->meta_key){
							$meta_data = $this->unpack_meta_file($meta_data);
						}
					}				
				}
				$post->metadata[$i]=$meta_data;
				$value = is_serialized($meta_data->meta_value)?unserialize($meta_data->meta_value):$meta_data->meta_value;
				update_post_meta($post->ID,$meta_data->meta_key,$value);
				update_post_meta($post->ID,$meta_data->meta_key,$value);
			}
		}
		return $post;
	}
		
	function decode_file($encoded){
		return unserialize(gzuncompress(stripslashes(base64_decode(strtr($encoded, '-_,', '+/=')))));
	}
			
	function unpack_meta_file($meta_data){
		$r = (array)$meta_data;		
		if(property_exists($meta_data,'packed_file')){
			//$this->upload_dir['path'].'/'.$meta
			//$filename = $this->upload_dir['path'].'/'.basename($meta_data->meta_value);
			$filename = wp_unique_filename( $this->upload_dir['path'], basename($meta_data->meta_value) );
			$filename = $this->upload_dir['path']."/$filename";
	
			$s = file_put_contents( $filename , $this->decode_file($meta_data->packed_file) );
			$meta_data->meta_value = $filename;		
		}
		return $meta_data;
	}	
	
	function remove_actions(){
		remove_all_actions('save_post');
		remove_all_actions('new_to_publish');
	}	
	
	function get_imported_ids($import_key){
		global $wpdb;
		$import_key = intval($import_key);
		$ids = $wpdb->get_col("SELECT M.post_id FROM `{$wpdb->postmeta}` M INNER JOIN `{$wpdb->posts}` P ON P.ID=M.post_id WHERE M.meta_key='_import_key' AND M.meta_value='$import_key'",0);
		return is_array($ids)?$ids:array();
	}
}

?>