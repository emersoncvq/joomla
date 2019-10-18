<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('text');

/**
 * Form Field class for the Joomla Platform.
 * Provides and input field for e-mail addresses
 *
 * @link   http://www.w3.org/TR/html-markup/input.email.html#input.email
 * @see    JFormRuleEmail
 * @since  11.1
 */
class JFormFields2spreview extends JFormFieldText
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  11.1
     */
    protected $type = 's2spreview';

    /**
     * Method to get the field input markup for e-mail addresses.
     *
     * @return  string  The field input markup.
     *
     * @since   11.1
     */
    protected function getInput()
    {
        $plugin_path = JURI::root( true ).'/plugins/content/social2s';
        $plugin_path = JPATH_PLUGINS.'/content/social2s';
        require_once($plugin_path.'/features/social.php');
   
        if(!JPluginHelper::isEnabled('content', 'social2s')){
            return '<div class="alert alert-warning">'.JText::sprintf('SOCIAL2S_JNO_PREVIEW').'</div>';
        }

        $s2s_social = new s2s_social;

        $plugin = JPluginHelper::getPlugin('content', 'social2s');
        $params = new JRegistry($plugin->params);

        $row = (object) array('introtext' => '');
        $context = 'com_content.article';

        //$html = $this->formatTmpl($params,$row,false,$context);

        //return $html;
        
    }



    public function formatTmpl(&$params,$article, $trigger = false,$context){

        $s2s_pro_variables = $params->get('s2s_pro_variables', 0);

        $jversion = new JVersion();
        $document = JFactory::getDocument();

        $buttons = $this->getButtons($params,$context);
        $plugin_path = JURI::root( true ).'/plugins/content/social2s';
        $images_path = JURI::root( true ).'/plugins/content/social2s/assets/';
        $media_path = JURI::root( true ).'/media/plg_social2s';

        if($params->get('load_jquery', '0') == 1){

            //$this->s2s_debug[] = '<strong>Jquery: </strong> <span class="label label-warning"> loaded </span>';
            //$document->addScript($plugin_path.'/js/jquery-2.2.0.min.js', 'text/javascript');
        }
        if($params->get('load_bootstrap', '0') == 1){

            //$this->s2s_debug[] = '<strong>Bootstrap: </strong> <span class="label label-warning"> loaded </span>';
            $document->addStyleSheet($media_path.'/css/bootstrap.css', 'text/css');
        }

        if($params->get('social2s_minify_debug', 0) == 1){
            //$this->s2s_debug[] = '<strong>javascript minification: </strong> <span class="label label-warning"> deactivated </span>';
            $document->addScript($media_path.'/js/social2s.js', 'text/javascript');
        }else{
            $document->addScript($media_path.'/js/social2s.min.js', 'text/javascript');
        }

        

        if($context=='com_content.category' || $context=='com_content.featured'){


            $show_text = $params->get('s2s_cat_show_text', 1);
            $s2sgroup = $params->get('s2s_cat_group', 0);
            $button_size = $params->get('s2s_cat_size', 'btn');
            $alineamiento = $params->get('s2s_cat_button_align', 'left');
            $load_style = $params->get('s2s_cat_load_style', true);
            $style = $params->get('s2s_cat_style', "-default-");
            //check SAME
            if($this->params->get('s2s_takefromarticle')){
                $show_text = $params->get('s2s_art_show_text', 1);
                $s2sgroup = $params->get('s2s_art_group', 0);
                $button_size = $params->get('s2s_art_size', 'btn');
                $alineamiento = $params->get('s2s_art_button_align', 'left');
                $load_style = $params->get('s2s_art_load_style', true);
                $style = $params->get('s2s_art_style', "-default-");
            }
        }elseif($context=='com_content.article' || $context=='com_virtuemart.productdetails' || $context=='jevents'){
            $show_text = $params->get('s2s_art_show_text', 1);
            $s2sgroup = $params->get('s2s_art_group', 0);
            $button_size = $params->get('s2s_art_size', 'btn');
            $alineamiento = $params->get('s2s_art_button_align', 'left');
            $load_style = $params->get('s2s_art_load_style', true);
            $style = $params->get('s2s_art_style', "-default-");
        }



        /*VARIABLES*/
        if($s2s_pro_variables){
            //SHOW TEXT
            $var_s2s_show_text = JFactory::getApplication()->input->getInt('s2s_show_text');
            if($var_s2s_show_text){
                $show_text=true;
            }
        }


/*TEMPLATES*/
        $load_base = $params->get('social2s_load_base', true);
        $base = $params->get('social2s_base', '0');

        $basecss="";
        if($load_base){

            if($base=="0"){
                $basecss="s2sdefault";
            }elseif($base=="1"){
                $basecss="s2smodern";
            }else{
                $basecss="s2sdefault";
            }
            //minify
            if($params->get('social2s_minify_debug', 0) == 0){
                $document->addStyleSheet($media_path.'/css/behavior/'.$basecss.'.min.css', 'text/css');
            }else{
                $document->addStyleSheet($media_path.'/css/behavior/'.$basecss.'.css', 'text/css');
            }
        }
        if($load_style){
            /*variables*/
            if($s2s_pro_variables){
                $var_s2s_style = JFactory::getApplication()->input->getWord('s2s_style');
                if($var_s2s_style){
                    $var_s2s_style_array = array('default', 'colour','dark','icon_colour');
                    
                    if(in_array($var_s2s_style, $var_s2s_style_array)){
                        //if($var_s2s_style == 'default') $var_s2s_style = '-default-';
                        $style = $var_s2s_style;
                    }
                }
            }
            $document->addStyleSheet($media_path.'/css/styles/'.$style.'.css', 'text/css');
        }

        /*carga fontawesome*/
        if($params->get('load_awesome', 0)){
            $document->addStyleSheet($media_path.'/css/font-awesome.min.css', 'text/css');
        }

/*LANGUAGE*/
        $idioma = $params->get('s2s_language', '0');
        $idioma_joomla = JFactory::getLanguage()->getTag();
        // Twitter and Gplus
        if($idioma != '0'){
            $idioma_twitter = substr($idioma, 0, 2);
        }else{
            $idioma_twitter =  substr(str_replace('-', '_', JFactory::getLanguage()->getTag()),0, 2);
            $idioma = str_replace('-', '_', JFactory::getLanguage()->getTag());
        }
/*FIN LANGUAGE*/

/*LINKS*/   

        /*carga texts*/
        $texts = $this->getTexts($show_text);
/*fin LINKS*/

/*cookies*/
        $insert = $params->get('s2s_insert', '0');
        $insert_position = $params->get('s2s_insert_position', '0');
        $insert_element = $params->get('s2s_insert_element', '');

/*plugin options*/
        $social2s_load_social_scripts = $params->get('social2s_load_social_scripts', '0');
        $s2s_art_mobile_min = $params->get('s2s_art_mobile_min', 978);

        $social2s_debug = $params->get('social2s_debug', 0);
        $s2s_cta_active = $params->get('s2s_cta_active', 0);
        $s2s_cta_default = $params->get('s2s_cta_default', 'twitter');


/*DEMO*/
$s2sdemo = JFactory::getApplication()->input->getInt('s2sdemo');

        $html = "";
        $s2s_debug_msg="";

        /*DEBUG*/
        if($social2s_debug){
            //old 2.1.7
            /*$html .= '<div class="s2s_debug">';
                $html .= '<a class="label">context: '.$context.'</a>';
            $html .= '</div>';*/
            $s2s_option_type = 'type="text"';
        }else{
            $s2s_option_type = 'type="hidden"';
        }


        /*$html .= '<div class="s2s_options">';
            $html .= '<div class="s2s_insert">'.$insert.'</div>';
            $html .= '<div class="s2s_insert_position">'.$insert_position.'</div>';
            $html .= '<div class="s2s_insert_element">'.$insert_element.'</div>';

            $html .= '<div class="social2s_behavior">'.$basecss.'</div>';
            if($s2sdemo){
                $html .= '<input name="social2s_demo" type="hidden" class="social2s_demo" value="1"/>';
                $uri = JFactory::getURI();
                $absolute_url = $uri->toString();
                $s2s_current_url = $absolute_url;
                $html .= '<input name="social2s_url" type="hidden" class="social2s_url" value="'.$s2s_current_url.'" />';
            }
            $html .= '<input name="social2s_lang_1tag" '.$s2s_option_type.' class="social2s_lang_1tag" value="'.$idioma.'" />';
            $html .= '<input name="social2s_lang_2tag" '.$s2s_option_type.' class="social2s_lang_2tag" value="'.$idioma_twitter.'" />';
            $html .= '<input name="social2s_load_social_scripts" '.$s2s_option_type.' class="social2s_load_social_scripts" value="'.$social2s_load_social_scripts.'" />';
            $html .= '<input name="social2s_context" '.$s2s_option_type.' class="social2s_context" value="'.$context.'" />';
            $html .= '<input name="social2s_debug" '.$s2s_option_type.' class="social2s_debug" value="'.$social2s_debug.'" />';
            $html .= '<input name="s2s_art_mobile_min" '.$s2s_option_type.' class="s2s_art_mobile_min" value="'.$s2s_art_mobile_min.'" />';
            
            if($context != 'com_content.article'){
                $s2s_cta_active = 0;
            }


            
            $html .= '<input name="s2s_cta_active" '.$s2s_option_type.' class="s2s_cta_active" value="'.$s2s_cta_active.'" />';
            $html .= '<input name="s2s_cta_default" '.$s2s_option_type.' class="s2s_cta_default" value="'.$s2s_cta_default.'" />';
        $html .= '</div>';
        */







    /*POS FIXED*/
    $s2s_pos_fix = $params->get('s2s_art_fixed', 0);
    if($s2s_pro_variables){
        $var_s2s_pos_fix = JFactory::getApplication()->input->getInt('s2s_pos_fix');
        if($var_s2s_pos_fix){
            $s2s_pos_fix = 1;
        }
    }


    $pos_fix = '';
    $s2s_art_fixed_mode = false;
    $realign = '';
    $cssalign = '';
    if($s2s_pos_fix && $context=='com_content.article'){

        $s2s_art_fixed_mode = $params->get('s2s_art_fixed_mode', 's2s_fixed_horizontal');
        $s2s_art_fixed_posx = $params->get('s2s_art_fixed_posx', 'right');
        $s2s_art_fixed_posy = $params->get('s2s_art_fixed_posy', 'center');

        $pos_fix .= 's2s_pos_fixed ';
        $pos_fix .= $s2s_art_fixed_mode;
        $pos_fix .= ' s2s_pos_fix_x_'.$s2s_art_fixed_posx;
        $pos_fix .= ' s2s_pos_fix_y_'.$s2s_art_fixed_posy;

        
    }else{
        //$realign = 'align="'.$alineamiento.'"';
        $cssalign = 'align_'.$alineamiento;
    }

    //mobile 
    $s2s_art_fill = $params->get('s2s_art_fill', '0');
    $s2s_cat_fill = $params->get('s2s_cat_fill', '0');


    if($context == 'com_content.article'){
        if($s2s_art_fill == 0){
            $css_fill = '';
        }elseif($s2s_art_fill == 1){
            $css_fill = 'fill';
        }else{
            $css_fill = 'fill_mobile';
        }
        
    }elseif($context=='com_content.category' || $context=='com_content.featured'){
        if($s2s_cat_fill == 0){
            $css_fill = '';
        }elseif($s2s_cat_fill == 1){
            $css_fill = 'fill';
        }else{
            $css_fill = 'fill_mobile';
        }

    }else{
        if($s2s_cat_fill == 0){
            $css_fill = '';
        }elseif($s2s_cat_fill == 1){
            $css_fill = 'fill';
        }else{
            $css_fill = 'fill_mobile';
        }
    }

    //BALLOON POSITION
    $s2s_art_balloon_pos = $params->get('s2s_art_balloon_pos', '0');

    if($s2s_art_balloon_pos){
        $balloon_pos = 's2s_balloon_top';
    }else{
        $balloon_pos = 's2s_balloon_bottom';
    }

    $jversion = new JVersion();
    if($jversion->RELEASE <= "2.5"){
        $html .= '<div style="clear:both;"></div>';
    }

    $html .= '<div class="s2s_supra_contenedor '.$basecss.' '.$cssalign.' '.$pos_fix.' '.$balloon_pos.'" '.$realign.'>';
        $html .= '<div class="s2s_contenedor '.$css_fill;

        if($s2sgroup){
            if($s2s_pos_fix && $s2s_art_fixed_mode=="s2s_fixed_vertical"){
                $html .= ' btn-group-vertical';
            }else{
                $html .= ' btn-group';
            }
        }
        $html .= '">';


        /**************************************/
        /**************** SOCIAL **************/
        /**************************************/

        $s2s_order = $params->get('s2s_order','0,1,2,3,4,5,6,7,8');

        //check number
        if(strlen($s2s_order) != 17){
            $s2s_order='0,1,2,3,4,5,6,7,8';
        }

        $order_array = explode(',',$s2s_order);

        $s2s_social = new s2s_social;

        //buttons after plus
        $afterplus = '';
        $param_mas_on = $params->get('mas_on',0);

        //mobile
        $jmobile_fw = new JApplicationWebClient;
        
        if ($jmobile_fw->mobile){
            $jmobile = true;
        }else{
            $jmobile = false;
        }

        //test if mobile active
        $param_mas_only_mobile = $params->get('mas_only_mob',0);
        
        $mas_on = false;
        if($param_mas_on){
            $mas_on = true;
            if($param_mas_only_mobile){
                if(!$jmobile){
                    $mas_on = false;
                }
            }
        }


        $cookie_button = false;
        $title = '';
        $full_link = '';

        foreach ($order_array as $key => $value) {

            if(!$mas_on){
                $afterplus = '';
            }

            if($value == '0'){
                /*TWITTER*/
                if($params->get('twitter_on',1)){
                    $html .= $s2s_social->getTwitter($params, $button_size, $buttons, $cookie_button, $title, $full_link, $idioma_twitter, $texts,$afterplus);
                }
            }elseif ($value == '1'){
                /*FACEBOOK*/
                if($params->get('facebook_on',1)){
                    $html .= $s2s_social->getFacebook($params, $button_size, $buttons, $cookie_button, $title, $full_link, $idioma_twitter, $texts,$afterplus, $jmobile);
                }
            }elseif ($value == '2'){
                /*PINTEREST*/
                if($params->get('pinterest_on',1)){
                    $html .= $s2s_social->getPinterest($params, $button_size, $buttons, $cookie_button, $title, $full_link, $idioma_twitter, $texts,$afterplus);
                }
            }elseif ($value == '3'){
                /*LINKEDIN*/
                if($params->get('linkedin_on',1)){
                    $html .= $s2s_social->getLinkedin($params, $button_size, $buttons, $cookie_button, $title, $full_link, $idioma_twitter, $texts,$afterplus);
                }
            }elseif ($value == '4'){
                /*GOOGLE PLUS*/
                if($params->get('gplus_on',1)){
                    $html .= $s2s_social->getGPlus($params, $button_size, $buttons, $cookie_button, $title, $full_link, $idioma_twitter, $texts,$afterplus);
                }
            }elseif ($value == '5'){
                /*WAPP*/
                if($params->get('wapp_on',0)){
                    $html .= $s2s_social->getWapp($params, $button_size, $buttons, $cookie_button, $title, $full_link, $idioma_twitter, $texts,$afterplus);
                }
            }elseif ($value == '6'){
                /*TUMBLR*/
                if($params->get('tumblr_on',1)){
                    $html .= $s2s_social->getTumblr($params, $button_size, $buttons, $cookie_button, $title, $full_link, $idioma_twitter, $idioma, $texts,$afterplus);
                }
            }elseif ($value == '7'){
                /*vk*/
                if($params->get('vk_on',0)){
                    $html .= $s2s_social->getVk($params, $button_size, $buttons, $cookie_button, $title, $full_link, $idioma_twitter, $idioma, $texts, $context, $article,$afterplus);  
                }
            }elseif ($value == '8'){
                /*MORE*/
                $afterplus = 'afterplus';
                if($params->get('mas_on',0)){
                    //$html .= $s2s_social->getMas($params, $button_size, $buttons, $cookie_button, $title, $full_link, $idioma_twitter, $idioma, $texts, $context, $article);        
                }
            }
        }

        //boton +
        if($mas_on){
            $html .= $s2s_social->getMas($params, $button_size, $buttons, $cookie_button, $title, $full_link, $idioma_twitter, $idioma, $texts, $context, $article);        
        }



//OPENGRAPH
        //require_once('features/opengraph.php');


            /*cierro contenedor*/   
            $html .= '</div>';
        $html .= '</div>';
        //$html .=  $s2s_debug_msg;


//CTA
        //require_once('features/cta.php');


//DEBUG
    /*
        $param_s2s_debug = $params->get('social2s_debug', 0);
        if($param_s2s_debug){
            
            $html .= '<table class="table table-striped table-condensed table-bordered"><thead><tr class="alert alert-success"><th><h4><i class="fa fa-bug"></i> Debug</h4></th></tr></thead>';
                foreach ($this->s2s_debug as $key => $value) {
                    $html .= '<tr><td>'.$value.'</td></tr>';
                }
                
            $html .= '</table>';

            //vacío el array del debug
            unset($this->s2s_debug);
        }
    */


//OPENGRAPH DEBUG
    /*
        //opengraph debug
        $s2s_og = $params->get('opengraph', 1);
        $uri = JFactory::getURI();
        $absolute_url = $uri->toString();
        if($context=='com_content.article' || $context=='com_virtuemart.productdetails'){
            $s2s_og_debug = $params->get('s2s_og_debug', 0);
            if($s2s_og_debug && $s2s_og){
                
                $html .= '<table class="table table-striped table-condensed table-bordered"><thead><tr class="alert alert-info"><th><h4><i class="fa fa-bug"></i> OpenGraph debug</h4> </th></tr></thead>';
                    foreach ($this->s2s_og_debug as $key => $value) {
                        $html .= '<tr><td>'.$value.'</td></tr>';
                    }
                    $html .= '<tr><td>
                        <div class="btn-group">
                            <a class="btn btn-primary" href="https://developers.facebook.com/tools/debug/og/object/?q='.$absolute_url.'" target="_blank">test OpenGraph</a>
                            <a class="btn btn-info" href="https://cards-dev.twitter.com/validator" target="_blank">test twitter cards</a>
                        </div></td></tr>';

                $html .= '</table>';

                //vacío el array del debug
                //unset($this->s2s_debug);

            }
        }

    */







        return $html;
    }

    public function getButtons(&$params,$context){

        $s2s_pro_variables = $params->get('s2s_pro_variables', 0);

        if($context=='com_content.category' || $context=='com_content.featured'){
            /*CATEGORY params*/
            $s2s_button_style = $params->get('s2s_cat_button_style', 0);
            $fontawesome_sign = $params->get('s2s_cat_fontawesome_sign', 0);
            $show_text = $params->get('s2s_cat_show_text', 1);
            $s2sgroup = $params->get('s2s_cat_group', 0);
            //check SAME
            if($this->params->get('s2s_takefromarticle')){
                /*ARTICLE params*/
                $s2s_button_style = $params->get('s2s_art_button_style', 0);
                $fontawesome_sign = $params->get('s2s_art_fontawesome_sign', 0);
                $show_text = $params->get('s2s_art_show_text', 1);
                $s2sgroup = $params->get('s2s_art_group', 0);
            }
        }elseif($context=='com_content.article' || $context == 'com_virtuemart.productdetails' || $context == 'jevents'){
            /*ARTICLE params*/
            $s2s_button_style = $params->get('s2s_art_button_style', 0);
            $fontawesome_sign = $params->get('s2s_art_fontawesome_sign', 0);
            $show_text = $params->get('s2s_art_show_text', 1);
            $s2sgroup = $params->get('s2s_art_group', 0);
        }

        /*VARIABLES*/
        if($s2s_pro_variables){
            //SHOW TEXT
            $var_s2s_show_text = JFactory::getApplication()->input->getInt('s2s_show_text');
            if($var_s2s_show_text){
                $show_text=true;
            }
        }


        //CHECK VIRTUEMART

        /*paths*/
        $images_path = JURI::base().'plugins/content/social2s/assets/';

        /*signs*/
        if($fontawesome_sign){
            $fontawesome_sign_value = '-square';
        }else{
            $fontawesome_sign_value = '';
        }

        $texts = $this->getTexts($show_text);

        $buttons = array();

        /*FONTAWESOME*/
        if($s2s_button_style == '1'){
            $buttons['twitter'] = '<i class="fa fa-twitter'.$fontawesome_sign_value.'">  '.$texts['twitter'].'</i>';

            $buttons['facebook'] = '<i class="fa fa-facebook'.$fontawesome_sign_value.'">  '.$texts['facebook'].'</i>';

            $buttons['pinterest'] = '<i class="fa fa-pinterest'.$fontawesome_sign_value.'">  '.$texts['pinterest'].'</i>';

            $buttons['linkedin'] = '<i class="fa fa-linkedin'.$fontawesome_sign_value.'">  '.$texts['linkedin'].'</i>';

            $buttons['gplus'] = '<i class="fa fa-google-plus'.$fontawesome_sign_value.'">  '.$texts['gplus'].'</i>';

            $buttons['wapp'] = '<i class="fa fa-whatsapp">  '.$texts['wapp'].'</i>';

            $buttons['tumblr'] = '<i class="fa fa-tumblr'.$fontawesome_sign_value.'">  '.$texts['tumblr'].'</i>';

            $buttons['vk'] = '<i class="fa fa-vk">  '.$texts['vk'].'</i>';

        /*IMAGES*/
        }else if($s2s_button_style == '2'){
            $buttons['twitter'] = '<img src="'.$images_path.'twitter.png" alt="twitter button"/>'.$texts['twitter'];    

            $buttons['facebook'] = '<img src="'.$images_path.'facebook.png" alt="facebook button"/>'.$texts['facebook'];    

            $buttons['pinterest'] = '<img src="'.$images_path.'pinterest.png" alt="pinterest button"/>'.$texts['pinterest'];    

            $buttons['linkedin'] = '<img src="'.$images_path.'linkedin.png" alt="linkedin button" />'.$texts['linkedin'];   

            $buttons['gplus'] = '<img src="'.$images_path.'gplus.png" alt="gplus button"/>'.$texts['gplus'];

            $buttons['wapp'] = '<img src="'.$images_path.'wapp.png" alt="whatsapp button"/>'.$texts['wapp'];

            $buttons['tumblr'] = '<img src="'.$images_path.'tumblr.png" alt="tumblr button"/>'.$texts['tumblr'];

            $buttons['vk'] = '<img src="'.$images_path.'vk.png" alt="vk button"/>'.$texts['vk'];
        }else{
            /*default*/
            $buttons['twitter'] = $texts['twitter'];
            $buttons['facebook'] = $texts['facebook'];
            $buttons['pinterest'] = $texts['pinterest'];
            $buttons['linkedin'] = $texts['linkedin'];
            $buttons['gplus'] = $texts['gplus'];
            $buttons['wapp'] = $texts['wapp'];
            $buttons['tumblr'] = $texts['tumblr'];
            $buttons['vk'] = $texts['vk'];
        }

        return $buttons;
    }

    public function getTexts($show_text){
        $texts = array();

        if($show_text){
            $texts['twitter'] = '<span class="s2s_social_text">'.JText::_('SOCIAL2S_TWITTER').'</span>';
            $texts['facebook'] = '<span class="s2s_social_text">'.JText::_('SOCIAL2S_FACEBOOK').'</span>';
            $texts['pinterest'] = '<span class="s2s_social_text">'.JText::_('SOCIAL2S_PINTEREST').'</span>';
            $texts['linkedin'] = '<span class="s2s_social_text">'.JText::_('SOCIAL2S_LINKEDIN').'</span>';
            $texts['gplus'] = '<span class="s2s_social_text">'.JText::_('SOCIAL2S_GPLUS').'</span>';
            $texts['wapp'] = '<span class="s2s_social_text">'.JText::_('SOCIAL2S_WAPP').'</span>';
            $texts['tumblr'] = '<span class="s2s_social_text">'.JText::_('SOCIAL2S_TUMBLR').'</span>';
            $texts['vk'] = '<span class="s2s_social_text">'.JText::_('SOCIAL2S_VK').'</span>';
        }else{
            $texts['twitter'] = "";
            $texts['facebook'] = "";
            $texts['pinterest']= "";
            $texts['linkedin'] = "";
            $texts['gplus'] = "";
            $texts['wapp'] = "";
            $texts['tumblr'] = "";
            $texts['vk']  = "";
        }
        return $texts;
    }











}
