<?php
	/**************************************/
	/**************** OPENGRAPH ************/
	/**************************************/

class s2s_og {

	public static $s2s_obj;

	public static $s2s_og;

	public static $s2s_article;

	public function __construct()
    {
    	self::$s2s_obj = PlgContentSocial2s::$s2s_obj;
    	self::$s2s_og = (object) array();
    }

    static function init($s2s_obj, $row){

    	self::$s2s_article = $row;
    	$context = self::$s2s_obj->context;
    	$params = self::$s2s_obj->params;

    	//prepare data
    	self::$s2s_og->title = '';
    	self::$s2s_og->description = '';
    	self::$s2s_og->metadescription = '';

    	//check
    	//self::s2sOg_check();

    	if(!self::s2sOg_check()) return;

    	self::ogPrepareDefault();

    	self::ogPrepareArticle();

    	self::ogPrepareJevents();

    	self::ogPrepareDesc();

    	//TODO
    	//self::killMeta();
    	
    	self::ogWriteMeta();

    	self::tcardWriteMeta();




    }

    public static function s2sOg_check(){
    	$params = self::$s2s_obj->params;
    	$context = self::$s2s_obj->context;
    	$ismodule = self::$s2s_obj->ismodule;

    	if($ismodule){
    		PlgContentSocial2s::updateS2sOgDebug('OG Check context', 'NOT passed: IS A MODULE','danger', 'check');
    		return false;
    	}

    	//CONTEXT check
		if(		$context == 'com_k2.item' 
			||  $context == 'com_content.article'
		//	||  $context == 'com_content.featured'
			||  $context == 'com_virtuemart.productdetails' 
			||  $context == 'com_jevents' 
			||  $context == 'com_jevents.' 

		){
			PlgContentSocial2s::updateS2sOgDebug('Check context', 'Passed: '.$context,'success', 'check');
		}else{
			//OTHERS default data
			if($params->get('opengraph_default_data','0')){
				PlgContentSocial2s::updateS2sOgDebug('OG Check context', 'Passed: Default data','success', 'check');

			}else{
				PlgContentSocial2s::updateS2sOgDebug('OG Check context', 'NOT passed: '.$context,'danger', 'check');
				return false;
			}
		}

    	$s2s_og = $params->get('opengraph', 1);
    	$s2s_og_k2 = $params->get('s2s_og_k2', 0);
    	$twitter_cards = $params->get('twitter_cards', 0);
		

		//K2 check
		if($context == 'com_k2.item' && $s2s_og_k2==1){
			return false;
		}

		//PARAM check ON
		if(!$s2s_og && !$twitter_cards){
			PlgContentSocial2s::updateS2sOgDebug('Check ON', 'OpenGraph deactivated','success', 'check');
			return false;
		}

		return true;

    }

    /**
     * [ogPrepareDefault description]
     * @return [type] [description]
     */
	public static function ogPrepareDefault(){

		$context = self::$s2s_obj->context;

		//CONTEXT check
		if(		$context == 'com_k2.itemlist' 
			||  $context == 'com_content.category'
			||  $context == 'com_content.featured'
			||  $context == 'com_virtuemart.category'

		){

			$params = self::$s2s_obj->params;

			if(!$params->get('opengraph_default_data','0')) return;

			$app = JFactory::getApplication();

			//TITLE
			if($params->get('opengraph_default_data','0') == '1'){
				$menu = $app->getMenu();
				self::$s2s_og->title = $menu->getActive()->title;
				PlgContentSocial2s::updateS2sOgDebug('Og data default', 'Default title menu: '.self::$s2s_og->title ,'danger', 'share-alt');
			}else{
				self::$s2s_og->title = $app->getCfg('sitename');
				PlgContentSocial2s::updateS2sOgDebug('Og data default', 'Default title sitename: '.self::$s2s_og->title ,'danger', 'share-alt');
			}
		}else{
			return;
		}




	}

