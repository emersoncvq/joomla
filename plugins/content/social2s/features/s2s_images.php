<?php
	/**************************************/
	/*************** S2S IMAGES ***********/
	/**************************************/

class s2s_images {

	public static $s2s_obj;

	public function __construct()
    {
    	self::$s2s_obj = PlgContentSocial2s::$s2s_obj;
    }


    /**
     * [getImagesArticle get com_content article images]
     * @param  [type] $article [description]
     * @return [type]          [description]
     */
    public static function getImagesArticle($article){
		
    	$params = self::$s2s_obj->params;
    	$og_add_dom_img= $params->get('og_add_dom_img', '0');

		$images= array();

		//image def ALWAYS
		if($always = self::prepareDefAlways($params)) return $always;

		//ALL IMAGES
		$images= array();
			
		//Introimages
		if($article->images){

			$introimages = json_decode($article->images);
			
			//full
			if($introimages->image_fulltext){
				PlgContentSocial2s::updateS2sOgDebug('Fulltext img', 'Single: '.$introimages->image_fulltext,'success', 'image');
				array_push($images,$introimages->image_fulltext);
			}

			//intro
			if(!$params->get('og_skip_intro_img','0')){
				if($introimages->image_intro){
					PlgContentSocial2s::updateS2sOgDebug('Intro img', 'Single: '.$introimages->image_intro,'success', 'image');
					array_push($images,$introimages->image_intro);
				}
			}
		}
	

		//SINGLE IMAGE
		if($params->get('og_multi_img','0') == '0'){
			$images_ok = self::prepareImages($images);

			if(count($images_ok) >= 1){
				$implosion = implode("<br>",array_slice($images_ok, 0,1));
				PlgContentSocial2s::updateS2sOgDebug('Candidate IMG', 'Single: '.$implosion,'success', 'image');

				//var_dump(array_slice($images_ok, 0,1));
				return array_slice($images_ok, 0,1);
			}
		}


		//DOM
		if($og_add_dom_img){
			if($og_add_dom_img == '1') $dom_images = self::getDomImage($article->introtext, false);
			if($og_add_dom_img == '2') $dom_images = self::getDomImage($article->introtext, true);
		 	
		 	$images = array_merge($images,$dom_images);
		}

		$images_ok = self::prepareImages($images);


		//Default image LAST CHANCE
		if(empty($images_ok)){

			if($params->get('opengraph_default_image_opt', '0') == '3'){
				array_push($images_ok,$params->get('opengraph_default_image',''));
				PlgContentSocial2s::updateS2sOgDebug('Article image', 'last chance','success', 'image');
			}
		}

		$implosion = implode("<br>",$images_ok);
		PlgContentSocial2s::updateS2sOgDebug('Article image', 'Multi: '.$implosion,'success', 'image');

		return $images_ok;
	}

