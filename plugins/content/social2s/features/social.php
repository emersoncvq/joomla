<?php
defined('_JEXEC') or die;


class s2s_social {

	public static $s2s_obj;


	//check loaded scripts
	public static $s2s_loaded_scripts;

	public function __construct()
    {
    	self::$s2s_obj = PlgContentSocial2s::$s2s_obj;
    }

	/*TWITTER*/
	public function getTwitter(
		/*$button_size, 
		$buttons, 
		$cookie_button, 
		$title, 
		$full_link, 
		$idioma_twitter, 
		$texts,*/
		$afterplus
	){
		
		//COMMON
		$params = self::$s2s_obj->params;
		$styles = self::$s2s_obj->styles;

		$full_link = self::$s2s_obj->full_link;
		$title = self::$s2s_obj->title;
		$button_size = $styles->size;
		$buttons = s2s_tmpl::$s2s_buttons;
		$cookie_button = self::$s2s_obj->cookie_button;
		$idioma_twitter = self::$s2s_obj->lang_twitter;
		$texts =  s2s_tmpl::getTexts($styles->text);

		$twitter_user = $params->get('twitter_user', '');
		$twitter_via = $params->get('twitter_via', 0);
		$twitter_counter = $params->get('twitter_count', 0);
		$twitter_follow = $params->get('twitter_follow', 0);
		$twitter_follow_counter = $params->get('twitter_follow_count', 0);

		$s2s_twitter_utf8 = $params->get('s2s_twitter_link_utf8', '0');
		$s2s_twitter_text_utf8 = $params->get('s2s_twitter_text_utf8', '0');

		$full_link_encoded = htmlspecialchars($full_link);
		$full_link_encoded = urlencode($full_link_encoded);
		$full_link_encoded = str_replace('&amp;', '%26', $full_link);

		//var_dump('<br>Twitter: '.$full_link_encoded);
		
		if($s2s_twitter_utf8=='0'){
			$full_link_encoded = str_replace('&amp;', '%26', $full_link);
		}elseif($s2s_twitter_utf8=='1'){
			$full_link_encoded = urlencode($full_link_encoded);
		}elseif($s2s_twitter_utf8=='2'){
			$full_link_encoded = str_replace('&amp;', '%26', $full_link);
		}elseif($s2s_twitter_utf8=='3'){
			$full_link_encoded = htmlspecialchars($full_link);
		}

		if($s2s_twitter_text_utf8=='0'){
			$text_encoded = $title;
		}elseif($s2s_twitter_text_utf8=='1'){
			$text_encoded = $title;
		}elseif($s2s_twitter_text_utf8=='2'){
			$text_encoded = rawurlencode($title);
		}elseif($s2s_twitter_text_utf8=='3'){
			$text_encoded = htmlspecialchars($title);
		}

		$html = '<div class="s2s_twitter s2s_btn '.$button_size.' '.$afterplus.'">
			<a class="s2s_icon">'.$buttons['twitter'].' </a>
			<div class="globo s2s_globo_closed s2s_twitter_iframe">'.$cookie_button.'
				<div class="s2s_flecha"></div>

				<a style="display:none" 
					href="https://twitter.com/share"
					data-text="'.$text_encoded.'" 
					data-url="'.$full_link_encoded.'" 
					class="twitter-share-button"';
					if($twitter_via){
						$html .=' data-via="'.$twitter_user.'"';
					}
					$html .= ' data-lang="'.$idioma_twitter.'" 
					data-size="large"';
					if($twitter_counter){
						$html .='>';
					}else{
						$html .= ' data-count="none">';
					}
					//$html .= 'data-count="none">';
					$html .= $texts['twitter'].'
				</a>';
				if($twitter_follow){
					if($twitter_follow_counter){
						$tw_follow_counter = 'data-show-count="true"';
					}else{
						$tw_follow_counter = 'data-show-count="false"';
					}
					$html .='<br>
					<a href="https://twitter.com/'.$twitter_user.'" class="twitter-follow-button" '.$tw_follow_counter.'></a>';
				}
			$html .='</div>
		</div>';

		return $html;
	}