	//ARTICLE
	//K2
    /**
     * [ogPrepareArticle com_content.article]
     * @return [type] [description]
     */
	public static function ogPrepareArticle(){

		//ARTICLE + K2 item
		//
		$s2s_obj = self::$s2s_obj;
		
		$valida = false;
		if($s2s_obj->context == 'com_content.article' || $s2s_obj->context == 'com_k2.item' || $s2s_obj->context == 'com_virtuemart.productdetails') $valida = true;

		if(!$valida) return;


		//modify title (if were necessary)
		self::$s2s_og->title = $s2s_obj->title;

		//modify description
		self::$s2s_og->description = $s2s_obj->description.'...';

		//images
		self::$s2s_og->images = self::$s2s_obj->images;

		//url
		self::$s2s_og->absolute_url = self::$s2s_obj->absolute_url;
		self::$s2s_og->url = self::$s2s_obj->url;

		PlgContentSocial2s::updateS2sOgDebug('Og Article-item', 'Title: '.self::$s2s_og->title ,'success', 'share-alt');
		PlgContentSocial2s::updateS2sOgDebug('Og Article-item', 'Description: '.self::$s2s_og->description ,'success', 'share-alt');



		$implosion = implode("<br>",self::$s2s_og->images);
		PlgContentSocial2s::updateS2sOgDebug('Og Article-item', 'Images: '.$implosion ,'success', 'share-alt');

	}

	public static function ogPrepareJevents(){

		$s2s_obj = self::$s2s_obj;
		$context = self::$s2s_obj->context;

		//CONTEXT check
		if(	$context == 'com_jevents' ||  $context == 'com_jevents.'){

			//modify title (if were necessary)
			self::$s2s_og->title = $s2s_obj->title;
			self::$s2s_og->description = $s2s_obj->description.'...';
			self::$s2s_og->images = self::$s2s_obj->images;
			self::$s2s_og->absolute_url = self::$s2s_obj->absolute_url;
			self::$s2s_og->url = self::$s2s_obj->url;

			PlgContentSocial2s::updateS2sOgDebug('Og jevents-tit', 'Title: '.self::$s2s_og->title ,'success', 'share-alt');
			PlgContentSocial2s::updateS2sOgDebug('Og jevents-desc', 'Description: '.self::$s2s_og->description ,'success', 'share-alt');

			if(self::$s2s_og->images){
				$implosion = implode("<br>",self::$s2s_og->images);
				PlgContentSocial2s::updateS2sOgDebug('Og jevents-img', 'Images: '.$implosion ,'success', 'share-alt');
			}

		}else{
			return;
		}

	}



	/**
	 * [ogPrepareMeta prepare META]
	 * @return [type] [meta]
	 */
	public static function ogPrepareDesc(){
		$params = self::$s2s_obj->params;

		if(!$params->get('opengraph_metadescription','0')) return;

		//always
		if($params->get('opengraph_metadescription','0') == '1'){
			self::$s2s_og->description = self::$s2s_obj->metadescription;
			PlgContentSocial2s::updateS2sOgDebug('OG Use meta description', ' Always: '.self::$s2s_obj->metadescription ,'danger', 'share-alt');
		}else{

			//last chance
			if(self::$s2s_og->description == ''){
				self::$s2s_og->description = self::$s2s_og->metadescription;
				PlgContentSocial2s::updateS2sOgDebug('OG Use meta description', 'Last chance: '.self::$s2s_og->description ,'danger', 'share-alt');
			}
		}
	}