    /**
     * [getImagesArticle get com_content article images]
     * @param  [type] $article [description]
     * @return [type]          [description]
     */
    public static function getImagesJevents($article){
		
    	$params = self::$s2s_obj->params;
    	$og_add_dom_img= $params->get('og_add_dom_img', '0');

		$images= array();

		//image def ALWAYS
		if($always = self::prepareDefAlways($params)) return $always;

		//ALL IMAGES
		$images= array();
			
/*
		//SINGLE IMAGE
		if($params->get('og_multi_img','0') == '0'){
			$images_ok = self::prepareImages($images);

			if(count($images_ok) >= 1){
				$implosion = implode("<br>",array_slice($images_ok, 0,1));
				PlgContentSocial2s::updateS2sOgDebug('Candidate IMG', 'Single: '.$implosion,'success', 'image');

				//var_dump(array_slice($images_ok, 0,1));
				return array_slice($images_ok, 0,1);
			}
		}
*/

		if($og_add_dom_img){
			if($og_add_dom_img == '1') $dom_images = self::getDomImage($article->_description, false);
			if($og_add_dom_img == '2') $dom_images = self::getDomImage($article->_description, true);
		 	
		 	$images = array_merge($images,$dom_images);
		}

		$images_ok = self::prepareImages($images);


		//Default image LAST CHANCE
		if(empty($images_ok)){

			if($params->get('opengraph_default_image_opt', '0') == '3'){
				array_push($images_ok,$params->get('opengraph_default_image',''));
				PlgContentSocial2s::updateS2sOgDebug('Article image', 'last chance','success', 'image');
			}
		}

		$implosion = implode("<br>",$images_ok);
		PlgContentSocial2s::updateS2sOgDebug('Article image', 'Multi: '.$implosion,'success', 'image');

		return $images_ok;
	}
	/**
	 * [getImagesK2 get k2 images]
	 * @param  [type] $article [description]
	 * @return [type]          [array]
	*/
	public static function getImagesK2($article){
		
		$params = self::$s2s_obj->params;
		$def = $params->get('opengraph_default_image_opt', '0');
		$og_add_dom_img= $params->get('og_add_dom_img', '0');

		$item = $article;

		//$date = JFactory::getDate($item->modified);
		//$timestamp = '?t='.$date->toUnix();

    	$params = self::$s2s_obj->params;
		

		//image def ALWAYS
		if($always = self::prepareDefAlways($params)) return $always;


		//ALL IMAGES
		$images= array();
			
		if (JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$item->id).'_XL.jpg'))
		{
	   		$image = 'media/k2/items/cache/'.md5("Image".$item->id).'_XL.jpg';
	   		array_push($images,utf8_decode($image));

	   	}elseif (JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$item->id).'_Generic.jpg'))
		{
	    	$image = 'media/k2/items/cache/'.md5("Image".$item->id).'_Generic.jpg';
	    	array_push($images,utf8_decode($image));
		}


		//SINGLE IMAGE
		if($params->get('og_multi_img','0') == '0'){
			$images_ok = self::prepareImages($images);
			if(count($images_ok) >= 1){

				$implosion = implode("<br>",$images_ok);
				PlgContentSocial2s::updateS2sOgDebug('K2 Image', 'Single: '.$implosion,'success', 'image');
				return $images_ok;
			}
		}

		//DOM
		if($og_add_dom_img){
			if($og_add_dom_img == '1') $dom_images = self::getDomImage($article->introtext, false);
			if($og_add_dom_img == '2') $dom_images = self::getDomImage($article->introtext, true);
		 	
		 	$images = array_merge($images,$dom_images);
		}

		$images_ok = self::prepareImages($images);

		//Default image LAST CHANCE
		if(empty($images_ok)){

			if($params->get('opengraph_default_image_opt', '0') == '3'){
				array_push($images_ok,$params->get('opengraph_default_image',''));
				PlgContentSocial2s::updateS2sOgDebug('K2 image', 'last chance','success', 'image');
			}
		}

		$implosion = implode("<br>",$images_ok);
		PlgContentSocial2s::updateS2sOgDebug('K2 Image', 'Multi: '.$implosion,'success', 'image');

		return $images_ok;

	}

    /**
     * [getImagesArticle get com_content article images]
     * @param  [type] $article [description]
     * @return [type]          [description]
     */
    public static function getImagesVirtuemart($article){
		
    	$params = self::$s2s_obj->params;
    	$def = $params->get('opengraph_default_image_opt', '0');
    	$og_add_dom_img= $params->get('og_add_dom_img', '0');
		$images= array();

		//image def ALWAYS
		if($always = self::prepareDefAlways($params)) return $always;

		$max = $params->get('og_max_multi_img', '3');


		//SINGLE IMAGE
		if($params->get('og_multi_img','0') == '0'){
			
			$max = '1';

		}else{

			$max = $params->get('og_max_multi_img', '3');
		}


		//VM images
		if($article->images){
			
			foreach ($article->images as $key => $image) {
				if($image->file_is_forSale == '0' && $image->file_mimetype != ''){

					if(count($images) < $max){
						array_push($images,utf8_decode($image->file_url));
					}
				}
			}
		}

		//DOM
		if($og_add_dom_img){

			//si ya hay suficientes imágenes, no parseo el dom
			if(count($images) < $max){

				if($og_add_dom_img == '1') $dom_images = self::getDomImage($article->product_desc, false);
				if($og_add_dom_img == '2') $dom_images = self::getDomImage($article->product_desc, true);
			 	
			 	$images = array_merge($images,$dom_images);
			}
		}
		

		$images_ok = self::prepareImages($images);

		//Default image LAST CHANCE
		if(empty($images_ok)){

			if($params->get('opengraph_default_image_opt', '0') == '3'){
				array_push($images_ok,$params->get('opengraph_default_image',''));
				PlgContentSocial2s::updateS2sOgDebug('VM image', 'last chance','success', 'image');
			}
		}

		$implosion = implode("<br>",$images_ok);
		PlgContentSocial2s::updateS2sOgDebug('VM Image', 'Multi: '.$implosion,'success', 'image');


		return $images_ok;
		

	}


	/**
	 * [getDomImage get image from dom]
	 * @param  [type] $html [description]
	 * @return [type]       [string]
	*/
	private static function getDomImage($html, $og_add_dom_img=false){

		$image = '';
		$images = array();

		$domdoc = new DOMDocument();
		@$domdoc->loadHTML($html);
		$tags = $domdoc->getElementsByTagName('img');

		foreach ($tags as $tag) {
		    $image = utf8_decode($tag->getAttribute('src'));
		    if(self::img_size($image)){
		    	array_push($images,$image);
		    	//only one
		    	if(!$og_add_dom_img){
		    		break;
		    	}
				
		    }
		}

		return $images;
	}

	/**
	 * [img_size get sizes of images]
	 * @param  [type] $img [description]
	 * @return [type]      [description]
	*/
	private static function img_size($img){

		$params = self::$s2s_obj->params;

		$og_skip_min_img = $params->get('og_skip_min_img', 0);
		
		if(!$og_skip_min_img) return true;

		if (substr($img, 0, 7) == "http://"){
			$final_img_to_size = $img;
		}elseif (substr($img, 0, 8) == "https://"){
			$final_img_to_size = $img;
		}else{
			$final_img_to_size = JPATH_SITE.'/'.$img;
		}

		$image_w = getimagesize($final_img_to_size);

		if($image_w[0]>=200){
			return true;
	    }else{
			return false;
	    }
	}




	/**
	 * [prepare images]
	 * @param  [type] $img [description]
	 * @return [type]      [description]
	*/
	private static function prepareImages($images){
		$params = self::$s2s_obj->params;

		$images_ok = array();

		//check image
		foreach ($images as $key => $image) {
			if(self::img_size($image)) array_push($images_ok,$image);
		}

		$images_unique = array_unique($images_ok);
		$images_ok = array_values($images_unique);
		//asort($images_ok);
		//remove maximun
		$max = $params->get('og_max_multi_img', '3');

		$images_ok = array_slice($images_ok, 0, $max);

		return $images_ok;
	}	

	/**
	 * [prepare default images]
	 * @param  [type] $img [description]
	 * @return [type]      [description]
	*/
	private static function prepareDefAlways($params){
		$images= array();
		//ALWAYS
		if($params->get('opengraph_default_image_opt', '0') == '1'){

			array_push($images,$params->get('opengraph_default_image',''));
			PlgContentSocial2s::updateS2sOgDebug('Default image', 'Always:<br>'.$params->get('opengraph_default_image',''),'success', 'image');
			
			return $images;
		}else{
			return false;
		}
	}



    
}

?>