	public function getTwitterOwn($afterplus){
			

		//COMMON
		$params = self::$s2s_obj->params;
		$styles = self::$s2s_obj->styles;

		$full_link = self::$s2s_obj->full_link;
		$title = self::$s2s_obj->title;
		$button_size = $styles->size;
		$buttons = s2s_tmpl::$s2s_buttons;
		$cookie_button = self::$s2s_obj->cookie_button;
		$idioma_twitter = self::$s2s_obj->lang_twitter;
		$texts =  s2s_tmpl::getTexts($styles->text);

		$twitter_user = $params->get('twitter_user', '');
		$twitter_via = $params->get('twitter_via', 0);
		$twitter_counter = $params->get('twitter_count', 0);
		$twitter_follow = $params->get('twitter_follow', 0);
		$twitter_follow_counter = $params->get('twitter_follow_count', 0);

		$s2s_twitter_utf8 = $params->get('s2s_twitter_link_utf8', '0');
		$s2s_twitter_text_utf8 = $params->get('s2s_twitter_text_utf8', '0');
/*
		$twitter_b_count = $params->get('twitter_b_count', 0);
		
		if($twitter_b_count){
			$twitter_t_count_html = '<span class="s2s_badge s2s_twitter_count_total">0</span>';
		}else{
			$twitter_t_count_html = '';
		}
*/
		$full_link_encoded = htmlspecialchars($full_link);
		$full_link_encoded = urlencode($full_link_encoded);
		$full_link_encoded = str_replace('&amp;', '%26', $full_link);
		
		if($s2s_twitter_utf8=='0'){
			$full_link_encoded = str_replace('&amp;', '%26', $full_link);
		}elseif($s2s_twitter_utf8=='1'){
			$full_link_encoded = urlencode($full_link_encoded);
		}elseif($s2s_twitter_utf8=='2'){
			$full_link_encoded = str_replace('&amp;', '%26', $full_link);
		}elseif($s2s_twitter_utf8=='3'){
			$full_link_encoded = htmlspecialchars($full_link);
		}

		if($s2s_twitter_text_utf8=='0'){
			$text_encoded = rawurlencode($title);
		}elseif($s2s_twitter_text_utf8=='1'){
			$text_encoded = $title;
		}elseif($s2s_twitter_text_utf8=='2'){
			$text_encoded = rawurlencode($title);
		}elseif($s2s_twitter_text_utf8=='3'){
			$text_encoded = htmlspecialchars($title);
		}




		$html = '<div class="s2s_twitter s2s_twitter_own s2s_btn '.$button_size.' '.$afterplus.'">
			<a class="s2s_icon">'.$buttons['twitter'].' </a>
			<div class="globo s2s_globo_closed s2s_twitter_iframe">
				<div class="s2s_flecha"></div>';
				

				$via = 	$twitter_via ? '&via='.$twitter_user : '';

				
				$lang_tweet = $this->getTwitterLanguage($idioma_twitter);

				$html .= '<div class="s2s_div_btn_twitter">';

					$html .= '<a class="s2s_a_btn s2s_btn_twitter" target="_blank" rel="noopener noreferrer" href="https://twitter.com/intent/tweet?text='.$text_encoded.'&url='.$full_link_encoded.$via.'">
						'.$lang_tweet.'</a>';

				//waiting for proper API :(
				/*$html .= '<div class="s2s_twitter_counter">
							<i></i>
							<u></u>
							<span></span>
						</div>';*/
				$html .= '</div>';

				if($twitter_follow){
					$html .= '<hr class="clearfix">';
					if($twitter_follow_counter){
						$tw_follow_counter = 'data-show-count="true"';
					}else{
						$tw_follow_counter = 'data-show-count="false"';
					}
					//$html .='<br>';
					$html .= '<a href="https://twitter.com/'.$twitter_user.'" class="twitter-follow-button" '.$tw_follow_counter.'></a>';
				}

			$html .='</div>
		</div>';

		return $html;
	}

	public function getTwitterLanguage($lang, $follow=false){

		$tweet_lang = array(
			'en'=>'<i class="s2sfo fo-twitter"></i> <span>Tweet</span>',
			'ar'=>'<span>غرِّد</span> <i class="s2sfo fo-twitter"></i>',
			'bn'=>'<i class="s2sfo fo-twitter"></i> <span>টুইট</span>',
			'cs'=>'<i class="s2sfo fo-twitter"></i> <span>Tweet</span>',
			'da'=>'<i class="s2sfo fo-twitter"></i> <span>Tweet</span>',
			'ca'=>'<i class="s2sfo fo-twitter"></i> <span>Tuit</span>',
			'de'=>'<i class="s2sfo fo-twitter"></i> <span>Twittern</span>',
			'el'=>'<i class="s2sfo fo-twitter"></i> <span>Tweet</span>',
			'es'=>'<i class="s2sfo fo-twitter"></i> <span>Twittear</span>',
			'fa'=>'<span>توییت</span> <i class="s2sfo fo-twitter"></i>',
			'fi'=>'<i class="s2sfo fo-twitter"></i> <span>Twiittaa</span>',
			'fil'=>'<i class="s2sfo fo-twitter"></i> <span>I-tweet</span>',
			'fr'=>'<i class="s2sfo fo-twitter"></i> <span>Tweeter</span>',
			'he'=>'<span>ציוץ</span> <i class="s2sfo fo-twitter"></i>',
			'hi'=>'<i class="s2sfo fo-twitter"></i> <span>ट्वीट</span>',
			'hu'=>'<i class="s2sfo fo-twitter"></i> <span>Tweet</span>',
			'id'=>'<i class="s2sfo fo-twitter"></i> <span>Tweet</span>',
			'it'=>'<i class="s2sfo fo-twitter"></i> <span>Tweet</span>',
			'ja'=>'<i class="s2sfo fo-twitter"></i> <span>ツイート</span>',
			'ko'=>'<i class="s2sfo fo-twitter"></i> <span>트윗</span>',
			'msa'=>'<i class="s2sfo fo-twitter"></i> <span>Tweet</span>',
			'nl'=>'<i class="s2sfo fo-twitter"></i> <span>Tweeten</span>',
			'no'=>'<i class="s2sfo fo-twitter"></i> <span>Tweet</span>',
			'pl'=>'<i class="s2sfo fo-twitter"></i> <span>Tweetnij</span>',
			'pt'=>'<i class="s2sfo fo-twitter"></i> <span>Tweetar</span>',
			'ro'=>'<i class="s2sfo fo-twitter"></i> <span>Tweet</span>',
			'ru'=>'<i class="s2sfo fo-twitter"></i> <span>Твитнуть</span>',
			'sv'=>'<i class="s2sfo fo-twitter"></i> <span>Tweeta</span>',
			'th'=>'<i class="s2sfo fo-twitter"></i> <span>ทวีต</span>',
			'tr'=>'<i class="s2sfo fo-twitter"></i> <span>Tweetle</span>',
			'uk'=>'<i class="s2sfo fo-twitter"></i> <span>Твіт</span>',
			'ur'=>'<span>ٹویٹ کریں</span> <i class="s2sfo fo-twitter"></i>',
			'vi'=>'<i class="s2sfo fo-twitter"></i> <span>Tweet</span>',
			'zh-cn'=>'<i class="s2sfo fo-twitter"></i> <span>发推</span>',
			'zh-tw'=>'<i class="s2sfo fo-twitter"></i> <span>推文</span>'
		);

		$tweet = isset($tweet_lang[$lang]) ? $tweet = $tweet_lang[$lang] : $tweet = $tweet_lang['en'];

		return $tweet;
	}