	/**
	 * [og_images_size returns size ]
	 * @return [type] [meta]
	 */
	private static function og_images_meta($img,$type='og'){
	
		//TO DO external function
		//detect external url
		if (substr($img, 0, 7) == "http://"){
			$final_img_to_size = $img;
			$final_img = $img;
		}elseif (substr($img, 0, 8) == "https://"){
			$final_img_to_size = $img;
			$final_img = $img;
		}else{
			$final_img_to_size = JPATH_SITE.'/'.$img;
			$final_img =  JURI::base().$img;
		}


		//check 
		$encoded_img = urlencode($final_img);
		$og_img_size_tag = '';

		if($type=='og'){
			if($img != ""){ 
					//image
					$og_img_size_tag .= '<meta property="og:image" content="'.$final_img.'">';

					//size
					$image_w = getimagesize($final_img_to_size);
					$og_img_size_tag .= '<meta property="og:image:width" content="'.$image_w[0].'" />';
					$og_img_size_tag .= '<meta property="og:image:height" content="'.$image_w[1].'" />';
			}

			PlgContentSocial2s::updateS2sOgDebug('Og Final image','<img src="'.$final_img.'" width="80px"/>  '.$final_img.'<br/> width: '.$image_w[0].' <br/> height: '.$image_w[1],'success', 'share-alt');
		}

		if($type=='tcard'){	

			if($img != ""){ 
					//image
					$og_img_size_tag .= '<meta property="twitter:image" content="'.$final_img.'">';
			}
			PlgContentSocial2s::updateS2sOgDebug('Twitter card Final image','<img src="'.$final_img.'" width="80px"/>  '.$final_img,'success', 'share-alt');
		}


		return $og_img_size_tag;
	}

	public static function killMeta(){

		/*TODO*/

	}


	/**
	 * [ogWriteMeta create META]
	 * @return [type] [meta]
	 */
	public static function ogWriteMeta(){
		$params = self::$s2s_obj->params;
		$s2s_obj = self::$s2s_obj;
		$s2s_og = self::$s2s_og;

		$s2s_og_on = $params->get('opengraph', 1);
		if(!$s2s_og_on){
			PlgContentSocial2s::updateS2sOgDebug('Check OG on','Deactivated: og meta won´t be written','warning', 'share-alt');
			return;
		}

		//title
		$opengraph    = '<meta property="og:title" content="'.htmlspecialchars($s2s_og->title).'">';

		//description
		$opengraph .= '<meta property="og:description" content="'.htmlspecialchars($s2s_og->description).'">';

		//images
		if(self::$s2s_obj->images){
			foreach (self::$s2s_obj->images as $key => $image) {
				$opengraph .= self::og_images_meta($image);
			}
		}


		//sitename
		$config = JFactory::getConfig();
		$opengraph .= '<meta property="og:site_name" content="'.$config->get('sitename').'" />';

		//url
        $opengraph .= '<meta property="og:url"  content="'. $s2s_og->absolute_url.'" />';

        //type
        if($s2s_obj->context == 'com_virtuemart.productdetails'){
        	//VIRTUEMART
        	$opengraph .= '<meta property="og:type"  content="product" />';	
        }else{
        	//WEBSITE
        	$opengraph .= '<meta property="og:type"  content="website" />';
        }
		

		PlgContentSocial2s::updateS2sOgDebug('Og metas',htmlentities($opengraph) ,'success', 'share-alt');

		//app_id
		$opengraph .= '<meta property="fb:app_id" content="514279921989553" />';

		$document = JFactory::getDocument();
		$document->addCustomTag($opengraph);

	}


