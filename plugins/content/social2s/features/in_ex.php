<?php
class s2s_checkvisibility {

	static function check_component($component, $view){

		//first bifurcation: component and view
		if($component=='com_content'){
		    if($view == 'article' || $view == 'category' || $view == 'featured'){
		    	return true;
		    }
		}elseif ($component=='com_virtuemart') {
			return true;
		}elseif ($component=='com_k2') {
			return true;
		}elseif ($component=='com_k') {
			return true;
		}else{
			PlgContentSocial2s::updateS2sDebug('Check component', $component.' not supported', 'danger', 'toggle-on');
			return false;
		}
		return false;
	} 

	static public function check_component_support(){
		$s2s_obj = PlgContentSocial2s::$s2s_obj;
		$context = $s2s_obj->context;
		$params = $s2s_obj->params;

		

		return 	true;
	}

	static function check_context(){
		$s2s_obj = PlgContentSocial2s::$s2s_obj;
		$context = $s2s_obj->context;
		//ALL SUPPORTED CONTEXTS
		if( $context=='com_content.category' || 
			$context=='com_content.featured' ||  
			$context=='com_content.article'  || 

			$context=='com_virtuemart.productdetails')
		{ 

			PlgContentSocial2s::updateS2sDebug('Check context', $context.' PASSED', 'success', 'toggle-on');
			return true;
		}else{
			PlgContentSocial2s::updateS2sDebug('Check context', $context.' NOT passed', 'danger', 'toggle-on');
			return false;
		}
	}



	static function check_on(){

		$s2s_obj = PlgContentSocial2s::$s2s_obj;

		$check_view = true;

		$view = $s2s_obj->view;
		$params = $s2s_obj->params;

		$art_on = $params->get('s2s_display_art_view','1');
		$cat_on = $params->get('s2s_display_cat_view','1');
		$feat_on = $params->get('s2s_featured_inc_exc','1');
		$s2s_takefromarticle = $params->get('s2s_takefromarticle','1');

   	
		if($s2s_takefromarticle){
			$cat_on=$art_on;
		}


	    //ACTIVE OR NOT
	    //com_content
	    if($view == 'article'){
	    	if(!$art_on) {
	    		PlgContentSocial2s::updateS2sDebug('Check activation', $view.' NOT passed', 'danger', 'toggle-on');
	    		$check_view = false;
	    	}
	    }elseif($view == 'category'){
	    	if(!$cat_on) {
	    		PlgContentSocial2s::updateS2sDebug('Check activation', $view.' NOT passed', 'danger', 'toggle-on');
	    		$check_view = false;
	    	}
	    }elseif($view == 'featured'){
	    	if(!$feat_on || !$cat_on) {
	    		PlgContentSocial2s::updateS2sDebug('Check activation', $view.' NOT passed', 'danger', 'toggle-on');
	    		$check_view = false;
	    	}
	    }

	    //VIRTUEMART SUPPORT ACTIVE
	    
	    return $check_view;
	}


	static function check_on_k2(){

		$s2s_obj = PlgContentSocial2s::$s2s_obj;

		$check_view = true;

		$view = $s2s_obj->view;
		$params = $s2s_obj->params;


		$art_on = $params->get('s2s_k2_item','1');
		$cat_on = $params->get('s2s_k2_itemlist','1');
		$k2_module = $params->get('s2s_k2_module','1');
		$s2s_takefromarticle = $params->get('s2s_takefromarticle','1');

   	
		if($s2s_takefromarticle){
			$cat_on=$art_on;
		}

	    //ACTIVE OR NOT
	    //com_content
	    if($view == 'item'){
	    	if(!$art_on) {
	    		PlgContentSocial2s::updateS2sDebug('Check activation', $view.' NOT passed', 'danger', 'toggle-on');
	    		$check_view = false;
	    	}
	    }elseif($view == 'itemlist'){
	    	if(!$cat_on) {
	    		PlgContentSocial2s::updateS2sDebug('Check activation', $view.' NOT passed', 'danger', 'toggle-on');
	    		$check_view = false;
	    	}
	    }

	    return $check_view;
	}





	static function check_position(){
		
		$s2s_obj = PlgContentSocial2s::$s2s_obj;

	
		//check same
		$s2s_pos = 0;
		//$s2s_obj = PlgContentSocial2s::$s2s_obj;
		$context = $s2s_obj->context;

		$s2s_pos = $s2s_obj->params->get('s2s_art_pos',1);

		if($context=='com_k2.itemlist' || 
			$context=='com_content.category' || 
			$context=='com_content.featured')
		{
			//check activated
			$s2s_takefromarticle = $s2s_obj->params->get('s2s_takefromarticle',1);
			$s2s_display_cat_view = $s2s_obj->params->get('s2s_display_art_view',0);


			if($s2s_takefromarticle){
				$s2s_display_cat_view = $s2s_obj->params->get('s2s_display_art_view',0);
			}else{
				$s2s_display_cat_view = $s2s_obj->params->get('s2s_display_cat_view',0);
			}


			$s2s_pos = $s2s_obj->params->get('s2s_cat_pos',0);
			//check SAME
			if($s2s_takefromarticle){
	    		$s2s_pos = $s2s_obj->params->get('s2s_art_pos',0);
	    	}
		}

		return $s2s_pos;
	}