	public function getTwitterFollow($lang, $follow=false){

		$tweet_lang = array(
			'en'=>'<i class="s2sfo fo-twitter"></i> <span>Tweet</span>',
			'ar'=>'<span>على تويتر</span> <i class="s2sfo fo-twitter fa-2x"></i>',
			'bn'=>'<i class="s2sfo fo-twitter"></i> <span>টুইট</span>',
			'cs'=>'<i class="s2sfo fo-twitter"></i> <span>Tweet</span>',
			'da'=>'<i class="s2sfo fo-twitter"></i> <span>Tweet</span>',
			'da'=>'<i class="s2sfo fo-twitter"></i> <span>Tweet</span>',
			'de'=>'<i class="s2sfo fo-twitter"></i> <span>Twittern</span>',
			'el'=>'<i class="s2sfo fo-twitter"></i> <span>Tweet</span>',
			'es'=>'<i class="s2sfo fo-twitter"></i> <span>Twittear</span>',
			'fa'=>'<span>توییت</span> <i class="s2sfo fo-twitter"></i>',
			'fi'=>'<i class="s2sfo fo-twitter"></i> <span>Twiittaa</span>',
			'fil'=>'<i class="s2sfo fo-twitter"></i> <span>I-tweet</span>',
			'fr'=>'<i class="s2sfo fo-twitter"></i> <span>Tweeter</span>',
			'he'=>'<span>ציוץ</span> <i class="s2sfo fo-twitter"></i>',
			'hi'=>'<i class="s2sfo fo-twitter"></i> <span>ट्वीट</span>',
			'hu'=>'<i class="s2sfo fo-twitter"></i> <span>Tweet</span>',
			'id'=>'<i class="s2sfo fo-twitter"></i> <span>Tweet</span>',
			'it'=>'<i class="s2sfo fo-twitter"></i> <span>Tweet</span>',
			'ja'=>'<i class="s2sfo fo-twitter"></i> <span>ツイート</span>',
			'ko'=>'<i class="s2sfo fo-twitter"></i> <span>트윗</span>',
			'msa'=>'<i class="s2sfo fo-twitter"></i> <span>Tweet</span>',
			'nl'=>'<i class="s2sfo fo-twitter"></i> <span>Tweeten</span>',
			'no'=>'<i class="s2sfo fo-twitter"></i> <span>Tweet</span>',
			'pl'=>'<i class="s2sfo fo-twitter"></i> <span>Tweetnij</span>',
			'pt'=>'<i class="s2sfo fo-twitter"></i> <span>Tweetar</span>',
			'ro'=>'<i class="s2sfo fo-twitter"></i> <span>Tweet</span>',
			'ru'=>'<i class="s2sfo fo-twitter"></i> <span>Твитнуть</span>',
			'sv'=>'<i class="s2sfo fo-twitter"></i> <span>Tweeta</span>',
			'th'=>'<i class="s2sfo fo-twitter"></i> <span>ทวีต</span>',
			'tr'=>'<i class="s2sfo fo-twitter"></i> <span>Tweetle</span>',
			'uk'=>'<i class="s2sfo fo-twitter"></i> <span>Твіт</span>',
			'ur'=>'<span>ٹویٹ کریں</span> <i class="s2sfo fo-twitter"></i>',
			'vi'=>'<i class="s2sfo fo-twitter"></i> <span>Tweet</span>',
			'zh-cn'=>'<i class="s2sfo fo-twitter"></i> <span>发推</span>',
			'zh-tw'=>'<i class="s2sfo fo-twitter"></i> <span>推文</span>'
		);

		$tweet = isset($tweet_lang[$lang]) ? $tweet = $tweet_lang[$lang] : $tweet = $tweet_lang['en'];

		return $tweet;
	}
	
