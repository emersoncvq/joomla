<?php
class s2s_tmpl
{

    public static $s2s_obj;

    //style
    public static $s2s_styles;

    //buttons
    public static $s2s_buttons;

    //check loaded scripts
    public static $s2s_loaded_scripts;



    public function __construct()
    {
        self::$s2s_obj = PlgContentSocial2s::$s2s_obj;
    }

    public static function init($s2s_obj, $article)
    {
        self::$s2s_obj = $s2s_obj;

        self::$s2s_obj->plugin_path = JURI::base() . 'plugins/content/social2s';
        self::$s2s_obj->images_path = JURI::base() . 'media/plg_social2s/assets/';
        self::$s2s_obj->media_path  = JURI::base() . 'media/plg_social2s';

        if (!class_exists('ContentHelperRoute')) {
            require_once JPATH_SITE . '/components/com_content/helpers/route.php';
        }



        // prepare DATA
        self::prepareArticle($article);

        //MOBILE
        self::getMobile();

        //SCRIPTS
        self::getScripts();

        //LANGUAGE
        self::getLanguage();

        //STYLES
        PlgContentSocial2s::$s2s_obj->styles = self::getStyles();

        //TEMPLATE
        $tmpl = self::getTemplate();

        //BUTTONS
        self::$s2s_buttons = self::getButtons();

        //LINKS
        //self::getLinks();

        //VARIABLES
        self::getVariables();

        //COOKIE BUTTON
        PlgContentSocial2s::$s2s_obj->cookie_button = self::getCookieButton();

        //CLASSES
        self::$s2s_obj->supraclasses = self::getSupraClasses();

        //SOCIAL
        $social = self::getSocial();

        //HTML
        $html = self::getHtml($social);

        return $html.$tmpl;

    }

    /**
     * [prepareArticle description]
     * @param  [type] $article [description]
     * @return [type]          [description]
     */
    public static function prepareArticle($article)
    {

        if (isset($article->catid)) {
            self::$s2s_obj->catid = $article->catid;
        }

        if (isset($article->id)) {
            self::$s2s_obj->id = $article->id;
        }

        if (isset($article->alias)) {
            self::$s2s_obj->alias = JFilterOutput::stringURLSafe($article->title);
        }

        if (isset($article->title)) {
            self::$s2s_obj->title = $article->title;
        }

        if (isset($article->slug)) {
            self::$s2s_obj->slug = $article->slug;
        }




        $context = self::$s2s_obj->context;
        $uri     = JFactory::getURI();


        //    /////////////////////////////////
        //    //////    DEFAULT DATA    ///////
        //    /////////////////////////////////

        $description     = '';
        $app             = JFactory::getApplication();
        $metadescription = $app->getCfg('MetaDesc');
        $image           = '';
        $jinput = $app ->input;

        $s2s_images = new s2s_images;

        /////////////////////////////////
        //////      VIRTUEMART    ///////
        /////////////////////////////////
        if ($context == 'com_virtuemart.productdetails') {

            $vm_link   = str_replace(JURI::base(), "", $uri->toString());
            $link      = JRoute::_($vm_link);
            $full_link = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}/{$_SERVER['REQUEST_URI']}";

            $description = self::_descriptionFormatted($article->product_desc);

            $article->title = $article->product_name;


            
            //IMAGES
            $images = $s2s_images::getImagesVirtuemart($article);


        /////////////////////////////////
        //////       JEVENTS      ///////
        /////////////////////////////////
        } elseif ($context == 'com_jevents' || $context == 'com_jevents.') {
          
           
            $option = $jinput->get('option');
            $task = $jinput->get('task');
            $evid = $jinput->get('evid');
            $Itemid = $jinput->get('Itemid');
            $year = $jinput->get('year');
            $month = $jinput->get('month');
            $day = $jinput->get('day');
            $title = $jinput->get('title');
            $uid = $jinput->get('uid');

            $url = 'index.php?option=com_jevents&task='.$task.'&evid='.$article->_ev_id.'&Itemid='.$Itemid.'&year='.$year.'&month='.$month.'&day='.$day.'&title='.$title.'&uid='.$uid;
            
            $url = JUri::getInstance();

            //$full_link = JURI::base() . substr(JRoute::_($url, false), strlen(JURI::base(true)) + 1);
            $full_link = $url;

            $description = self::_descriptionFormatted($article->_description);

            //$link = substr(JRoute::_('index.php?option=com_jevents&task=icalrepeat.detail&evid='.$article->_ev_id, false), strlen(JURI::base(true)) + 1);
          
            //$link =  \Joomla\CMS\Router\Route::_(substr(JRoute::_($url, false), strlen(JURI::base(true)) + 1), $xhtml = false, $ssl = null);
            //$link =  substr(JRoute::_($url, false), strlen(JURI::base(true)) + 1);
            $link =  substr(JRoute::_($url, false), strlen(JURI::base()));
            
            //$link =  $url;

            $images = $s2s_images::getImagesJevents($article);



            /////////////////////////////////
            //////         K2         ///////
            /////////////////////////////////
        } elseif ($context == 'com_k2.item' || $context == 'com_k2.itemlist') {

            //withOUT slug
            $link = JRoute::_(K2HelperRoute::getItemRoute(self::$s2s_obj->id, self::$s2s_obj->catid));

            //with slug
            $full_link = JURI::base() . substr(JRoute::_(K2HelperRoute::getItemRoute(self::$s2s_obj->id, self::$s2s_obj->catid)), strlen(JURI::base(true)) + 1);

            //DESCRIPTION
            $description = self::_descriptionFormatted($article->introtext);

            //METADESCRIPTION
            if ($article->metadesc != '') {
                $metadescription = $article->metadesc;
            }

            //IMAGES
            $images = $s2s_images::getImagesK2($article);

            /////////////////////////////////
            //////         SP         ///////
            /////////////////////////////////
        } elseif ($context == 'com_sppagebuilder.page' || $context == 'com_spsoccer.page') {
            //with slug
            $link = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}/{$_SERVER['REQUEST_URI']}";