	/**
	 * [tcardWriteMeta create META]
	 * @return [type] [meta]
	 */
	public static function tcardWriteMeta(){
		$params = self::$s2s_obj->params;
		$s2s_og = self::$s2s_og;

		//CHECK ON
		$t_cards_on = $params->get('twitter_cards', 0);
		if(!$t_cards_on){
			PlgContentSocial2s::updateS2sOgDebug('Check Twitter card on','Deactivated: og meta won´t be written','warning', 'share-alt');
			return;
		}

		//USER CHECK
		$twitter_user = $params->get('twitter_user', '');
		if($twitter_user == ""){
			PlgContentSocial2s::updateS2sOgDebug('Check Twitter user','Empty: og meta won´t be written','warning', 'share-alt');
			return;
		}
		//quito la @
		if (strpos($twitter_user,'@') !== false) {
		    $twitter_user = ltrim($twitter_user, '@');
		}

		//WRITE TWITTER CARD
		$tw_cards='';
		if($params->get('twitter_cards_summary', 0)){
			$tw_cards    = '<meta property="twitter:card" content="summary_large_image"/>';
		}else{
			$tw_cards    = '<meta property="twitter:card" content="summary"/>';
		}

		//user
		$tw_cards    .= '<meta property="twitter:site" content="@'.$twitter_user.'"/>';

		//title
		$tw_cards    .= '<meta property="twitter:title" content="'.htmlspecialchars($s2s_og->title).'">';

		//description
		$tw_cards .= '<meta property="twitter:description" content="'.htmlspecialchars($s2s_og->description).'">';


		//images just first
		if(isset(self::$s2s_obj->images[0])){
			if(self::$s2s_obj->images[0] != ''){
				$tw_cards .= self::og_images_meta(self::$s2s_obj->images[0], 'tcard');
			}
		}

		PlgContentSocial2s::updateS2sOgDebug('Twitter card metas',htmlentities($tw_cards) ,'success', 'share-alt');
		
		$document = JFactory::getDocument();
		$document->addCustomTag($tw_cards);
	}



	//RESTO DE FUNCIONES








}
























/*

	//OPENGRAPH
	$s2s_og = $params->get('opengraph', 1);
	$s2s_og_k2 = $params->get('s2s_og_k2', 0);

	//K2 check
	$check_k2_og = true;
	if($context == 'k2.item' && $s2s_og_k2==1){
		$check_k2_og = false;
	}
	
	if($context == 'k2.item' || $context=='com_content.article' || $context=='com_virtuemart.productdetails' || $context=='jevents' || $context == 'com_content.featured'){
		if($s2s_og && $check_k2_og){

			$opengraph = '';

			//VIRTUEMART
			if($context=='com_virtuemart.productdetails'){
				
				//titulo	
				$opengraph    = '<meta property="og:title" content="'.htmlspecialchars($article->product_name).'"/>' ."\n";
				self::$s2s_og_debug[] = '<strong>og:title:</strong> '.htmlspecialchars($article->product_name);

				//function returns array
				//array
				$ogdata =  $this->og_images_virtuemart($params,$article,$context,'og:image');
				//data

				//Type
				$opengraph .= '<meta property="og:type"  content="product" />';

				//amount
				if($params->get('s2s_virtuemart_sale_price_amount','0')){
					if($article->prices['salesPrice']){
						$opengraph .= '<meta property="product:sale_price:amount" content="'.$article->prices['salesPrice'].'" /> ';
					}
				}
				//currency
				if($params->get('s2s_virtuemart_sale_price_currency','0')){
					
					if(isset($article->prices['product_currency'])){

						$currency_model = VmModel::getModel('currency');
						$displayCurrency = $currency_model->getCurrency($article->prices['product_currency']);
						$opengraph .= '<meta property="product:sale_price:currency" content="'.$displayCurrency->currency_code_3.'" /> ';
					}
				}

				//starts
				if($params->get('s2s_virtuemart_sale_price_dates_start','0')){
					if(isset($article->prices['product_price_publish_up']) && $article->prices['product_price_publish_up']!="0000-00-00 00:00:00"){
						$opengraph .= '<meta property="product:sale_price_dates:start" content="'.$article->prices['product_price_publish_up'].'" /> ';
					}
				}

				//ends
				if($params->get('s2s_virtuemart_sale_price_dates_end','0')){
					if(isset($article->prices['product_price_publish_down']) && $article->prices['product_price_publish_down']!="0000-00-00 00:00:00"){
						$opengraph .= '<meta property="product:sale_price_dates:end" content="'.$article->prices['product_price_publish_down'].'" /> ';
					}
				}

				//todo
					/*
					<meta property="product:original_price:amount"   content="Sample Original Price: " /> 
					<meta property="product:original_price:currency" content="Sample Original Price: " /> 
					<meta property="product:pretax_price:amount"     content="Sample Pre-tax Price: " /> 
					<meta property="product:pretax_price:currency"   content="Sample Pre-tax Price: " /> 
					<meta property="product:price:amount"            content="Sample Price: " /> 
					<meta property="product:price:currency"          content="Sample Price: " /> 
					<meta property="product:shipping_cost:amount"    content="Sample Shipping Cost: " /> 
					<meta property="product:shipping_cost:currency"  content="Sample Shipping Cost: " /> 
					<meta property="product:weight:value"            content="Sample Weight: Value" /> 
					<meta property="product:weight:units"            content="Sample Weight: Units" /> 
					<meta property="product:shipping_weight:value"   content="Sample Shipping Weight: Value" /> 
					<meta property="product:shipping_weight:units"   content="Sample Shipping Weight: Units" /> 

					<meta property="product:price:amount" content="10700">
					<meta property="product:price:currency" content="USD">
					<meta property="product:shipping_weight:value" content="1">
					<meta property="product:shipping_weight:units" content="lb">
					<meta property="product:sale_price:amount" content="6995">
					<meta property="product:sale_price:currency" content="USD">
					<meta property="product:brand" content="Rolex">
					<meta property="product:mfr_part_no" content="116261">
					<meta property="product:target_gender" content="male">
					<meta property="product:age_group" content="adult">
					<meta property="product:condition" content="new">
					<meta property="product:availability" content="in stock">
					<meta property="og:updated_time" content="2013-12-16T14:18:00-05:00">
					*/