	/*FACEBOOK*/
	public function getFacebook($afterplus){
		
		//COMMON
		$params = self::$s2s_obj->params;
		$styles = self::$s2s_obj->styles;

		$full_link = self::$s2s_obj->full_link;
		$title = self::$s2s_obj->title;
		$button_size = $styles->size;
		$buttons = s2s_tmpl::$s2s_buttons;
		$cookie_button = self::$s2s_obj->cookie_button;
		$idioma = self::$s2s_obj->lang;
		$idioma_twitter = self::$s2s_obj->lang_twitter;
		$texts =  s2s_tmpl::getTexts($styles->text);
		$jmobile = self::$s2s_obj->jmobile;


		$facebook_layout = $params->get('facebook_layout', 'button');
		$facebook_share = $params->get('facebook_share', 0);

		
		if($facebook_share){
			$fb_share = 'data-share="true"';
		}else{
			$fb_share = 'data-share="false"';
		}

		if($jmobile){
			$fb_mobile = 'data-mobile-iframe=true';
		}else{
			$fb_mobile = 'data-mobile-iframe=false';
			$fb_mobile = 'data-mobile-iframe=true';
		}



		//COUNT
		

		$facebook_share_count = $params->get('facebook_share_count', 0);

		//Disabled 4.0.141
		$facebook_share_count = 0;
		
		
		if($facebook_share_count){
			$facebook_s_count_html = '<span class="s2s_badge s2s_fb_count_share">0</span>';
		}else{
			$facebook_s_count_html = '';
		}

		$facebook_like_count = $params->get('facebook_like_count', 0);
		//Disabled 4.0.141
		$facebook_like_count = 0;
		
		if($facebook_like_count){
			$facebook_l_count_html = '<span class="s2s_badge s2s_fb_count_like">0</span>';
		}else{
			$facebook_l_count_html = '';
		}



		$facebook_save = $params->get('facebook_save', 0);

		$facebook_size = $params->get('facebook_size', 'small');

		$html ='<div class="s2s_facebook s2s_btn '.$button_size.' '.$afterplus.'" >
			<a class="s2s_icon">'.$buttons['facebook'].$facebook_s_count_html.' '.$facebook_l_count_html.'</a>
				<div class="globo s2s_globo_closed s2s_facebook_iframe">
					<div class="s2s_flecha"></div>
					'.$cookie_button.'<div id="fb-root"></div>';
				/*test*/
					//$html .= '<div class="fb-like" data-href="http://www.dibuxo.com" data-width="450" data-show-faces="false" data-send="true"></div>';
				/*real*/
					$html .= '<div class="fb-like" 
						data-layout="'.$facebook_layout.'" 
						data-action="like" ';

						if($params->get('s2s_virtuemart_fixsef', 0) && $params->get('s2s_virtuemart', 0))
						{
							//var_dump("data-href disabled");
						}else{ 
							$html .= 'data-href="'.$full_link.'" ';
							//var_dump("data-href enabled");
						}		

						$html .= 'data-width="450" data-show-faces="false" data-size="'.$facebook_size.'" data-send="true" '.$fb_share.' '.$fb_mobile.'>
					</div>';

					if($facebook_save){
						$html .= '</br></br><div class="fb-save" 
						    data-uri="'.$full_link.'" data-size="small">
						</div>';
					}

				$html .= '</div>
			</div>';

		return $html;
	}

	/*PINTEREST*/
	public function getPinterest($afterplus){
		
		//COMMON
		$params = self::$s2s_obj->params;
		$styles = self::$s2s_obj->styles;

		$full_link = self::$s2s_obj->full_link;
		$title = self::$s2s_obj->title;
		$button_size = $styles->size;
		$buttons = s2s_tmpl::$s2s_buttons;
		$cookie_button = self::$s2s_obj->cookie_button;
		$idioma = self::$s2s_obj->lang;
		$idioma_twitter = self::$s2s_obj->lang_twitter;
		$texts =  s2s_tmpl::getTexts($styles->text);
		$jmobile = self::$s2s_obj->jmobile;

		//shape
		$pinterest_shape = $params->get('pinterest_shape', 1);
		if($pinterest_shape){
			$pin_shape = 'data-pin-shape="round"';
		}else{
			$pin_shape = '';
		}

		//size
		$pinterest_size = $params->get('pinterest_size', 1);
		if($pinterest_size){
			$pin_size = 'data-pin-tall="true"';
		}else{
			$pin_size = '';
		}

		//COUNT
		$pinterest_count = $params->get('pinterest_count', 0);
		//Disabled 4.0.141
		$pinterest_count = 0;

		if($pinterest_count){
			$pinterest_s_count_html = '<span class="s2s_badge s2s_pinterest_badge_count">0</span>';
		}else{
			$pinterest_s_count_html = '';
		}

		//COUNT in button
		$pinterest_b_count = $params->get('pinterest_b_count', 0);

		if($pinterest_b_count == 0){
			$pinterest_b_count_code = '';
		}elseif($pinterest_b_count == 1){
			$pinterest_b_count_code = 'data-pin-count="above"';
		}else{
			$pinterest_b_count_code = 'data-pin-count="beside"';
		}



		//shape
		$pinterest_color = $params->get('pinterest_color', 'red');
		$pin_color = 'data-pin-color="'.$pinterest_color.'"';
		$document = JFactory::getDocument();

		$html = '';
		

		$html .= '<div class="s2s_pinterest s2s_btn '.$button_size.' '.$afterplus.'">';

		$html .= '<a class="s2s_icon">'.$buttons['pinterest'].' '.$pinterest_s_count_html.'</a>';
		
		$html .= '<div class="globo s2s_globo_closed s2s_pinterest_iframe">
					<div class="s2s_flecha"></div>
					'.$cookie_button;
				//$html .= '<a href="//www.pinterest.com/pin/create/button/?url='.rawurlencode(JURI::getInstance()->toString()).'&amp;description='.rawurlencode($document->getTitle()).'"'; 
				
				//2.1.8
				
				$html .= '<a href="//www.pinterest.com/pin/create/button/?url='.rawurlencode(JURI::getInstance()->toString()).'&amp;description='.rawurlencode($title).'"'; 
				$html .= ' data-pin-do="buttonBookmark" 
					'.$pin_shape.' 
					'.$pin_color.' 
					'.$pinterest_b_count_code.' 
					'.$pin_size.' data-pin-sticky="false">';
					//$html .='<img src="//assets.pinterest.com/images/pidgets/pinit_fg_en_rect_white_28.png" alt="Pinterest"/>';
					$html .='</a>';
				$html .=' </div>
			</div>';

		return $html;
	}