            //with slug
            $full_link = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}/{$_SERVER['REQUEST_URI']}";

            /////////////////////////////////
            //////       JOOMLA       ///////
            /////////////////////////////////
        } else {
            //LINKS
            $link      = JRoute::_(ContentHelperRoute::getArticleRoute(self::$s2s_obj->slug, self::$s2s_obj->catid));
            $full_link = JURI::base() . substr(JRoute::_(ContentHelperRoute::getArticleRoute(self::$s2s_obj->slug, self::$s2s_obj->catid)), strlen(JURI::base(true)) + 1);

            //DESCRIPTION
            if (isset($article->introtext)) {
                $description = self::_descriptionFormatted($article->introtext);
            }

            //METADESCRIPTION
            if ($article->metadesc != '') {
                $metadescription = $article->metadesc;
            }

            //IMAGES
            $images = $s2s_images::getImagesArticle($article);

        }


        self::$s2s_obj->title           = $article->title;
        self::$s2s_obj->safetitle       = json_encode($article->title);
        self::$s2s_obj->description     = $description;
        self::$s2s_obj->metadescription = $metadescription;


        self::$s2s_obj->link      = $link;
        self::$s2s_obj->full_link = $full_link;

        self::$s2s_obj->images = $images;

        $uri                         = JFactory::getURI();
        self::$s2s_obj->url          = $uri;
        self::$s2s_obj->absolute_url = $uri->toString();

        PlgContentSocial2s::updateS2sDebug('Title', self::$s2s_obj->title, 'info', 'file-text-o');
        PlgContentSocial2s::updateS2sDebug('Description', self::$s2s_obj->description, 'info', 'file-text-o');
        PlgContentSocial2s::updateS2sDebug('Link', $link, 'info', 'link');
        PlgContentSocial2s::updateS2sDebug('Full_link', $full_link, 'info', 'link');

        //update main s2s_object
        PlgContentSocial2s::$s2s_obj = self::$s2s_obj;

    }

    /**
     * [_descriptionFormatted format descriptions]
     * @param  [type] $text [description]
     * @return [type]       [html]
     */
    private static function _descriptionFormatted($text)
    {

        $regex         = '#{(.*)}#iU';
        $regex2        = '#{/(.*)}#iU';
        $output        = "";
        $first_plugin  = preg_replace($regex, $output, $text);
        $second_plugin = preg_replace($regex2, $output, $first_plugin);

        $description = implode(' ', array_slice(explode(' ', strip_tags($second_plugin, '<br>')), 0, 70));

        $description = str_replace(array("\r", "\n"), '', $description);

        $description = html_entity_decode($description, ENT_COMPAT, 'UTF-8');

        $description = str_replace('<br />', '', $description);

        return $description;
    }

    /**
     * [MOBILE checker]
     * @return none
     */
    public static function getMobile()
    {

        $jmobile_fw = new JApplicationWeb;

        if ($jmobile_fw->client->mobile) {
            self::$s2s_obj->jmobile = true;
        } else {
            self::$s2s_obj->jmobile = false;
        }
        $s2s_mobile = self::$s2s_obj->jmobile;

        $doc = JFactory::getDocument();
        $doc->addScriptDeclaration("var s2s_mobile = '{$s2s_mobile}';");
    }

    /**
     * [SCRIPTS loader]
     * @return none
     */
    public static function getScripts()
    {
        $jversion    = new JVersion();
        $document    = JFactory::getDocument();
        $plugin_path = self::$s2s_obj->plugin_path;
        $images_path = self::$s2s_obj->images_path;
        $media_path  = self::$s2s_obj->media_path;

        $params = self::$s2s_obj->params;

        //JQUERY
        if ($params->get('load_jquery', '1') == '1') {
            $s2s_jquery_version = $params->get('s2s_jquery_version', '0');
            if ($s2s_jquery_version == '0') {

                JHtml::_('jquery.framework', true, true);
                PlgContentSocial2s::updateS2sDebug('JQUERY', 'DEFAULT LOADED', 'success', 'file-code-o');

            } else {
                $document->addScript($media_path . '/js/jquery/' . $s2s_jquery_version . '.js', 'text/javascript');
                PlgContentSocial2s::updateS2sDebug('JQUERY', $s2s_jquery_version . ' LOADED', 'success', 'file-code-o');
            }
        } else {
            PlgContentSocial2s::updateS2sDebug('JQUERY', 'deactivated', 'info', 'file-code-o');
        }


        //MINIFY
        if ($params->get('social2s_minify_debug', '1') == '1') {
            PlgContentSocial2s::updateS2sDebug('MINIFY JS', 'deactivated: social2s.js', 'info', 'file-archive-o');
            //$document->addScript($media_path . '/js/social2s.js');
            $jspath = '/js/social2s.js';
        } else {
            PlgContentSocial2s::updateS2sDebug('MINIFY JS', 'ACTIVATED social2s.min.js'.$media_path . '/js/social2s.min.js', 'success', 'file-archive-o');
            //$document->addScript($media_path . '/js/social2s.min.js','text/javascript',true, true);
            $jspath = '/js/social2s.min.js';
        }

        //if is already loaded
        
        if(self::$s2s_obj->s2sjs_file_loaded == false){
           $document->addScriptDeclaration("
                (function(d){
                  var f = d.getElementsByTagName('SCRIPT')[0], p = d.createElement('SCRIPT');
                  p.type = 'text/javascript';
                  p.async = false;
                  p.defer = true;
                  p.src = '".$media_path.$jspath."';
                  f.parentNode.insertBefore(p, f);
                }(document));
            ");
        }
        //if is already loaded
        self::$s2s_obj->s2sjs_file_loaded = 'true';

        //echo '<script async defer src="https://connect.facebook.net/en_US/sdk.js"></script>';


    }

    /**
     * [LANGUAGE]
     * @return none
     */
    public static function getLanguage()
    {

        $params = self::$s2s_obj->params;

        $idioma        = $params->get('s2s_language', '0');
        $idioma_joomla = JFactory::getLanguage()->getTag();
        // Twitter and Gplus
        if ($idioma != '0') {
            $idioma_twitter = substr($idioma, 0, 2);
        } else {
            $idioma_twitter = substr(str_replace('-', '_', JFactory::getLanguage()->getTag()), 0, 2);
            $idioma         = str_replace('-', '_', JFactory::getLanguage()->getTag());
        }

        $lang           = JFactory::getLanguage();
        $lang_direction = $lang->get('rtl');

        if ($lang_direction == 0) {
            $lang_direction = 'lang_directon_right';
        } else {
            $lang_direction = 'lang_directon_left';
        }
        PlgContentSocial2s::$s2s_obj->lang           = $idioma;
        PlgContentSocial2s::$s2s_obj->lang_twitter   = $idioma_twitter;
        PlgContentSocial2s::$s2s_obj->lang_direction = $lang_direction;

        PlgContentSocial2s::updateS2sDebug('LANGUAGE', 'lang: ' . $idioma . ' | twitter: ' . $idioma_twitter . ' | direction: ' . $lang_direction, 'info', 'language');
    }

    /**
     * [STYLES]
     * @return [type] [object]
     */
    public static function getStyles()
    {

        $params  = self::$s2s_obj->params;
        $context = self::$s2s_obj->context;

        //ITEM VIEW
        $style   = $params->get('s2s_art_style', 'default');
        $align   = $params->get('s2s_art_button_align', 'left');
        $fill    = $params->get('s2s_art_fill', '0');
        $group   = $params->get('s2s_art_group', '1');
        $size    = $params->get('s2s_art_size', 's2s-btn-default');
        $sign    = $params->get('s2s_art_font_style', 0);
        $text    = $params->get('s2s_art_show_text', 0);
        $balloon = $params->get('s2s_art_balloon_pos', '0');

        //CAT VIEW
        if ($context == 'com_k2.itemlist' ||
            $context == 'com_content.category' ||
            $context == 'com_content.featured') {
            if (!$params->get('s2s_takefromarticle')) {
                $style   = $params->get('s2s_cat_style', 'default');
                $align   = $params->get('s2s_cat_button_align', 'left');
                $fill    = $params->get('s2s_cat_fill', '0');
                $group   = $params->get('s2s_cat_group', 0);
                $size    = $params->get('s2s_cat_size', 's2s-btn-default');
                $sign    = $params->get('s2s_cat_font_style', 0);
                $text    = $params->get('s2s_cat_show_text', 0);
                $balloon = $params->get('s2s_cat_balloon_pos', '0');
            }
        }

        $s2s_group          = 's2s-btn-split';
        $s2s_pos_fix        = $params->get('s2s_art_fixed', 0);
        $s2s_art_fixed_mode = $params->get('s2s_art_fixed_mode', 's2s_fixed_horizontal');
        if ($group) {
            if ($s2s_pos_fix && $s2s_art_fixed_mode == "s2s_fixed_vertical") {
                $s2s_group = ' s2s-btn-group-vertical';
            } else {
                $s2s_group = ' s2s-btn-group';
            }
        }

        $styles = (object) [
            'style'   => $style,
            'align'   => $align,
            'fill'    => $fill,
            'group'   => $s2s_group,
            'size'    => $size,
            'sign'    => $sign,
            'text'    => $text,
            'balloon' => $balloon,
        ];

        return $styles;
    }

    /**
     * [TEMPLATE]
     * @return none
     */
    public static function getTemplate()
    {
        $params      = self::$s2s_obj->params;
        $document    = JFactory::getDocument();
        $plugin_path = self::$s2s_obj->plugin_path;
        $media_path  = self::$s2s_obj->media_path;

        $load_base  = $params->get('social2s_load_base', true);
        $base       = $params->get('social2s_base', '0');
        $minify     = $params->get('social2s_minify_debug', 0);
        $load_style = $params->get('s2s_load_style', 1);
        $load_fo    = $params->get('load_s2sfont', 1);
        
        $s2s_defer_css    = $params->get('social2s_css_defer', 1);

        $tmpl= '';

        $basecss = "";
        if ($load_base) {

            $basecss = "s2sdefault";
            if ($base == "1") {
                $basecss = "s2smodern";
            }
            self::$s2s_obj->behavior = $basecss;

            //minify
            $min = ($minify == '0' ? '.min' : '');

            if(self::$s2s_obj->s2scss_file_loaded == false){

                if($s2s_defer_css){
                   
                    $css_path = $media_path . '/css/behavior/' . $basecss .'.css';

                        $tmpl .= '<noscript id="deferred-styles_s2s_basecss">
                          <link rel="stylesheet" type="text/css" href="'.$css_path.'"/>
                        </noscript>';

                        $tmpl .= '<script>
                          var loadDeferredStyles_s2s_basecss = function() {
                            var addStylesNode = document.getElementById("deferred-styles_s2s_basecss");
                            var replacement = document.createElement("div");
                            replacement.innerHTML = addStylesNode.textContent;
                            document.body.appendChild(replacement)
                            addStylesNode.parentElement.removeChild(addStylesNode);
                          };
                          var raf = requestAnimationFrame || mozRequestAnimationFrame ||
                              webkitRequestAnimationFrame || msRequestAnimationFrame;
                          if (raf) raf(function() { window.setTimeout(loadDeferredStyles_s2s_basecss,50); });
                          else window.addEventListener(\'load\', loadDeferredStyles_s2s_basecss);
                        </script>';

                        PlgContentSocial2s::updateS2sDebug('MINIFY base CSS', 'DEFERRED: '.$basecss.'.css', 'info', 'file-archive-o');

                }else{
                    $document->addStyleSheet($media_path . '/css/behavior/' . $basecss.'.css', 'text/css');
                    PlgContentSocial2s::updateS2sDebug('MINIFY base CSS', $basecss.'.css', 'info', 'file-archive-o');
                }
            }




        }

        //Style file
        if ($load_style) {

            if(self::$s2s_obj->s2scss_file_loaded == false){

                if($s2s_defer_css){

                    $css_path = $media_path . '/css/styles/' . PlgContentSocial2s::$s2s_obj->styles->style . '.css';

                        $tmpl .=  '<noscript id="deferred-styles_s2s_style">
                          <link rel="stylesheet" type="text/css" href="'.$css_path.'"/>
                        </noscript>';

                        $tmpl .=  '<script>
                          var loadDeferredStyles_s2s_style = function() {
                            var addStylesNode = document.getElementById("deferred-styles_s2s_style");
                            var replacement = document.createElement("div");
                            replacement.innerHTML = addStylesNode.textContent;
                            document.body.appendChild(replacement)
                            addStylesNode.parentElement.removeChild(addStylesNode);
                          };
                          var raf = requestAnimationFrame || mozRequestAnimationFrame ||
                              webkitRequestAnimationFrame || msRequestAnimationFrame;
                          if (raf) raf(function() { window.setTimeout(loadDeferredStyles_s2s_style, 100); });
                          else window.addEventListener(\'load\', loadDeferredStyles_s2s_style);
                        </script>';

                        PlgContentSocial2s::updateS2sDebug('LOAD STYLE CSS', 'DEFERRED: '.$media_path . '/css/styles/' . PlgContentSocial2s::$s2s_obj->styles->style . '.css', 'success', 'css3');
                }else{
                   $document->addStyleSheet($media_path . '/css/styles/' . PlgContentSocial2s::$s2s_obj->styles->style . '.css', 'text/css');
                    PlgContentSocial2s::updateS2sDebug('LOAD STYLE CSS', 'LOADED', 'success', 'css3');
                }
            }

 
        }

        //font css file
        if ($load_fo) {
            //minify


            if(self::$s2s_obj->s2scss_file_loaded == false){
                $min = ($minify == '0' ? '.min' : '');

                if($s2s_defer_css){

                    $css_path = $media_path . '/css/s2sfont'.$min.'.css';

                        $tmpl .=  '<noscript id="deferred-styles_s2s_font">
                          <link rel="stylesheet" type="text/css" href="'.$css_path.'"/>
                        </noscript>';

                        $tmpl .=  '<script>
                          var loadDeferredStyles_s2s_font = function() {
                            var addStylesNode = document.getElementById("deferred-styles_s2s_font");
                            var replacement = document.createElement("div");
                            replacement.innerHTML = addStylesNode.textContent;
                            document.body.appendChild(replacement)
                            addStylesNode.parentElement.removeChild(addStylesNode);
                          };
                          var raf = requestAnimationFrame || mozRequestAnimationFrame ||
                              webkitRequestAnimationFrame || msRequestAnimationFrame;
                          if (raf) raf(function() { window.setTimeout(loadDeferredStyles_s2s_font, 150); });
                          else window.addEventListener(\'load\', loadDeferredStyles_s2s_font);
                        </script>';

                         PlgContentSocial2s::updateS2sDebug('LOAD S2SFONT CSS', 'DEFERRED: s2sfont'.$min.'.css', 'success', 'file-archive-o');
                }else{
                    $document->addStyleSheet($media_path . '/css/s2sfont'.$min.'.css', 'text/css');
                    PlgContentSocial2s::updateS2sDebug('LOAD S2SFONT CSS', 's2sfont'.$min.'.css', 'success', 'file-archive-o');
                }
            }
        }

        self::$s2s_obj->s2scss_file_loaded = true;

        return $tmpl;
    }

    /**
     * [BUTTONS]
     * @return [type] [array]
     */
    public static function getButtons()
    {
        $context = self::$s2s_obj->context;
        $params  = self::$s2s_obj->params;
        $styles  = PlgContentSocial2s::$s2s_obj->styles;

        //paths
        $images_path = JURI::base() . 'media/plg_social2s/assets/';
        $media_path  = JURI::base() . 'media/plg_social2s';

        //font style
        $font_style = $styles->sign;
        if ($styles->sign == '0') {
            $font_style = '';
        }

        $texts = self::getTexts($styles->text);

        $buttons = array();

        $s2s_button_style = $params->get('s2s_button_style', '1');
        //FONTAWESOME <span class="s2s_text">
        //
        //OLD FONTAWESOME V3
        /*if($s2s_button_style == '1'){
        $buttons['twitter'] = '<i class="fa fa-twitter'.$fa_sign_value.'"></i>  '.$texts['twitter'];
        $buttons['facebook'] = '<i class="fa fa-facebook'.$fa_sign_value.'"></i>  '.$texts['facebook'];
        $buttons['pinterest'] = '<i class="fa fa-pinterest'.$fa_sign_value.'"></i>  '.$texts['pinterest'];
        $buttons['linkedin'] = '<i class="fa fa-linkedin'.$fa_sign_value.'"></i>  '.$texts['linkedin'];
        $buttons['gplus'] = '<i class="fa fa-google-plus'.$fa_sign_value.'"></i>  '.$texts['gplus'];
        $buttons['wapp'] = '<i class="fa fa-whatsapp"></i>  '.$texts['wapp'];
        $buttons['tgram'] = '<i class="fa fa-telegram"></i>  '.$texts['tgram'];

        $buttons['flipb'] = '<i class="fa fa-bookmark"></i>  '.$texts['tgram'];
        $buttons['delio'] = '<i class="fa fa-delicious"></i>  '.$texts['delio'];
        $buttons['tumblr'] = '<i class="fa fa-tumblr'.$fa_sign_value.'"></i>  '.$texts['tumblr'];
        $buttons['vk'] = '<i class="fa fa-vk"></i>  '.$texts['vk'];
         */

        if ($s2s_button_style == '1') {
            $buttons['twitter']   = '<i class="s2sfo fo-twitter' . $font_style . '"></i>  ' . $texts['twitter'];
            $buttons['facebook']  = '<i class="s2sfo fo-facebook' . $font_style . '"></i>  ' . $texts['facebook'];
            $buttons['pinterest'] = '<i class="s2sfo fo-pinterest' . $font_style . '"></i>  ' . $texts['pinterest'];
            $buttons['linkedin']  = '<i class="s2sfo fo-linkedin' . $font_style . '"></i>  ' . $texts['linkedin'];
            $buttons['gplus']     = '<i class="s2sfo fo-google-plus' . $font_style . '"></i>  ' . $texts['gplus'];
            $buttons['wapp']      = '<i class="s2sfo fo-whatsapp' . $font_style . '"></i>  ' . $texts['wapp'];
            $buttons['tgram']     = '<i class="s2sfo fo-tgram' . $font_style . '"></i>  ' . $texts['tgram'];
            $buttons['flipb']     = '<i class="s2sfo fo-flipb' . $font_style . '"></i>  ' . $texts['flipb'];
            $buttons['vk']        = '<i class="s2sfo fo-vk' . $font_style . '"></i>  ' . $texts['vk'];
            $buttons['delio']     = '<i class="s2sfo fo-delio' . $font_style . '"></i>  ' . $texts['delio'];
            $buttons['tumblr']    = '<i class="s2sfo fo-tumblr' . $font_style . '"></i>  ' . $texts['tumblr'];
            $buttons['reddit']    = '<i class="s2sfo fo-reddit' . $font_style . '"></i>  ' . $texts['reddit'];
            $buttons['email']     = '<i class="s2sfo fo-email' . $font_style . '"></i>  ' . $texts['email'];

            //IMAGES
        } else if ($s2s_button_style == '2') {
            $buttons['twitter']   = '<img src="' . $images_path . 'png/twitter.png" alt="twitter button"/>' . $texts['twitter'];
            $buttons['facebook']  = '<img src="' . $images_path . 'png/facebook.png" alt="facebook button"/>' . $texts['facebook'];
            $buttons['pinterest'] = '<img src="' . $images_path . 'png/pinterest.png" alt="pinterest button"/>' . $texts['pinterest'];
            $buttons['linkedin']  = '<img src="' . $images_path . 'png/linkedin.png" alt="linkedin button" />' . $texts['linkedin'];
            $buttons['gplus']     = '<img src="' . $images_path . 'png/gplus.png" alt="gplus button"/>' . $texts['gplus'];
            $buttons['wapp']      = '<img src="' . $images_path . 'png/wapp.png" alt="whatsapp button"/>' . $texts['wapp'];
            $buttons['tgram']     = '<img src="' . $images_path . 'png/telegram.png" alt="telegram button"/>' . $texts['tgram'];
            $buttons['flipb']     = '<img src="' . $images_path . 'png/flipb.gif" alt="flipboard button"/>' . $texts['flipb'];
            $buttons['delio']     = '<img src="' . $images_path . 'png/delio.png" alt="delicious button"/>' . $texts['delio'];
            $buttons['tumblr']    = '<img src="' . $images_path . 'png/tumblr.png" alt="tumblr button"/>' . $texts['tumblr'];
            $buttons['reddit']    = '<img src="' . $images_path . 'png/reddit.png" alt="tumblr button"/>' . $texts['reddit'];
            $buttons['vk']        = '<img src="' . $images_path . 'png/vk.png" alt="vk button"/>' . $texts['vk'];
            $buttons['email']     = '<img src="' . $images_path . 'png/email.png" alt="email button"/>' . $texts['email'];
        } else {
            //default
            $buttons['twitter']   = $texts['twitter'];
            $buttons['facebook']  = $texts['facebook'];
            $buttons['pinterest'] = $texts['pinterest'];
            $buttons['linkedin']  = $texts['linkedin'];
            $buttons['gplus']     = $texts['gplus'];
            $buttons['wapp']      = $texts['wapp'];
            $buttons['tgram']     = $texts['tgram'];
            $buttons['flipb']     = $texts['flipb'];
            $buttons['delio']     = $texts['delio'];
            $buttons['tumblr']    = $texts['tumblr'];
            $buttons['reddit']    = $texts['reddit'];
            $buttons['vk']        = $texts['vk'];
            $buttons['email']     = $texts['email'];
        }

        return $buttons;
    }

    /**
     * [BUTTONS - TEXT]
     * @param  [type] $show_text [BOOL]
     * @return [type]            [ARRAY]
     */
    public static function getTexts($show_text)
    {
        $texts = array();
        if ($show_text) {
            $texts['twitter']   = '<span class="s2s_social_text">' . JText::_('SOCIAL2S_TWITTER') . '</span>';
            $texts['facebook']  = '<span class="s2s_social_text">' . JText::_('SOCIAL2S_FACEBOOK') . '</span>';
            $texts['pinterest'] = '<span class="s2s_social_text">' . JText::_('SOCIAL2S_PINTEREST') . '</span>';
            $texts['linkedin']  = '<span class="s2s_social_text">' . JText::_('SOCIAL2S_LINKEDIN') . '</span>';
            $texts['gplus']     = '<span class="s2s_social_text">' . JText::_('SOCIAL2S_GPLUS') . '</span>';
            $texts['wapp']      = '<span class="s2s_social_text">' . JText::_('SOCIAL2S_WAPP') . '</span>';
            $texts['tgram']     = '<span class="s2s_social_text">' . JText::_('SOCIAL2S_TGRAM') . '</span>';
            $texts['flipb']     = '<span class="s2s_social_text">' . JText::_('SOCIAL2S_FLIPB') . '</span>';
            $texts['delio']     = '<span class="s2s_social_text">' . JText::_('SOCIAL2S_DELIO') . '</span>';
            $texts['tumblr']    = '<span class="s2s_social_text">' . JText::_('SOCIAL2S_TUMBLR') . '</span>';
            $texts['reddit']    = '<span class="s2s_social_text">' . JText::_('SOCIAL2S_REDDIT') . '</span>';
            $texts['vk']        = '<span class="s2s_social_text">' . JText::_('SOCIAL2S_VK') . '</span>';
            $texts['email']     = '<span class="s2s_social_text">' . JText::_('SOCIAL2S_EMAIL') . '</span>';
        } else {
            $texts['twitter']   = "";
            $texts['facebook']  = "";
            $texts['pinterest'] = "";
            $texts['linkedin']  = "";
            $texts['gplus']     = "";
            $texts['wapp']      = "";
            $texts['tgram']     = "";
            $texts['flipb']     = "";
            $texts['delio']     = "";
            $texts['tumblr']    = "";
            $texts['reddit']    = "";
            $texts['vk']        = "";
            $texts['email']     = "";
        }
        return $texts;
    }

    /**
     * [VARIABLES inject variables in js]
     * @return [type] NONE
     */
    public static function getVariables()
    {
        $params = self::$s2s_obj->params;

        $doc = JFactory::getDocument();

        //injection
        $insert          = $params->get('s2s_insert', '0');
        $insert_position = $params->get('s2s_insert_position', '0');
        $insert_element  = $params->get('s2s_insert_element', '');

        $doc->addScriptDeclaration("var insert = '{$insert}';");
        $doc->addScriptDeclaration("var insert_position = '{$insert_position}';");
        $doc->addScriptDeclaration("var insert_element = '{$insert_element}';");

        //cookies
        $checkCookie = $params->get('s2s_stupid_cookie_on', '0');

        $doc->addScriptDeclaration("var s2s_checkCookie = '{$checkCookie}';");

        //language
        $s2s_lang_1tag      = self::$s2s_obj->lang;
        $s2s_lang_2tag      = self::$s2s_obj->lang_twitter;
        $s2s_lang_direction = self::$s2s_obj->lang_direction;

        $doc->addScriptDeclaration("var s2s_lang_1tag = '{$s2s_lang_1tag}';");
        $doc->addScriptDeclaration("var s2s_lang_2tag = '{$s2s_lang_2tag}';");
        $doc->addScriptDeclaration("var s2s_lang_direction = '{$s2s_lang_direction}';");

        //general
        $s2s_load_scripts_onload = $params->get('s2s_load_scripts_onload', '0');
        $s2s_context             = self::$s2s_obj->context;
        $s2s_debug               = $params->get('s2s_debug', 0);
        $s2s_version             = self::_getVersion();

        $doc->addScriptDeclaration("var s2s_load_scripts_onload = '{$s2s_load_scripts_onload}';");
        $doc->addScriptDeclaration("var s2s_context = '{$s2s_context}';");
        $doc->addScriptDeclaration("var s2s_debug = '{$s2s_debug}';");
        $doc->addScriptDeclaration("var s2s_version = '{$s2s_version}';");

        //pos fixed
        $s2s_art_mobile_min = $params->get('s2s_art_mobile_min', 978);
        $doc->addScriptDeclaration("var s2s_art_mobile_min = '{$s2s_art_mobile_min}';");

        //K2
        $s2s_k2_remove_social = $params->get('s2s_k2_remove_social', '1');
        $doc->addScriptDeclaration("var s2s_k2_remove_social = '{$s2s_k2_remove_social}';");

        //license
        $s2s_sha = $params->get('s2s_license_email', '');
        $doc->addScriptDeclaration("var s2s_sha = '{$s2s_sha}';");

        //CTA
        $s2s_cta_active = $params->get('s2s_cta_active', 0);
        if (self::$s2s_obj->context != 'com_content.article') {
            $s2s_cta_active = 0;
        }

        $s2s_cta_default = $params->get('s2s_cta_default', 'twitter');

        $doc->addScriptDeclaration("var s2s_cta_active = '{$s2s_cta_active}';");
        $doc->addScriptDeclaration("var s2s_cta_default = '{$s2s_cta_default}';");

        //COUNTS
        $twitter_b_count_hide = $params->get('twitter_b_count_hide', 1);
        $twitter_b_count      = $params->get('twitter_b_count', 0);
        $facebook_count_hide  = $params->get('facebook_count_hide', 1);
        $facebook_like_count  = $params->get('facebook_like_count', 0);
        $facebook_share_count = $params->get('facebook_share_count', 0);
        $facebook_total_count = $params->get('facebook_total_count', 0);
        $pinterest_count_hide = $params->get('pinterest_count_hide', 1);
        $pinterest_count      = $params->get('pinterest_count', 0);
        $linkedin_count_hide  = $params->get('linkedin_count_hide', 1);
        $linkedin_count       = $params->get('linkedin_count', 0);
        $gplus_b_count_hide   = $params->get('gplus_b_count_hide', 1);
        $gplus_b_count        = $params->get('gplus_b_count', 0);
        $tumblr_count         = $params->get('tumblr_count', 1);
        $tumblr_count_hide    = $params->get('tumblr_count_hide', 1);
        $vk_b_count_hide      = $params->get('s2s_vk_count_hide', 1);
        $vk_b_count           = $params->get('s2s_vk_count', 1);


        //TODO
        //disable counts v4.0.141 because I cant retrieve counts :/
        $twitter_b_count      = 0;
        $facebook_share_count = 0;
        $facebook_total_count = 0;
        $pinterest_count      = 0;
        $linkedin_count       = 0;
        $gplus_b_count        = 0;
        $tumblr_count         = 0;
        $vk_b_count           = 0;



        $doc->addScriptDeclaration("var twitter_b_count_hide = '{$twitter_b_count_hide}';");
        $doc->addScriptDeclaration("var twitter_b_count = '{$twitter_b_count}';");
        $doc->addScriptDeclaration("var facebook_count_hide = '{$facebook_count_hide}';");
        $doc->addScriptDeclaration("var facebook_like_count = '{$facebook_like_count}';");
        $doc->addScriptDeclaration("var facebook_share_count = '{$facebook_share_count}';");
        $doc->addScriptDeclaration("var facebook_total_count = '{$facebook_total_count}';");
        $doc->addScriptDeclaration("var pinterest_count_hide = '{$pinterest_count_hide}';");
        $doc->addScriptDeclaration("var pinterest_count = '{$pinterest_count}';");
        $doc->addScriptDeclaration("var linkedin_count_hide = '{$linkedin_count_hide}';");
        $doc->addScriptDeclaration("var linkedin_count = '{$linkedin_count}';");
        $doc->addScriptDeclaration("var gplus_b_count_hide = '{$gplus_b_count_hide}';");
        $doc->addScriptDeclaration("var gplus_b_count = '{$gplus_b_count}';");
        $doc->addScriptDeclaration("var tumblr_count = '{$gplus_b_count}';");
        $doc->addScriptDeclaration("var tumblr_count_hide = '{$gplus_b_count}';");
        $doc->addScriptDeclaration("var vk_b_count_hide = '{$vk_b_count_hide}';");
        $doc->addScriptDeclaration("var vk_b_count = '{$vk_b_count}';");

        //DATA
        $safefull_link = rawurlencode(self::$s2s_obj->full_link);
        $safetitle     = rawurlencode(self::$s2s_obj->title);

        $doc->addScriptDeclaration("var php_full_link = '{$safefull_link}';");
        $doc->addScriptDeclaration("var php_title = '{$safetitle}';");

    }

    /**
     * [_getVersion gets the plugin version]
     * @return [type] [string]
     */
    private static function _getVersion()
    {
        $db    = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query
            ->select(array('*'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('social2s'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('content'));
        $db->setQuery($query);
        $result         = $db->loadObject();
        $manifest_cache = json_decode($result->manifest_cache);
        if (isset($manifest_cache->version)) {
            return $manifest_cache->version;
        }
        return;
    }

    /**
     * [getCookieButton]
     * @return [type] [html]
     */
    public static function getCookieButton()
    {
        $params = self::$s2s_obj->params;
        /*cookie*/
        $checkCookie = $params->get('s2s_stupid_cookie_on', '0');
        $cookieLink  = $params->get('s2s_stupid_cookie_link', '0');
        $ccm_support = $params->get('s2s_stupid_cookie_ccm_support', '0');

        /*paths*/
        $images_path = JURI::base() . 'media/plg_social2s/assets/';

        /* Find article alias and catid */
        $db  = JFactory::getDBO();
        $sql = 'select alias, catid from #__content where id=' . $cookieLink;
        $db->setQuery($sql);
        $row   = $db->loadAssoc();
        $alias = $row['alias'];
        $catid = $row['catid'];

        $cookieLink_url = ContentHelperRoute::getArticleRoute($cookieLink . ':' . $alias, $catid);

        $muestra_cookies = false;
        if ($checkCookie == "1") {

            if ($ccm_support == "1") {
                $muestra_cookies = self::checkcookiecomponents();
            } else {
                if (!isset($_COOKIE['s2s_cookie']) || $_COOKIE['s2s_cookie'] == null || $_COOKIE['s2s_cookie'] != "1") {
                    $muestra_cookies = true;
                }
            }
        }

        PlgContentSocial2s::updateS2sDebug('COOKIE conf', 'Check:' . $checkCookie . ' | Link:' . $cookieLink . ' | External comp: ' . $ccm_support . ' | Show:' . $muestra_cookies, 'info', 'user-secret');
        PlgContentSocial2s::updateS2sDebug('COOKIE link', $cookieLink_url, 'info', 'user-secret');

        if ($muestra_cookies) {
            $cookie_button = '
				<aside class="s2s_cookie_contenedor">
					<span>' . JText::_('SOCIAL2S_COOKIES_PERMISSION') . '</span>
					<a class="s2s_cookie_button">' . JText::_('SOCIAL2S_COOKIES_ACCEPT') . '</a>
					<a class="s2s_cookie_information"><span><i  class="s2sfo fo-rplus_q"></i>' . JText::_('SOCIAL2S_COOKIES_INFO') . '</span></a>
					<div class="s2s_cookie_more_info">
						<p>
							<img class="s2s_cookie_eulogo" src="' . $images_path . 'social2s.svg" width="32" height="32" alt="dibuxo social2s ue cookies"/>
							' . JText::_('SOCIAL2S_COOKIES_MORE_INFO') . '
						</p>
						<p class="s2s_cookie_read_policy">' . JText::_('SOCIAL2S_COOKIES_READ_POLICY') . ' <a class="" href="' . $cookieLink_url . '">' . JText::_('SOCIAL2S_COOKIES_COOKIES_POLICY') . '</a></p>';

                        if($params->get('s2s_credits',1)){
						  $cookie_button .= '<p class="s2s_cookie_copyright"><a class="" href="https://jtotal.org" target="_blank" rel="nofollow">About Social2s</a></p>';
                        }
					$cookie_button .= '</div>
				</aside>';
        } else {
            $cookie_button = '';
        }

        return $cookie_button;
    }

    /**
     * [checkcookiecomponents]
     * @return [type] [bool]
     */
    private static function checkcookiecomponents()
    {



        $params  = self::$s2s_obj->params;

        $muestra_cookies = false;

        $cookie_comp = 'None';

        $cookie_custom = $params->get('s2s_stupid_cookie_custom','');

        if($cookie_custom == ''){
            $muestra_cookies = true;
        }else if(isset($_COOKIE[$cookie_custom]) && $_COOKIE[$cookie_custom] == "yes") {
            $cookie_comp     = $cookie_custom;
            $muestra_cookies = false;
        } else {
            $muestra_cookies = true;
        }

        /*
        //gearscookie
        if (isset($_COOKIE['gearcookies']) && $_COOKIE['gearcookies'] == "yes") {
            $cookie_comp     = 'gearcookies';
            $muestra_cookies = false;
        } else {
            $muestra_cookies = true;
        }

        //cookie confirm
        if (isset($_COOKIE['ccm_cookies_accepted']) && $_COOKIE['ccm_cookies_accepted'] == "yes") {
            $cookie_comp     = 'ccm_cookies';
            $muestra_cookies = false;
        } else {
            $muestra_cookies = true;
        }

        //folcomedia
        if (isset($_COOKIE['fmalertcookies']) && $_COOKIE['fmalertcookies'] == true) {
            $cookie_comp     = 'fmalertcookies';
            $muestra_cookies = false;
        } else {
            $muestra_cookies = true;
        }

        if (isset($_COOKIE['jbcookies']) && $_COOKIE['jbcookies'] == "yes") {
            $cookie_comp     = 'jbcookies';
            $muestra_cookies = false;
        } else {
            $muestra_cookies = true;
        }

        //Cookie Accept
        if (isset($_COOKIE['cookieaccept']) && $_COOKIE['cookieaccept'] == "yes") {
            $cookie_comp     = 'cookieaccept';
            $muestra_cookies = false;
        } else {
            $muestra_cookies = true;
        }
        */

        //Custom cookie component
        /*
        if($params->get('s2s_stupid_cookie_custom', '') != ''){
            if (isset($_COOKIE[$params->get('s2s_stupid_cookie_custom', '')]) && $_COOKIE[$params->get('s2s_stupid_cookie_custom', '')] == "yes") {
                $cookie_comp     = $params->get('s2s_stupid_cookie_custom', '');
                $muestra_cookies = false;
            } else {
                $muestra_cookies = true;
            }  
        }        
*/

        //if s2s_cookie is accepted, i update cookie components
        if (isset($_COOKIE['s2s_cookie']) && $_COOKIE['s2s_cookie'] == "1") {
            
            $cookie_comp     = 's2s_cookie';
            $muestra_cookies = false;
            
            if(isset($_COOKIE[$cookie_custom]) && $_COOKIE[$cookie_custom] == "1") {
                //leave return $muestra_cookies
            }else{
                $inputCookie     = JFactory::getApplication();
                $inputCookie->input->cookie->set($name=$cookie_custom,$value=true,$expire=0); 
            }

            //apply
            /*
            $inputCookie->set($name = 'fmalertcookies', $value = true, $expire = 0);
            $inputCookie->set($name = 'gearcookies', $value = 'yes', $expire = 0);
            $inputCookie->set($name = 'ccm_cookies_accepted', $value = 'yes', $expire = 0);
            $inputCookie->set($name = 'jbcookies', $value = 'yes', $expire = 0);
            $inputCookie->set($name = 'cookieaccept', $value = 'yes', $expire = 0);
            //$inputCookie->set($name = 'cookieaccept', $value = 'yes', $expire = 0);
            */
            
        }
        
        PlgContentSocial2s::updateS2sDebug('COOKIE component', $cookie_comp, 'info', 'user-secret');
        return $muestra_cookies;
    }

    /**
     * [getSupraClasses classes for supracontenedor]
     * @return [type] [array]
     */
    public static function getSupraClasses()
    {
        $params  = self::$s2s_obj->params;
        $context = self::$s2s_obj->context;

        //COOKIES
        $checkCookie = $params->get('s2s_stupid_cookie_on', '0');
        $cookieclass = ($checkCookie ? "cookie_on" : "");
        if (self::$s2s_obj->cookie_button == "") {
            $cookieclass = "";
        }

        //BTN class
        $btn_group_class = 's2s-btn-group';

        //POSITION FIXED
        //review 4.0 TODO
        $s2s_pos_fix = $params->get('s2s_art_fixed', 0);
        $pos_fix     = '';

        if ($s2s_pos_fix && $context == 'com_content.article') {

            $s2s_art_fixed_mode = $params->get('s2s_art_fixed_mode', 's2s_fixed_horizontal');
            $s2s_art_fixed_posx = $params->get('s2s_art_fixed_posx', 'right');
            $s2s_art_fixed_posy = $params->get('s2s_art_fixed_posy', 'center');

            $pos_fix .= ' s2s_pos_fixed ';
            $pos_fix .= $s2s_art_fixed_mode;
            $pos_fix .= ' s2s_pos_fix_x_' . $s2s_art_fixed_posx;
            $pos_fix .= ' s2s_pos_fix_y_' . $s2s_art_fixed_posy;

            //btn class
            if ($s2s_art_fixed_mode == "s2s_fixed_vertical") {
                $btn_group_class = 's2s-btn-group-vertical';
            }
        }

        //BALLOON POSITION
        $s2s_art_balloon_pos = $params->get('s2s_art_balloon_pos', '0');

        if ($s2s_art_balloon_pos) {
            $balloon_pos = 's2s_balloon_top';
        } else {
            $balloon_pos = 's2s_balloon_bottom';
        }

        //FILL
        $s2s_art_fill = $params->get('s2s_art_fill', '0');
        $s2s_cat_fill = $params->get('s2s_cat_fill', '0');
        if ($params->get('s2s_takefromarticle', '1')) {
            $s2s_cat_fill = $s2s_art_fill;
        }

        //TEXT_CLASS
        $s2s_art_text = $params->get('s2s_art_show_text', '0');
        $s2s_cat_text = $params->get('s2s_cat_show_text', '0');
        if ($params->get('s2s_takefromarticle', '1')) {
            $s2s_cat_text = $s2s_art_text;
        }

        //STICKY_CLASS
        $s2s_art_sticky = $params->get('s2s_art_sticky', '0');
        $s2s_cat_sticky = $params->get('s2s_art_sticky', '0');
        if ($params->get('s2s_takefromarticle', '1')) {
            $s2s_cat_sticky = $s2s_art_sticky;
        }

        //check article
        if ($context == 'com_content.article') {
            //fill
            if ($s2s_art_fill == 0) {
                $css_fill = '';
            } elseif ($s2s_art_fill == 1) {
                $css_fill = 's2s_fill';
            } else {
                $css_fill = 's2s_fill_mobile';
            }
            //text class
            if ($s2s_art_text == 0) {
                $css_text = 's2s_no_text';
            } elseif ($s2s_art_text == 1) {
                $css_text = 's2s_text';
            } else {
                $css_text = 's2s_text_mobile';
            }
            //sticky class
            if ($s2s_art_sticky == 0) {
                $css_sticky = '';
            } elseif ($s2s_art_sticky == 1) {
                $css_sticky = 's2s_sticky';
            } else {
                $css_sticky = 's2s_sticky_mobile';
            }

            //check category
        } elseif ($context == 'com_k2.itemlist'
            || $context == 'com_content.category'
            || $context == 'com_content.featured') {

            //fill
            if ($s2s_cat_fill == 0) {
                $css_fill = '';
            } elseif ($s2s_cat_fill == 1) {
                $css_fill = 's2s_fill';
            } else {
                $css_fill = 's2s_fill_mobile';
            }

            //css text
            if ($s2s_cat_text == 0) {
                $css_text = 's2s_no_text';
            } elseif ($s2s_cat_text == 1) {
                $css_text = 's2s_text';
            } else {
                $css_text = 's2s_text_mobile';
            }

            //sticky class
            if ($s2s_cat_sticky == 0) {
                $css_sticky = '';
            } elseif ($s2s_cat_sticky == 1) {
                $css_sticky = 's2s_sticky';
            } else {
                $css_sticky = 's2s_sticky_mobile';
            }

            //check category
        } else {
            //fill
            if ($s2s_cat_fill == 0) {
                $css_fill = '';
            } elseif ($s2s_cat_fill == 1) {
                $css_fill = 's2s_fill';
            } else {
                $css_fill = 's2s_fill_mobile';
            }

            //css text
            if ($s2s_cat_text == 0) {
                $css_text = 's2s_no_text';
            } elseif ($s2s_cat_text == 1) {
                $css_text = 's2s_text';
            } else {
                $css_text = 's2s_text_mobile';
            }

            //sticky class
            if ($s2s_cat_sticky == 0) {
                $css_sticky = '';
            } elseif ($s2s_cat_sticky == 1) {
                $css_sticky = 's2s_sticky';
            } else {
                $css_sticky = 's2s_sticky_mobile';
            }

        }

        $classes = array(
            'lang_direction'  => self::$s2s_obj->lang_direction,
            'behavior'        => self::$s2s_obj->behavior,
            'cookieclass'     => $cookieclass,
            'align'           => 'align_' . PlgContentSocial2s::$s2s_obj->styles->align,
            'pos_fix'         => $pos_fix,
            'balloon_pos'     => $balloon_pos,
            'css_fill'        => $css_fill,
            'css_text'        => $css_text,
            's2s_sticky'      => $css_sticky,
            'btn_group_class' => PlgContentSocial2s::$s2s_obj->styles->group,
        );

        PlgContentSocial2s::updateS2sDebug('Classes', implode(' | ', $classes), 'info', 'cog');

        return (object) $classes;
    }

    /**
     * [ORDER && SOCIAL]
     * @return [type] [array]
     */
    public static function getSocial()
    {
        $params = self::$s2s_obj->params;

        //get order
        $s2s_order = explode(',', $params->get('s2s_order', '0,1,2,3,4,5,6,7,8,9,10,11,12,13'));

        //elimino el último valor porque esta vacío
        //array_pop($s2s_order);

        //check number (12 elements currently)
        if (count($s2s_order) != 14) {
            $s2s_order = explode(',', '0,1,2,3,4,5,6,7,8,9,10,11,12,13');
        }

        require_once 'social.php';
        $s2s_social = new s2s_social;

        $afterplus    = '';
        $param_mas_on = $params->get('mas_on', 0);
        $mas_on       = false;

        if ($param_mas_on) {
            $mas_on = true;
            if ($param_mas_on == 2) {
                if (!self::$s2s_obj->jmobile) {
                    $mas_on = false;
                }
            }
        }

        //check if it is last
        if (end($s2s_order) == '13') {
            $mas_on = false;
        }

        $html = '';
        foreach ($s2s_order as $key => $value) {

            if (!$mas_on) {
                $afterplus = '';
            }

            if ($value == '0') {
                //TWITTER
                if ($params->get('twitter_on', 1)) {
                    if ($params->get('twitter_fast_as_light', 0)) {
                        // new
                        $html .= $s2s_social->getTwitterOwn($afterplus);
                    } else {
                        //legacy
                        $html .= $s2s_social->getTwitter($afterplus);
                    }
                }
            } elseif ($value == '1') {
                //FACEBOOK
                if ($params->get('facebook_on', 1)) {
                    $html .= $s2s_social->getFacebook($afterplus);
                }
            } elseif ($value == '2') {
                //PINTEREST
                if ($params->get('pinterest_on', 1)) {
                    $html .= $s2s_social->getPinterest($afterplus);
                }
            } elseif ($value == '3') {
                //LINKEDIN
                if ($params->get('linkedin_on', 1)) {
                    $html .= $s2s_social->getLinkedin($afterplus);
                }
            } 
            //bye gplus! snif
            /*elseif ($value == '4') {
                    //GOOGLE PLUS
                    if ($params->get('gplus_on', 0)) {
                        $html .= $s2s_social->getGPlus($afterplus);
                    }
                
            } */
            elseif ($value == '5') {
                //WAPP
                if ($params->get('wapp_on', 0)) {
                    $html .= $s2s_social->getWapp($afterplus);
                }
            } elseif ($value == '6') {
                //TGRAM
                if ($params->get('tgram_on', 0)) {
                    $html .= $s2s_social->getTgram($afterplus);
                }
            } elseif ($value == '7') {
                //FLIPB
                if ($params->get('flipb_on', 0)) {
                    $html .= $s2s_social->getFlipb($afterplus);
                }
            } elseif ($value == '8') {
                //FLIPB
                if ($params->get('delio_on', 0)) {
                    $html .= $s2s_social->getDelio($afterplus);
                }
            } elseif ($value == '9') {
                //TUMBLR
                if ($params->get('tumblr_on', 1)) {
                    $html .= $s2s_social->getTumblr($afterplus);
                }
            } elseif ($value == '10') {
                //vk
                if ($params->get('vk_on', 0)) {
                    $html .= $s2s_social->getVk($afterplus);
                }
            } elseif ($value == '11') {
                //email
                if ($params->get('email_on', 0)) {
                    $html .= $s2s_social->getEmail($afterplus);
                }
            } elseif ($value == '12') {
                //email
                if ($params->get('reddit_on', 0)) {
                    $html .= $s2s_social->getReddit($afterplus);
                }
            } elseif ($value == '13') {
                //MORE
                $afterplus = 'afterplus';
                if ($params->get('mas_on', 0)) {
                    //$html .= $s2s_social->getMas($params, $button_size, $buttons, $cookie_button, $title, $full_link, $idioma_twitter, $idioma, $texts, $context, $article);
                }
            }

        }

        //boton +
        if ($mas_on) {
            $html .= $s2s_social->getMas();
            /*$html .='<div class="s2s_mas btn btn-default '.$button_size.'">
        <a><i class="fa fa-plus-circle"></i></a>
        </div>';*/
        }

        return $html;

    }

    /**
     * [getHtml]
     * @param  [type] $social [description]
     * @return [type]         [html]
     */
    public static function getHtml($social)
    {


        $params = self::$s2s_obj->params;

        $class = self::$s2s_obj->supraclasses;

        $html = '';

        $html .= '<div class="s2s_supra_contenedor ' . $class->lang_direction . ' ' . $class->behavior . ' ' . $class->cookieclass . ' ' . $class->align . ' ' . $class->pos_fix . ' ' . $class->balloon_pos . ' ' . $class->s2s_sticky . '" >';

        $uri             = JFactory::getURI();
        $absolute_url    = $uri->toString();
        //4.0.138
        //$s2s_current_url = $absolute_url;
        $s2s_current_url = self::$s2s_obj->full_link;
        $html .= '<input name="social2s_url" type="hidden" class="social2s_url" value="' . $s2s_current_url . '" />';


        //SHARE TEXT
        $s2s_share_text = JText::_($params->get('s2s_text_to_share_text', 'SOCIAL2S_SHARE_TEXT'));

        //share text top
        if ($params->get('s2s_text_to_share', 0) && $params->get('s2s_text_to_share_position', 0) == 0) {        
                $html .= '<span class="s2s_share_text s2s_share_text_top">' .$s2s_share_text.'</span><div class="clearfix"></div>';
        }

        $html .= '<div class="s2s_contenedor ' . $class->css_fill . ' ' . $class->css_text . ' ' . $class->btn_group_class . '">';

            //share text left
            if ($params->get('s2s_text_to_share', 0) && $params->get('s2s_text_to_share_position', 0) == 1) {        
                $html .= '<span class="s2s_share_text s2s_share_text_left">' .$s2s_share_text.'</span>';
            }


        //botones
        $html .= $social;

        /*cierro contenedor*/
        $html .= '</div>';

        //CREDITS
        if ($params->get('s2s_credits', 1)) {
            $html .= '<div class="s2s_credits_wrapper"><small class="social2s_credits clearfix small">powered by <a rel="nofollow" target="_blank" href="https://jtotal.org/joomla/plugins/social2s">social2s</a></small></div>';
        }

        /*cierro supra contenedor*/
        $html .= '</div>';

        return $html;

    }

}