/*
				//DESCRIPCION
				//$descripcion = implode(' ', array_slice(explode(' ', strip_tags($article->product_desc)), 0, 70));
				$description = $s2s_social->description_formatted($article->product_desc);
				$opengraph .= '<meta property="og:description" content="'.htmlspecialchars($description).'..." />';

			}elseif($context == 'k2.item'){ 

				//K2
				$opengraph    = '<meta property="og:title" content="'.htmlspecialchars($article->title).'"/>' ."\n";
								
				//IMAGES
					//array
					$ogdata =  $this->og_images($params,$article,$context,'og:image');



					//$ogdata['error'] + $ogdata['og'];
					self::$s2s_og_debug[] = '<strong>og:title:</strong> from k2: '.$article->title;
					//error TODO
					//v2


					//self::$s2s_og_debug[] = '<strong>og:title:</strong> '.$article->title;

					//data
					//$opengraph .= $ogdata['og'];
					//var_dump($ogdata['og']);

				//DESCRIPTION
				//true
				if($params->get('opengraph_metadescription', 0)){
					//check menu desc
					if($article->params->get('menu-meta_description')==''){
						//check article desc
						if($article->metadesc!=''){
							$description = $s2s_social->description_formatted($article->metadesc);
						}else{
							$description = $s2s_social->description_formatted($article->introtext);
						}
					}else{
						$description = $s2s_social->description_formatted($article->params->get('menu-meta_description'));
					}

				}else{
					$description = $s2s_social->description_formatted($article->introtext);
				}
				$opengraph .= '<meta property="og:description" content="'.htmlspecialchars($description).'..." />';

				self::$s2s_og_debug[] = '<strong>og:description:</strong> '.htmlspecialchars($description);
				$opengraph .= '<meta property="og:type"  content="website" />';

				//DESCRIPTION
				//$descripTion = implode(' ', array_slice(explode(' ', strip_tags($article->introtext,'<br>')), 0, 70));
				//$description = $s2s_social->description_formatted($article->introtext);
				//$opengraph .= '<meta property="og:description" content="'.htmlspecialchars($description).'..." />';

				//self::$s2s_og_debug[] = '<strong>og:description:</strong> '.htmlspecialchars($description);

			//JEVENTS
			}elseif($context =='jevents'){

				$opengraph    = '<meta property="og:title" content="'.htmlspecialchars($article->_summary).'"/>' ."\n";

				$ogdata =  $this->og_images($params,$article,$context,'og:image');
				//var_dump($ogdata);

				//URL
				$uri = JFactory::getURI();
				$absolute_url = $uri->toString();
				
				$opengraph .= '<meta property="og:url"  content="'. $absolute_url.'" />';
				self::$s2s_og_debug[] = '<strong>og:url:</strong> '.$absolute_url;

				self::$s2s_og_debug[] = '<strong>og:title:</strong> '.htmlspecialchars($article->title);


				//var_dump($ogdata['og']);

				$opengraph .= '<meta property="og:type"  content="website" />';
				
				//DESCRIPTION
				//true
				if($params->get('opengraph_metadescription', 0)){
					//check menu desc
					if($article->params->get('menu-meta_description')==''){
						//check article desc
						if($article->metadesc!=''){
							$description = $s2s_social->description_formatted($article->metadesc);
						}else{
							$description = $s2s_social->description_formatted($article->introtext);
						}
					}else{
						$description = $s2s_social->description_formatted($article->params->get('menu-meta_description'));
					}

				}else{
					$description = $s2s_social->description_formatted($article->introtext);
				}

				$opengraph .= '<meta property="og:description" content="'.htmlspecialchars($description).'..." />';


				self::$s2s_og_debug[] = '<strong>og:description:</strong> '.htmlspecialchars($description);

			//FEATURED
			}elseif($context =='com_content.featured'){

				if($params->get('opengraph_default_categories','0')){
					
					$app = JFactory::getApplication();
					
					//TITLE
					if($params->get('opengraph_def_title_based','0')){
						$menu = $app->getMenu();
						$menuname = $menu->getActive()->title;
					}else{
						$menuname = $app->getCfg('sitename');
					}

					$opengraph   .= '<meta property="og:title" content="'.htmlspecialchars($menuname).'"/>' ."\n";

					//IMAGE
					$ogdata =  $this->og_images_featured($params,'og:image');

					//URL
					$uri = JFactory::getURI();
					$absolute_url = $uri->toString();
					
					$opengraph .= '<meta property="og:url"  content="'. $absolute_url.'" />';
					self::$s2s_og_debug[] = '<strong>og:url:</strong> '.$absolute_url;

					//Type
					$opengraph .= '<meta property="og:type"  content="website" />';

					//description			
					$MetaDesc = $app->getCfg('MetaDesc');

					$opengraph .= '<meta property="og:description" content="'.htmlspecialchars($MetaDesc).'..." />';
					self::$s2s_og_debug[] = '<strong>og:description:</strong> '.htmlspecialchars($MetaDesc);
				}else{
					$ogdata =  $this->og_images_featured($params,'og:image');
					self::$s2s_og_debug[] = '<strong>og in featured deactivated</strong> ';
				}

			}else{
				//ARTICLE
				$opengraph    = '<meta property="og:title" content="'.htmlspecialchars($article->title).'"/>' ."\n";

				//IMAGES
					//array
					$ogdata =  $this->og_images($params,$article,$context,'og:image');

					//$ogdata['error'] + $ogdata['og'];


				//URL
				$uri = JFactory::getURI();
				$absolute_url = $uri->toString();
				
				$opengraph .= '<meta property="og:url"  content="'. $absolute_url.'" />';
				self::$s2s_og_debug[] = '<strong>og:url:</strong> '.$absolute_url;

				self::$s2s_og_debug[] = '<strong>og:title:</strong> '.htmlspecialchars($article->title);


				//var_dump($ogdata['og']);

				$opengraph .= '<meta property="og:type"  content="website" />';
				
				//DESCRIPTION
				//true
				if($params->get('opengraph_metadescription', 0)){
					//check menu desc
					if($article->params->get('menu-meta_description')==''){
						//check article desc
						if($article->metadesc!=''){
							$description = $s2s_social->description_formatted($article->metadesc);
						}else{
							$description = $s2s_social->description_formatted($article->introtext);
						}
					}else{
						$description = $s2s_social->description_formatted($article->params->get('menu-meta_description'));
					}

				}else{
					$description = $s2s_social->description_formatted($article->introtext);
				}

				$opengraph .= '<meta property="og:description" content="'.htmlspecialchars($description).'..." />';


				self::$s2s_og_debug[] = '<strong>og:description:</strong> '.htmlspecialchars($description);
			}

			//data
			$opengraph .= $ogdata['og'];

			//SITE NAME
			$config = JFactory::getConfig();
			$opengraph .= '<meta property="og:site_name" content="'.$config->get('sitename').'" />';
			$opengraph .= '<meta property="fb:app_id" content="514279921989553" />';

			self::$s2s_og_debug[] = '<strong>og:site_name:</strong> '.$config->get( 'sitename' );

			$document->addCustomTag($opengraph);
		}
	}


	//TWITTER CARDS
	if($context=='com_content.article' || $context=='com_virtuemart.productdetails' || $context == 'k2.item'){

		if($params->get('twitter_cards')){
			$twitter_user = $params->get('twitter_user', '');
			if($twitter_user == ""){
				$s2s_debug_msg .= '<a class="alert ">no twitter user defined</a>';
			}

			//quito la @
			if (strpos($twitter_user,'@') !== false) {
			    $twitter_user = ltrim($twitter_user, '@');
			}


			if($params->get('twitter_cards_summary', 0)){
				$tw_cards    = '<meta property="twitter:card" content="summary_large_image"/>';
			}else{
				$tw_cards    = '<meta property="twitter:card" content="summary"/>';
			}


			$tw_cards    .= '<meta property="twitter:site" content="@'.$twitter_user.'"/>';

			self::$s2s_og_debug[] = '<strong>twitter user:</strong> @'.$twitter_user;

			//VIRTUEMART
			if($context=='com_virtuemart.productdetails'){
				$tw_cards    .= '<meta property="twitter:title" content="'.$article->product_name.'"/>';

				//images
					$ogdata =  $this->og_images_virtuemart($params,$article,$context,'twitter:image');
					//$ogdata =  $this->og_images_virtuemart($params,$article,$context,'twitter:image:src');

					//error TODO
					//if($social2s_debug){
					//	$s2s_debug_msg .= '<a class="label">twitter:image= '.$ogdata['error'].'</a>';
					//}
					
					//data
					$tw_cards .= $ogdata['og'];
				//description
				$tw_cards    .= '<meta property="twitter:description" content="'.$s2s_social->description_formatted($article->product_desc).'"/>';

			//ARTICLE
			}else{
				$tw_cards    .= '<meta property="twitter:title" content="'.$article->title.'"/>';

				self::$s2s_og_debug[] = '<strong>twitter title:</strong> '.$article->title;
				//IMAGES
					//array
					$ogdata =  $this->og_images($params,$article,$context,'twitter:image');

					$tw_cards    .= '<meta property="twitter:image:alt" content="'.$article->title.'"/>';
					//$ogdata =  $this->og_images($params,$article,$context,'twitter:image:src');
					//$ogdata['error'] + $ogdata['og'];


					//data
					$tw_cards .= $ogdata['og'];
					//var_dump($ogdata['og']);
				//K2
				if($context == 'k2.item'){
					$tw_cards  .= '<meta property="twitter:description" content="'.$s2s_social->description_formatted($article->introtext).'..."/>';
				}else{
					$tw_cards  .= '<meta property="twitter:description" content="'.htmlspecialchars($description).'..."/>';
				}
				

			}
			$document->addCustomTag($tw_cards);
		}
	}
*/
?>