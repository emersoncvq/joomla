<?php

	/*v2.0.6*/
	/*Call To Action*/
	$s2s_cta_close = true;
	if(!isset($_COOKIE['s2s_cta_close']) || $_COOKIE['s2s_cta_close'] == null || $_COOKIE['s2s_cta_close'] != "2"){
		$s2s_cta_close = false;
	}

	$cta_mobile = $params->get('s2s_cta_active_in_mobile',0);

	if($jmobile && !$cta_mobile){
		return;
	}
	
	if($params->get('s2s_cta_active',0) && $s2s_cta_close == false){
		
		$media_path = JURI::root( true ).'/media/plg_social2s';
		//minify
		$ctacss = 'cta_default';
		if($params->get('social2s_minify_debug', 0) == 0){
			$document->addStyleSheet($media_path.'/css/cta/'.$ctacss.'.min.css', 'text/css');
		}else{
			$document->addStyleSheet($media_path.'/css/cta/'.$ctacss.'.css', 'text/css');
		}

		//size
		$s2s_cta_size = $params->get('s2s_cta_size','s2s_cta_size_default');
		$s2s_cta_pos = $params->get('s2s_cta_pos','left');

		$html_hasta_ahora = $html;
		$html .= '<div class="s2s_cta_wrapper s2s_supra_contenedor '.$s2s_cta_size.' '.$style.' s2s_balloon_top cta_pos_'.$s2s_cta_pos.'">';

			if(isset($_COOKIE['s2s_cta_close'])){
				if($_COOKIE['s2s_cta_close'] == "1"){
					$html .= '<div class="s2s_cta_close"><span class="fa fa-eye-slash"></span></div>';
				}else{
					$html .= '<div class="s2s_cta_close"><span class="fa fa-close"></span></div>';
				}
			}else{
				$html .= '<div class="s2s_cta_close"><span class="fa fa-close"></span></div>';
			}
			

			//it's time to call to action!
			$cta_maintext = $params->get('s2s_cta_maintext','');
			$html .= '<div class="s2s_cta_maintext">';
			$html .= $cta_maintext;
			
			$html .= '</div>';
			
			if($context=='com_content.article'){
				$html .= $html_hasta_ahora;
			}
			//$html .= '</div>';
			//powered by
			$html .= '<div class="s2s_cta_powered"><small>powered by <a href="http://dibuxo.com/joomlacms/social-2s/social-2s-2" target="_blank">social2s</a></small></div>';
		$html .= '</div>';
	}

?>