	/*LINKEDIN*/
	public function getLinkedin($afterplus){
		
		//COMMON
		$params = self::$s2s_obj->params;
		$styles = self::$s2s_obj->styles;

		$full_link = self::$s2s_obj->full_link;
		$title = self::$s2s_obj->title;
		$button_size = $styles->size;
		$buttons = s2s_tmpl::$s2s_buttons;
		$cookie_button = self::$s2s_obj->cookie_button;
		$idioma = self::$s2s_obj->lang;
		$idioma_twitter = self::$s2s_obj->lang_twitter;
		$texts =  s2s_tmpl::getTexts($styles->text);
		$jmobile = self::$s2s_obj->jmobile;


		$linkedin_counter = $params->get('linkedin_counter', 0);
		//disabled in 4.0.141
		$linkedin_counter = 0;

		if($linkedin_counter == 2){
			$linkedin_c='data-counter="top"';
		}elseif($linkedin_counter == 1){
			$linkedin_c='data-counter="right"';
		}else{
			$linkedin_c='';
		}

		$linkedin_count = $params->get('linkedin_count', 0);
		
		if($linkedin_count){
			$linkedin_t_count_html = '<span class="s2s_badge s2s_linkedin_badge_count">0</span>';
		}else{
			$linkedin_t_count_html = '';
		}

		$html ='<div class="s2s_linkedin s2s_btn '.$button_size.' '.$afterplus.'" >
			<a class="s2s_icon">'.$buttons['linkedin'].' '.$linkedin_t_count_html.'</a>
				<div class="globo s2s_globo_closed s2s_linkedin_iframe">
					<div class="s2s_flecha"></div>
				'.$cookie_button.'
					<script type="IN/Share" '.$linkedin_c.' data-url="'.$full_link.'"></script>
				</div>
			</div>';

		return $html;
	}

	/*GOOGLE PLUS*/
	public function getGPlus($afterplus){
		
		//COMMON
		$params = self::$s2s_obj->params;
		$styles = self::$s2s_obj->styles;

		$full_link = self::$s2s_obj->full_link;
		$title = self::$s2s_obj->title;
		$button_size = $styles->size;
		$buttons = s2s_tmpl::$s2s_buttons;
		$cookie_button = self::$s2s_obj->cookie_button;
		$idioma = self::$s2s_obj->lang;
		$idioma_twitter = self::$s2s_obj->lang_twitter;
		$texts =  s2s_tmpl::getTexts($styles->text);
		$jmobile = self::$s2s_obj->jmobile;

		//counter
		$gcount = 'data-annotation="'.$params->get('gplus_count','bubble').'"';
		if($params->get('gplus_count','bubble')=='inline'){
			$gcount = '';
		}
		//size
		$gsize = 'data-height="'.$params->get('gplus_size',0).'"';
		if($params->get('gplus_size',0)==0){
			$gsize = '';
		}

		$gplus_b_count = $params->get('gplus_b_count', 0);
		
		if($gplus_b_count){
			//$gcount = $this->gplus_count($full_link);
			//I will do with donReach
			$gcount = '0';
			$gplus_b_t_count_html = '<span class="s2s_badge s2s_gplus_b_badge_count">'.$gcount.'</span>';
		}else{
			$gplus_b_t_count_html = '';
		}

		//follow
		$gplus_follow_on = $params->get('gplus_follow_on', 0);
		$gplus_follow_id = $params->get('gplus_follow_id', 0);




		$html = '<div class="s2s_gplus s2s_btn '.$button_size.' '.$afterplus.'" lang="'.$idioma_twitter.'"><a class="s2s_icon">'.$buttons['gplus'].' '.$gplus_b_t_count_html.'</a>
				<div class="globo s2s_globo_closed s2s_gplus_iframe">
					<div class="s2s_flecha"></div>
				'.$cookie_button.'
						<div class="s2s_gplus_one">
							<div class="g-plus" data-action="share" data-href="'.$full_link.'" '.$gcount.' '.$gsize.'></div>
						</div>';
						
						//G+1
						if($params->get('gplus1_on',1)){

							//counter +1
							$gcount1 = 'data-annotation="'.$params->get('gplus1_count','bubble').'"';

							if($params->get('gplus1_count','bubble')=='bubble'){
								$gcount1 = '';
							}
							//size +1
							$gsize1 = 'data-size="'.$params->get('gplus1_size','standard').'"';
							
							$html .= '<div class="s2s_gplus_one">
									<div class="g-plusone" '.$gsize1.' data-href="'.$full_link.'" '.$gcount1.'></div>
							</div>';
						}

						//follow
						if($gplus_follow_on && $gplus_follow_id!=0){
							$html .= '<div class="s2s_gplus_one"><div class="g-follow" data-annotation="bubble" data-height="20" data-href="//plus.google.com/u/0/'.$gplus_follow_id.'" data-rel="publisher"></div></div>';
						}
				$html .= '</div>
			</div>';

		return $html;
	}

	public static function gplus_count( $url ) {
	    /* get source for custom +1 button */
    	$jinput = JFactory::getApplication()->input;
		$option = $jinput->get('option');

		if($option=='com_plugins'){
			return;
		}

	    $contents = file_get_contents( 'https://plusone.google.com/_/+1/fastbutton?url=' .  $url );
	 
	    /* pull out count variable with regex */
	    preg_match( '/window\.__SSR = {c: ([\d]+)/', $contents, $matches );
	 	
	    /* if matched, return count, else zed */
	    if( isset( $matches[0] ) ) 
	        return (int) str_replace( 'window.__SSR = {c: ', '', $matches[0] );
	    return 0;
	}