	static function check_module($jparams){
		
		PlgContentSocial2s::$s2s_obj->ismodule = false;

		$s2s_obj = PlgContentSocial2s::$s2s_obj;

		$check_view = true;
		$s2s_display_module = $s2s_obj->params->get('s2s_display_module',0);

		$formData = new JRegistry($jparams);
		//old
		//$data = $formData->get('data');
		$data = $formData->get('ordering');

		//check if is module
		if($data !== null){

			//IS MODULE!
			PlgContentSocial2s::$s2s_obj->view = 'category';

			PlgContentSocial2s::$s2s_obj->ismodule = true;
			
			if($s2s_display_module==0){
				PlgContentSocial2s::updateS2sDebug('Check module', 'NOT passed', 'warning', 'toggle-on');
				$check_view = false;
			}else{
				PlgContentSocial2s::updateS2sDebug('Check module', 'Passed', 'success', 'toggle-on');
			}
		}

		return $check_view;
	}


	/*CHECK PAGES*/
	static function checkPages_v4($pluginParams, $row, $context) {



		//VIRTUEMART 
		if($context == 'com_virtuemart.productdetails'){
			PlgContentSocial2s::updateS2sDebug('check activation passed:','virtuemart.productdetails has free pass :)');
			return true;
		}

		//NO CATID
		if(!isset($row->catid)){
			PlgContentSocial2s::updateS2sDebug('Check CAT ID', 'NOT passed', 'danger', 'toggle-on');
			return false;
		}


		$category_id_v3 = $row->catid;
		$article_id_v3 = $row->id;

		$exists = false;
		$existsa = false;

		//K2
		if($context == 'com_k2.itemlist' || $context == 'com_k2.item'){
			//categories
			$catids = $pluginParams->get('s2s_k2_categories','');
			$cat_exc_inc = $pluginParams->get('s2s_k2_cat_inc_exc','1');

			//articles
			$article_ids = explode(',',$pluginParams->get('s2s_k2_article_ids',''));
			$article_exc_inc = $pluginParams->get('s2s_k2_article_inc_exc','1');

		}else{
			//categories
			$catids = $pluginParams->get('categories','');
			$cat_exc_inc = $pluginParams->get('cat_inc_exc','1');

			//articles
			$article_ids = $pluginParams->get('article_ids','');
			$article_exc_inc = $pluginParams->get('article_inc_exc','1');
		}

		if(gettype($catids)=="string") $catids = array("-1");
		//INCLUDE
		if($cat_exc_inc=="1" && !$exists){

			if (is_array($catids)) {
				$exists = (in_array($category_id_v3,$catids)) ? true:false;
			}
			if(empty($catids)){
				$exists = true;
			}elseif($catids[0]=="0" || $catids[0]=="-1"){
				$exists = true;
			}
		//EXCLUDE
		}else if($cat_exc_inc=="0" && !$exists){
			//INclude
			if (is_array($catids)) {
				$exists = (in_array($category_id_v3,$catids)) ? false:true;
			}
			//all selected
			if(empty($catids) || $catids[0]=="-1"){
				$exists = true;
			}elseif($catids[0]=="0"){
				$exists = false;
			}
			if($exists) PlgContentSocial2s::updateS2sDebug('Check exclude category:', 'PASSED','success', 'toggle-on');
			if(!$exists) PlgContentSocial2s::updateS2sDebug('Check exclude category:', 'NOT PASSED','danger', 'toggle-on');
		}



		//ARTICLES
		
		if($article_exc_inc=="1" && !$exists){
			//INclude
			if (is_array($article_ids)) {
				$exists = (in_array($article_id_v3,$article_ids)) ? true:false;
			}

		}else if($article_exc_inc=="0" && $exists){

			//INclude
			if (is_array($article_ids)) {
				$exists = (in_array($article_id_v3,$article_ids)) ? false:true;
			}else {
				//not sure
				$exists = true;
			}
		}

		if($exists) PlgContentSocial2s::updateS2sDebug('Check exclude article:', 'PASSED','success', 'toggle-on');
		if(!$exists) PlgContentSocial2s::updateS2sDebug('Check exclude article:', 'NOT PASSED','danger', 'toggle-on');

		return $exists;
	}







}





?>