	/*WAPP*/
	public function getWapp($afterplus){
		
		//COMMON
		$params = self::$s2s_obj->params;
		$styles = self::$s2s_obj->styles;

		$full_link = self::$s2s_obj->full_link;
		$title = self::$s2s_obj->title;
		$button_size = $styles->size;
		$buttons = s2s_tmpl::$s2s_buttons;
		$cookie_button = self::$s2s_obj->cookie_button;
		$idioma = self::$s2s_obj->lang;
		$idioma_twitter = self::$s2s_obj->lang_twitter;
		$texts =  s2s_tmpl::getTexts($styles->text);
		$jmobile = self::$s2s_obj->jmobile;

		//check if is mobile
		if($params->get('wapp_only_mobile',0)){
			$wapp_only_mobile = 'visible-xs';
	        
	        if (!$jmobile){
	            return '';
	        }
		}else{
			$wapp_only_mobile = '';
		}

		$html ='<div class="s2s_wapp s2s_btn '.$button_size.' '.$wapp_only_mobile.' '.$afterplus.'" >
			
			<a class="s2s_icon">'.$buttons['wapp'].'</a>
				<div class="globo s2s_globo_closed s2s_wapp_iframe">
					<div class="s2s_flecha"></div>
				

				<a class="s2s_a_btn wapp_link" 
					href="whatsapp://send?text='.urlencode($full_link).'" 
					data-text="'.$title.'" 
					data-action="share/whatsapp/share"
					data-href="'.urlencode($full_link).'"
				>
					<i class="s2sfo fo-whatsapp" aria-hidden="true"></i>
					<span class="s2s_text_int">'.JText::_('SOCIAL2S_WAPP_SHARE').'</span>
				</a>

				</div>
			</div>';

		return $html;
	}

	/*TGRAM*/
	public function getTgram($afterplus){
		
				//COMMON
		$params = self::$s2s_obj->params;
		$styles = self::$s2s_obj->styles;

		$full_link = self::$s2s_obj->full_link;
		$title = self::$s2s_obj->title;
		$button_size = $styles->size;
		$buttons = s2s_tmpl::$s2s_buttons;
		$cookie_button = self::$s2s_obj->cookie_button;
		$idioma = self::$s2s_obj->lang;
		$idioma_twitter = self::$s2s_obj->lang_twitter;
		$texts =  s2s_tmpl::getTexts($styles->text);
		$jmobile = self::$s2s_obj->jmobile;

		//check if is mobile
		if($params->get('tgram_only_mobile',0)){
			$tgram_only_mobile = 'visible-xs';

			$doc  = JFactory::getDocument();
			//3.0
			/*$appWeb      = new JApplicationWeb;
			//si no es movil
			if (!$appWeb->client->mobile){
				return '';
			}*/

			// 4.0
	        $appWeb = new JApplicationWebClient;
	        
	        if (!$appWeb->mobile){
	            return '';
	        }
		}else{
			$tgram_only_mobile = '';
		}

		$html ='<div class="s2s_tgram s2s_btn '.$button_size.' '.$tgram_only_mobile.' '.$afterplus.'" >
			
			<a class="s2s_icon">'.$buttons['tgram'].'</a>
				<div class="globo s2s_globo_closed s2s_tgram_iframe">
					<div class="s2s_flecha"></div>
				

				<a class="s2s_a_btn tgram_link" 
					href="https://telegram.me/share/url?url='.urlencode($full_link).'&text='.$title.'" target="_blank"
				><i class="s2sfo fo-telegram" aria-hidden="true"></i><span class="s2s_text_int">'.JText::_('SOCIAL2S_TGRAM_SHARE').'<span></a>

				</div>
			</div>';

		return $html;
	}

	/*FLIPB*/
	public function getFlipb($afterplus){
		
		//COMMON
		$params = self::$s2s_obj->params;
		$styles = self::$s2s_obj->styles;

		$full_link = self::$s2s_obj->full_link;
		$title = self::$s2s_obj->title;
		$button_size = $styles->size;
		$buttons = s2s_tmpl::$s2s_buttons;
		$cookie_button = self::$s2s_obj->cookie_button;
		$idioma = self::$s2s_obj->lang;
		$idioma_twitter = self::$s2s_obj->lang_twitter;
		$texts =  s2s_tmpl::getTexts($styles->text);
		$jmobile = self::$s2s_obj->jmobile;

		//check if is mobile
		if($params->get('flipb_only_mobile',0)){
			$flipb_only_mobile = 'visible-xs';
	        if (!$jmobile ){
	            return '';
	        }
		}else{
			$flipb_only_mobile = '';
		}

		$html ='<div class="s2s_flipb s2s_btn '.$button_size.' '.$flipb_only_mobile.' '.$afterplus.'" >';
			
			$html .= '<a class="s2s_icon">'.$buttons['flipb'].'</a>
				<div class="globo s2s_globo_closed s2s_flipb_iframe">
					<div class="s2s_flecha"></div>
				'.$cookie_button.'

					<a data-flip-widget="flipit" href="https://flipboard.com"></a>';

				$html .= '</div>
		</div>';

		return $html;

	}

	/*DELIO*/
	public function getDelio($afterplus){
		
		//COMMON
		$params = self::$s2s_obj->params;
		$styles = self::$s2s_obj->styles;

		$full_link = self::$s2s_obj->full_link;
		$title = self::$s2s_obj->title;
		$button_size = $styles->size;
		$buttons = s2s_tmpl::$s2s_buttons;
		$cookie_button = self::$s2s_obj->cookie_button;
		$idioma = self::$s2s_obj->lang;
		$idioma_twitter = self::$s2s_obj->lang_twitter;
		$texts =  s2s_tmpl::getTexts($styles->text);
		$jmobile = self::$s2s_obj->jmobile;

		$doc  = JFactory::getDocument();
		//check if is mobile
		if($params->get('delio_only_mobile',0)){
			$delio_only_mobile = 'visible-xs';        
	        if (!$jmobile){
	            return '';
	        }
		}else{
			$delio_only_mobile = '';
		}

		$html ='<div class="s2s_delio s2s_btn '.$button_size.' '.$delio_only_mobile.' '.$afterplus.'" >';
			
			$html .= '<a class="s2s_icon">'.$buttons['delio'].'</a>
				<div class="globo s2s_globo_closed s2s_delio_iframe">
					<div class="s2s_flecha"></div>';

				//prepare js variables
				//TODO (do it in general)
				//$doc->addScriptDeclaration("var php_full_link = '{$full_link}';");
				//$doc->addScriptDeclaration("var php_text = '{$title}';");

				$html .= '<a class="s2s_a_btn delio_link" onclick="window.open(\'//del.icio.us/save?v=5&noui&jump=close&url=\'+decodeURIComponent(php_full_link)+\'&notes=asdf&title=\'+decodeURIComponent(php_title), \'delicious\',\'toolbar=no,width=550,height=550\'); return false;"><i class="s2sfo fo-delio" aria-hidden="true"></i><span class="s2s_text_int">'.JText::_('SOCIAL2S_DELIO_SHARE').'</span></a>';

				$html .= '</div>
		</div>';

		return $html;

	}
	/*REDDIT*/
	public function getReddit($afterplus){

		//COMMON
		$params = self::$s2s_obj->params;
		$styles = self::$s2s_obj->styles;

		$full_link = self::$s2s_obj->full_link;
		$title = self::$s2s_obj->title;
		$button_size = $styles->size;
		$buttons = s2s_tmpl::$s2s_buttons;
		$cookie_button = self::$s2s_obj->cookie_button;
		$idioma = self::$s2s_obj->lang;
		$idioma_twitter = self::$s2s_obj->lang_twitter;
		$texts =  s2s_tmpl::getTexts($styles->text);
		$jmobile = self::$s2s_obj->jmobile;


		$html =   '<div class="s2s_reddit s2s_btn '.$button_size.' '.$afterplus.'" lang="'.$idioma_twitter.'">
			<a class="s2s_icon">'.$buttons['reddit'].'</a>
			<div class="globo s2s_globo_closed s2s_reddit_iframe">
				<div class="s2s_flecha"></div>';
					//'.$cookie_button;
					//$html .= '<a href="http://www.tumblr.com/share" title="Share on Tumblr">Share on Tumblr</a>';
				$html .= '<a class="s2s_a_btn reddit_link"
					href="//www.reddit.com/submit?url='.urlencode($full_link).'" 
					target="_blank"
				><i class="s2sfo fo-reddit" aria-hidden="true"></i><span class="s2s_text_int">'.JText::_('SOCIAL2S_REDDIT_SHARE').'<span></a>';

			$html .= '</div></div>';

		return $html;
	}

	/*TUMBLR*/
	public function getTumblr($afterplus){

		//COMMON
		$params = self::$s2s_obj->params;
		$styles = self::$s2s_obj->styles;

		$full_link = self::$s2s_obj->full_link;
		$title = self::$s2s_obj->title;
		$button_size = $styles->size;
		$buttons = s2s_tmpl::$s2s_buttons;
		$cookie_button = self::$s2s_obj->cookie_button;
		$idioma = self::$s2s_obj->lang;
		$idioma_twitter = self::$s2s_obj->lang_twitter;
		$texts =  s2s_tmpl::getTexts($styles->text);
		$jmobile = self::$s2s_obj->jmobile;

		$tumblr_follow = $params->get('tumblr_follow', 0);
		$tumblr_user = $params->get('tumblr_user', '');
		$tumblr_counter = $params->get('tumblr_counter', 0);

		if($tumblr_counter == 2){
			$tumblr_c='data-notes="top"';
		}elseif($tumblr_counter == 1){
			$tumblr_c='data-notes="right"';
		}else{
			$tumblr_c='';
		}

		//COUNT
		$tumblr_count = $params->get('tumblr_count', 0);
		//disabled in 4.0.141
		$tumblr_count = 0;
		
		if($tumblr_count){
			$tumblr_s_count_html = '<span class="s2s_badge s2s_tumblr_badge_count">0</span>';
		}else{
			$tumblr_s_count_html = '';
		}

		$tumblr_color = $params->get('tumblr_color', 'blue');

		$html =   '<div class="s2s_tumblr s2s_btn '.$button_size.' '.$afterplus.'" lang="'.$idioma_twitter.'"><a class="s2s_icon">'.$buttons['tumblr'].' '.$tumblr_s_count_html.'</a>
				<div class="globo s2s_globo_closed s2s_tumblr_iframe">
					<div class="s2s_flecha"></div>
					'.$cookie_button;



					//$html .= '<a href="http://www.tumblr.com/share" title="Share on Tumblr">Share on Tumblr</a>';
					$html .= '<a class="tumblr-share-button" 
					data-locale="'.$idioma.'" 
					data-href="'.$full_link.'" 
					data-color="'.$tumblr_color.'" 
					'.$tumblr_c.'
					href="https://embed.tumblr.com/share"></a>';
					if($tumblr_follow){
						
						//if cookies, display none
						$display_tb = ($cookie_button!=''?'style="display:none;"': '');

						$html .= '<br><iframe '.$display_tb.' class="tumblr-follow-button" ';
							//$html .= 'frameborder="0" border="0" scrolling="no" allowtransparency="true" ';
							$html .= 'height="24" width="80" src="https://platform.tumblr.com/v2/follow_button.html?type=follow&amp;tumblelog='.$tumblr_user.'&amp;color='.$tumblr_color.'"></iframe>';
					}
				$html .= '</div></div>';

		return $html;
	}

	/*VK*/
	public function getVk($afterplus){

		//COMMON
		$params = self::$s2s_obj->params;
		$styles = self::$s2s_obj->styles;

		$full_link = self::$s2s_obj->full_link;
		$title = self::$s2s_obj->title;
		$button_size = $styles->size;
		$buttons = s2s_tmpl::$s2s_buttons;
		$cookie_button = self::$s2s_obj->cookie_button;
		$idioma = self::$s2s_obj->lang;
		$idioma_twitter = self::$s2s_obj->lang_twitter;
		$texts =  s2s_tmpl::getTexts($styles->text);
		$jmobile = self::$s2s_obj->jmobile;

		if(!defined('VK_SCRIPT')){
			//$html .= '<script type="text/javascript" src="http://vkontakte.ru/js/api/share.js?9" charset="windows-1251"></script>';
			//define('VK_SCRIPT', 'true');
		}

		$vk_b_count = $params->get('s2s_vk_count', 0);
		//disabled in 4.0.141
		$vk_b_count = 0;
		
		if($vk_b_count){
			$vk_t_count_html = '<span class="s2s_badge s2s_vk_badge_count">0</span>';
		}else{
			$vk_t_count_html = '';
		}


		$html =   '<div class="s2s_vk s2s_btn '.$button_size.' '.$afterplus.'" lang="'.$idioma_twitter.'"><a class="s2s_icon">'.$buttons['vk'].' '.$vk_t_count_html.'</a>';

			if(!defined('VK_SCRIPT')){

				if($params->get('vk_new',0)){

					//new open id way
					$html .= '<script type="text/javascript" src="https://vk.com/js/api/openapi.js" charset="windows-1251"></script>';

					$html .= '<script type="text/javascript"> 
					  VK.init({ 
					    apiId: '.$params->get('vk_api','0').', 
					    onlyWidgets: true 
					  }); 
					</script>';
				}else{
					//default creepy way
					$html .= '<script type="text/javascript" src="https://vkontakte.ru/js/api/share.js?9" charset="windows-1251" asyn="async"></script>';
				}
				//define('VK_SCRIPT', 'true');
			}
			$html .= '<div class="globo s2s_globo_closed s2s_vk_iframe" id="s2s_vk_iframe">
					<div class="s2s_flecha"></div>';

					//new open id way
					if($params->get('vk_new')){
						if($context=='k2.itemlist' || $context=='com_content.category' || $context=='com_content.featured'){
							//multiple vk
							$vk_unique = PlgContentSocial2s::$article_num;
							PlgContentSocial2s::$article_num+=1;
						}else{
							$vk_unique = '';
						}
						$html .= '<div id="vk_like'.$vk_unique.'" class="vk_button"></div>';
						//$html .= '<script type="text/javascript"> 
						 //	VK.Widgets.Like("vk_like'.$vk_unique.'"); 
						//</script>';
						
					}else{
						//old default creepy way
						$html .= '<script type="text/javascript"><!--
						document.write(VK.Share.button({ 
							url: "'.$full_link.'",
							title: "'.$title.'"
							}));
						--></script>';
					}

				$html .= '</div>
			</div>
		';

		return $html;
	}

	/*EMAIL*/
	public function getEmail($afterplus){
		
				//COMMON
		$params = self::$s2s_obj->params;
		$styles = self::$s2s_obj->styles;

		$full_link = self::$s2s_obj->full_link;
		$title = self::$s2s_obj->title;
		$button_size = $styles->size;
		$buttons = s2s_tmpl::$s2s_buttons;
		$cookie_button = self::$s2s_obj->cookie_button;
		$idioma = self::$s2s_obj->lang;
		$idioma_twitter = self::$s2s_obj->lang_twitter;
		$texts =  s2s_tmpl::getTexts($styles->text);
		$jmobile = self::$s2s_obj->jmobile;


		$html ='<div class="s2s_email s2s_btn '.$button_size.' '.$afterplus.'" >
			
			<a class="s2s_icon">'.$buttons['email'].'</a>
				<div class="globo s2s_globo_closed s2s_email_iframe">
					<div class="s2s_flecha"></div>
				

				<a class="s2s_a_btn email_link" 
					href="mailto:someone@example.com?Subject='.$title.'&body='.urlencode($full_link).'"
				><i class="s2sfo fo-email" aria-hidden="true"></i><span class="s2s_text_int">'.JText::_('SOCIAL2S_EMAIL_SHARE').'<span></a>

				</div>
			</div>';

		return $html;
	}




	/*MAS*/
	public function getMas(){
		
		//COMMON
		$params = self::$s2s_obj->params;
		$styles = self::$s2s_obj->styles;

		$full_link = self::$s2s_obj->full_link;
		$title = self::$s2s_obj->title;
		$button_size = $styles->size;
		$buttons = s2s_tmpl::$s2s_buttons;
		$cookie_button = self::$s2s_obj->cookie_button;
		$idioma = self::$s2s_obj->lang;
		$idioma_twitter = self::$s2s_obj->lang_twitter;
		$texts =  s2s_tmpl::getTexts($styles->text);
		$jmobile = self::$s2s_obj->jmobile;

		$mas_style = $params->get('mas_style','rplus_q');

		//check if is mobile
		//if($params->get('mas_only_mob',0)){
		if($params->get('mas_on',0)=='2'){
			$wapp_only_mobile = 'visible-xs';

	        if (!$jmobile){
	            return '';
	        }
		}else{
			$wapp_only_mobile = '';
		}

		$html ='<div class="s2s_mas s2s_btn '.$button_size.' '.$wapp_only_mobile.'" >';
			
		//$html .=	'<a><i class="fa fa-plus-circle"></i></a>';
		$html .=	'<a><i class="s2sfo fo-'.$mas_style.'"></i></a>';
				

		$html .=	'</div>';

		//$html ='';

		return $html;
	}





}
?>