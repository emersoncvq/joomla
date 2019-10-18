/**
 * SOCIAL2S V4 JS
 * V 4.0.141
*/

s2s_try();

function s2s_try(){
  try {
    if(jQuery) {
      s2s_init();
      //close if jQuery statement
    }
  }catch(e){
    console.log("jQuery not active");
    setTimeout(function() { s2s_try() }, 1000);
  }
}



function s2s_init(){



jQuery(document).ready(function(e) {

  if(jQuery('body').hasClass('admin')){
    return;
  }

  //COUNT
  s2s_count();
  
  //k2_remove_social
  s2s_k2_remove_social();

  s2s_mas();

  s2s_cookies();

/*2.0.0*/
//load social scripts on load
  if(s2s_load_scripts_onload == "1"){
    load_social_scripts();
  }else if(s2s_load_scripts_onload == "2"){
     if(jQuery('.social2s_context').val() == "com_content.article"){
      load_social_scripts();
     }
  }


/*POSITION FIXED: to FIX in V5*/
//Min width pos FIX
/*
  if(jQuery('.s2s_pos_fixed').length >= 1){
    var ancho_total = jQuery(window).width();
    var ancho_defined = jQuery('.s2s_art_mobile_min').val();

    if(ancho_total<=ancho_defined){
      //if thinner I deactivate fixed positioning
      jQuery('.s2s_supra_contenedor').removeClass('s2s_pos_fixed');
      jQuery('.s2s_supra_contenedor').removeClass('s2s_fixed_vertical');
      jQuery('.s2s_contenedor').addClass('btn-group').removeClass('btn-group-vertical');
    }
  }
*/

/*INSERT: to FIX in V5*/
//Min width pos FIX
/*
  var s2s_insert = jQuery('.s2s_insert').html();
  var s2s_context = jQuery('.social2s_context').val();
  if(s2s_insert == "1" && s2s_context == 'com_content.article' && jQuery('.s2s_insert_element').html() != ''){

      var s2s_clase = '.'+jQuery('.s2s_insert_element').html();
      
    //verify if element exists
    if(jQuery(s2s_clase).length){

      //remove script to prevent vk error on injection
      jQuery('.s2s_vk script').html('');

      var s2s_element = jQuery(s2s_clase);
      var contenidos = jQuery('.s2s_supra_contenedor').html();
      var s2s_insert_position = jQuery('.s2s_insert_position').html();

      if(s2s_insert_position=="0"){
        s2s_element.before('<div class="s2s_supra_contenedor_dummy"></div>');
      }else if(s2s_insert_position=="1"){
        s2s_element.after('<div class="s2s_supra_contenedor_dummy"></div>');
      }else if(s2s_insert_position=="2"){
        s2s_element.prepend('<div class="s2s_supra_contenedor_dummy"></div>');
      }else if(s2s_insert_position=="3"){
        s2s_element.append('<div class="s2s_supra_contenedor_dummy"></div>');
      }else{
        s2s_element.after('<div class="s2s_supra_contenedor_dummy"></div>');
      }

      jQuery('.s2s_supra_contenedor_dummy').append(contenidos);
      //quito el supra contenedor
      jQuery('.s2s_supra_contenedor').html('');
    }
  }
*/
/*fin 1.0.27*/
    jQuery('.s2s_twitter').click(function(e){
      e.stopImmediatePropagation(); //e.preventDefault();
      buttons(this);
      if(checkCookie()) twitter();
  
      if(jQuery('.twitter-follow-button').length >= 1){
        //var ancho_social = jQuery('.s2s_contenedor').width();
        //if(jQuery('.s2s_btn_twitter').length == 0){
          //jQuery('.s2s_twitter_iframe').css('min-width','270px');
        //}
      }
    });
    jQuery('.s2s_facebook').click(function(e){
      e.stopImmediatePropagation(); //e.preventDefault();
      buttons(this);
      if(checkCookie()) facebook();
    });
    jQuery('.s2s_pinterest').click(function(e){
      //4.0.138 - remove stopPropagation due implosible to click in pin button
      //e.stopImmediatePropagation(); //e.preventDefault();
      buttons(this);
      if(checkCookie()) pinterest();
    });
    jQuery('.s2s_linkedin').click(function(e){
      e.stopImmediatePropagation(); //e.preventDefault();
      buttons(this);
      if(checkCookie()) linkedin(this);
    });
    jQuery('.s2s_gplus').click(function(e){
      e.stopImmediatePropagation(); //e.preventDefault();
      buttons(this);
      if(checkCookie()) gplus();
    });
    jQuery('.s2s_wapp').click(function(e){
      e.stopImmediatePropagation();
      buttons(this);

      //if(checkCookie()) gplus();
    });
    jQuery('.s2s_tgram').click(function(e){
      e.stopImmediatePropagation(); //e.preventDefault();
      buttons(this);
      //if(checkCookie()) gplus();
    });
    jQuery('.s2s_flipb').click(function(e){
      e.stopImmediatePropagation(); //e.preventDefault();
      buttons(this);
      if(checkCookie()) flipb();
    });
    jQuery('.s2s_delio').click(function(e){
      e.stopImmediatePropagation(); //e.preventDefault();
      buttons(this);
      if(checkCookie()) delio();
    });
    jQuery('.s2s_tumblr').click(function(e){
      e.stopImmediatePropagation(); //e.preventDefault();
      buttons(this);
      if(checkCookie()) tumblr();
    });
    jQuery('.s2s_vk').click(function(e){
      e.stopImmediatePropagation(); //e.preventDefault();
      buttons(this);
      if(checkCookie()) vk(this);
    });
    jQuery('.s2s_email').click(function(e){
      e.stopImmediatePropagation(); //e.preventDefault();
      buttons(this);
      if(checkCookie()) email(this);
    });

    jQuery('.s2s_reddit').click(function(e){
      e.stopImmediatePropagation(); //e.preventDefault();
      buttons(this);
      if(checkCookie()) reddit(this);
    });


    /**********************************/
    /*************   COOKIES   ******/
    /**********************************/
  
    function s2s_cookies(){

      jQuery('.s2s_cookie_contenedor').click(function(e){
        e.stopImmediatePropagation(); //e.preventDefault();
        
        cookieAccept();

        s2s_count();

        var globo = jQuery(this).parent('div');
        //console.log(globo);

        //v4 - no sé por qué lo activo aquí
        //is_on_screen(globo);

        /*TODO find a more elegant way*/
        var callSocialFunction = jQuery(this).parent();
        if(callSocialFunction.hasClass('s2s_twitter_iframe')){
          twitter();
        }else if (callSocialFunction.hasClass('s2s_facebook_iframe')){
          facebook();
        }else if (callSocialFunction.hasClass('s2s_pinterest_iframe')){
          pinterest();
        }else if (callSocialFunction.hasClass('s2s_linkedin_iframe')){
          linkedin();
        }else if (callSocialFunction.hasClass('s2s_gplus_iframe')){
          gplus();
        }else if (callSocialFunction.hasClass('s2s_tumblr_iframe')){
          tumblr();
        }else if (callSocialFunction.hasClass('s2s_vk_iframe')){
          vk();
        }
      });

      jQuery('.s2s_cookie_information').click(function(e){
        e.stopImmediatePropagation(); e.preventDefault();
        jQuery('.s2s_cookie_more_info').toggle('slow', function(e) {
          //end of animation
        });
      });
      //avoid propagation of cookie policy link
      jQuery('.s2s_cookie_read_policy a').click(function(e){
        e.stopImmediatePropagation(); e.preventDefault();
      });
      jQuery('.s2s_cookie_copyright a').click(function(e){
        e.stopImmediatePropagation(); e.preventDefault();
      });
    }



    function buttons(boton){

      var globo = jQuery(boton).children('div.globo');

        if(jQuery(boton).hasClass('afterplus') == true && jQuery(boton).hasClass('afterplus_open') == false){
          return;
        }



        //v4 UNIFY
        if(jQuery(boton).hasClass('active')){
          jQuery(boton).removeClass('active');
           setTimeout(function(){
             jQuery(boton).removeClass('active_done'); 
             jQuery(globo).addClass('s2s_globo_closed'); 
          }, 300);
        }else{
          jQuery('.s2s_contenedor > div').removeClass('active').removeClass('active_done');
          jQuery(globo).removeClass('s2s_globo_closed'); 
          jQuery(boton).addClass('active').addClass('active_done');
          //is_on_screen(globo);
          fix_arrow(globo);

          setTimeout(function(){
            is_on_screen(globo);
          }, 200);
        }


        //s2s_balloon_top

      /*
      
        //V3
        //MODERN
        if(jQuery('.s2smodern').length>=1){

            if(jQuery(boton).hasClass('active')){
              jQuery(boton).removeClass('active');
               setTimeout(function(){
                 jQuery(boton).removeClass('active_done'); 
              }, 300);
            }else{
              jQuery('.s2s_contenedor > div').removeClass('active').removeClass('active_done');
              jQuery(boton).addClass('active').addClass('active_done');
              //is_on_screen(globo);

              setTimeout(function(){
                  is_on_screen(globo);
              }, 200);
            }

          //DEFAULT
          }else{
            //var claseopen = jQuery('.s2s_twitter .globo').hasClass('s2sopen');

            //checkCookie();
            
            if(jQuery(boton).hasClass('active')){
              jQuery(boton).removeClass('active');
              globo.fadeTo(330, 0, function(){ 
                globo.hide();
              });
              
            }else{
              jQuery('.s2s_contenedor > div').removeClass('active');
              jQuery('.globo').hide();
              jQuery(boton).addClass('active');
              globo.fadeTo(330, 1, function(){});
              is_on_screen(globo);
            }
        }

      */

    }

    function fix_arrow(objeto){

      boton_w = jQuery(objeto).parent().outerWidth()/2-8;


      if(objeto.hasClass('is_on_screen')){
        //objeto.children('.s2s_flecha').css('right',boton_w+resta_prevent_left+'px');
        //objeto.children('.s2s_flecha').css('left','');
      }else{
        objeto.children('.s2s_flecha').css('left',boton_w+'px');
        objeto.children('.s2s_flecha').css('right','');
      }

    }


    function twitter(){
        if(!jQuery('.s2s_twitter_iframe').hasClass('s2s_loaded')){
            jQuery('.s2s_twitter_iframe').addClass("s2s_loaded");

            //new
            if(jQuery('.s2s_btn_twitter').length >= 1){

            }else{
              //old
              !function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';
              if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';
              fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');
            }

        }else{

        }
    }

    function facebook(){
      
      if(!jQuery('.s2s_facebook_iframe').hasClass('s2s_loaded')){
        jQuery('.s2s_facebook_iframe').addClass("s2s_loaded");
    
        var language = s2s_lang_1tag;

        //is_on_screen(jQuery('.s2s_facebook_iframe'));
        //language.replace("-", "_");
        (function(d, s, id) {
           var js, fjs = d.getElementsByTagName(s)[0];
          if (d.getElementById(id)) return;
          js = d.createElement(s); js.id = id;
          //js.src = "//connect.facebook.net/"+language+"/sdk.js#xfbml=1&appId=514279921989553&version=v3.2";
          js.src = "//connect.facebook.net/"+language+"/sdk.js#xfbml=1&version=v3.2";
          fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
      }
    }


    function pinterest(){
      if(!jQuery('.s2s_pinterest_iframe').hasClass('s2s_loaded')){
        jQuery('.s2s_pinterest_iframe').addClass("s2s_loaded");
         //is_on_screen(jQuery('.s2s_gplus_iframe'));


        (function(d){
          var f = d.getElementsByTagName('SCRIPT')[0], p = d.createElement('SCRIPT');
          p.type = 'text/javascript';
          p.async = true;
          p.defer = true;
          p.src = '//assets.pinterest.com/js/pinit.js';
          f.parentNode.insertBefore(p, f);
        }(document));


      }
    }

    function linkedin(este){
      if(!jQuery('.s2s_linkedin_iframe').hasClass('s2s_loaded')){
      
        //require reload for category
        if(jQuery('.social2s_context').val()=="com_content.article"){
          jQuery('.s2s_linkedin_iframe').addClass("s2s_loaded");
        }

        var language = s2s_lang_1tag;
        //is_on_screen(jQuery('.s2s_linkedin'));
        var linkedin_art_path = jQuery(este).children('.linkedin_art_path').val();
        //jQuery.getScript('http://platform.linkedin.com/in.js');
        jQuery.getScript("https://platform.linkedin.com/in.js?async=true", function success() {
          IN.init({
              lang: language
          });
        });
      }
    }

    function gplus(){
      if(!jQuery('.s2s_gplus_iframe').hasClass('s2s_loaded')){
        jQuery('.s2s_gplus_iframe').addClass("s2s_loaded");

        //is_on_screen(jQuery('.s2s_gplus_iframe'));

        var language =  jQuery('.s2s_gplus').attr('lang');

        window.___gcfg = {lang: language};
        (function() {
          var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
          po.src = 'https://apis.google.com/js/plusone.js';
          var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
        })();
      }
    }

    function flipb(){
      if(!jQuery('.s2s_flipb_iframe').hasClass('s2s_loaded')){
        jQuery('.s2s_flipb_iframe').addClass("s2s_loaded");

        //is_on_screen(jQuery('.s2s_gplus_iframe'));

        var language =  s2s_lang_1tag;

        /*window.___gcfg = {lang: language};
        (function() {
          var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
          po.src = 'https://cdn.flipboard.com/web/buttons/js/flbuttons.min.js';
          var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
        })();*/

        (function(d){
          var f = d.getElementsByTagName('SCRIPT')[0], p = d.createElement('SCRIPT');
          p.type = 'text/javascript';
          p.async = true;
          p.src = '//cdn.flipboard.com/web/buttons/js/flbuttons.min.js';
          f.parentNode.insertBefore(p, f);
        }(document));
      }
    }

    function delio(){
      /*if(!jQuery('.s2s_delio_iframe').hasClass('s2s_loaded')){
        jQuery('.s2s_delio_iframe').addClass("s2s_loaded");

        is_on_screen(jQuery('.s2s_delio_iframe'));
        var language =  jQuery('.s2s_delio').attr('lang');
        (function(d){
          var f = d.getElementsByTagName('SCRIPT')[0], p = d.createElement('SCRIPT');
          p.type = 'text/javascript';
          p.async = true;
          p.src = '//del.icio.us/save/get_bookmarklet_save?url='+encodeURIComponent(php_full_link)+'&title='+encodeURIComponent(php_text);
          f.parentNode.insertBefore(p, f);
        }(document));
      }else{
        function($){var bookmarklet=document.getElementById('DELI_save_link_slidedown');
          if(bookmarklet){$('#DELI_mist').show();
          $('#DELI_save_link_slidedown').slideDown('normal');
          return
        };
        if(!window.jQuery){
          node=document.createElement('SCRIPT');
          node.type='text/javascript';
          node.src='https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js';
          document.body.appendChild(node)
        }
        node=document.createElement('SCRIPT');
        node.type='text/javascript';
        node.src=window.location.protocol+'//del.icio.us/save/get_bookmarklet_save?url=&amp;title=&amp;notes='+encodeURIComponent(''+(window.getSelection?window.getSelection():document.getSelection?document.getSelection():document.selection.createRange().text));document.body.appendChild(node)})(window.jQuery);">Add Bookmark</a>
      }*/
    }
    /*

    <a href="javascript:(
    function($){
      var bookmarklet=document.getElementById('DELI_save_link_slidedown');
        if(bookmarklet){$('#DELI_mist').show();
          $('#DELI_save_link_slidedown').slideDown('normal');
          return
        };
        if(!window.jQuery){
          node=document.createElement('SCRIPT');
          node.type='text/javascript';
          node.src='https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js';
          document.body.appendChild(node)
        }
        node=document.createElement('SCRIPT');
        node.type='text/javascript';
        node.src=window.location.protocol+'//del.icio.us/save/get_bookmarklet_save?url='+encodeURIComponent(window.location.href)+'&title='+encodeURIComponent(document.title)+'&notes='+encodeURIComponent(''+(window.getSelection?window.getSelection():document.getSelection?document.getSelection():document.selection.createRange().text));
        document.body.appendChild(node)}
      )(window.jQuery);">
      
      <img src="https://del.icio.us/img/logo.png" style="display: inline;" height="16" width="16" alt="Delicious"> Save this on Delicious</a>

    */

    function tumblr(){
      if(!jQuery('.s2s_tumblr_iframe').hasClass('s2s_loaded')){
        jQuery('.s2s_tumblr_iframe').addClass("s2s_loaded");

        //fix share button
        if(jQuery('.tumblr-follow-button').length >= 1){
          jQuery('.tumblr-follow-button').removeAttr("style");
        }

        //var language =  jQuery('.s2s_linkedin').attr('lang');
        //is_on_screen(jQuery('.s2s_tumblr_iframe'));
        
        //old
        //jQuery.getScript('http://platform.tumblr.com/v1/share.js');
        !function(d,s,id){
          var js,ajs=d.getElementsByTagName(s)[0];
          if(!d.getElementById(id)){
            js=d.createElement(s);js.id=id;js.src="https://secure.assets.tumblr.com/share-button.js";
            ajs.parentNode.insertBefore(js,ajs);
          }
        }(document, "script", "tumblr-js");
      }
    }

    function vk(clikon){
      if(!jQuery('.s2s_vk_iframe').hasClass('s2s_loaded')){
        jQuery('.s2s_vk_iframe').addClass("s2s_loaded");

        //new
        //a way to get vk_id
        if(clikon){

          //NEW API  is active
          if(jQuery('.vk_button').length >= 1){
            var vk_boton_id = jQuery(clikon).children('.globo').children('.vk_button').attr('id');
            VK.Widgets.Like(vk_boton_id);
          }else{


          }
        }
        
      }
    }


    function email(){
      if(!jQuery('.s2s_email_iframe').hasClass('s2s_loaded')){
        jQuery('.s2s_email_iframe').addClass("s2s_loaded");
        //is_on_screen(jQuery('.s2s_gplus_iframe'));
        var language =  jQuery('.s2s_flipb').attr('lang');
      }
    }

    function reddit(clikon){
      if(!jQuery('.s2s_reddit_iframe').hasClass('s2s_loaded')){
        jQuery('.s2s_reddit_iframe').addClass("s2s_loaded");      
      }
    }


/*PRO*/

/*STUPID COOKIES*/
  function checkCookie(){
    //var checkCookie = jQuery('.checkCookie').text().split("")[0];
    //if param activate

    if(s2s_checkCookie == "1"){

      var s2s_cookie=getCookie("s2s_cookie");
      var ccm_cookie=getCookie("ccm_cookies_accepted");
      var gear_cookie=getCookie("gearcookies");
      var folcomedia_cookie=getCookie("fmalertcookies");
      var jb_cookie=getCookie("jbcookies");
      var crayon_cookieaccept=getCookie("cookieaccept");
      //var custom_cookieaccept=getCookie("cookieaccept");
      //si la cookie existe
      
      if((s2s_cookie!=null) && (s2s_cookie!="")){

        jQuery('.s2s_cookie_contenedor').hide();
        return true;
      }else{
        //alert("desactivada (false)");
        jQuery('.s2s_cookie_contenedor').show();
        return false;
      }
    }else{
      return true;
    }
  }

  function setCookie(c_name,value,exdays){
    var exdate=new Date();
    exdate.setDate(exdate.getDate() + exdays);
    var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
    document.cookie=c_name + "=" + c_value;
  }

  function getCookie(c_name){
    var c_value = document.cookie;
    var c_start = c_value.indexOf(" " + c_name + "=");
    if(c_start == -1){
      c_start = c_value.indexOf(c_name + "=");
    }
    if(c_start == -1){
      c_value = null;
    }else{
      c_start = c_value.indexOf("=", c_start) + 1;
      var c_end = c_value.indexOf(";", c_start);
      if (c_end == -1){
        c_end = c_value.length;
      }
      c_value = unescape(c_value.substring(c_start,c_end));
    }
    return c_value;
  }

  function cookieAccept(){
    //alert("cookie_aceptada");
    setCookie("s2s_cookie",1,365);
    jQuery('.s2s_cookie_contenedor').hide();
    jQuery('.s2s_supra_contenedor').removeClass('cookie_on');
  }

  function load_social_scripts(){
    if(jQuery('.s2s_twitter').length >= 1){
      twitter();
    }
    if(jQuery('.s2s_facebook').length >= 1){
      facebook();
    }
    if(jQuery('.s2s_pinterest').length >= 1){
      pinterest();
    }
    if(jQuery('.s2s_linkedin').length >= 1){
      linkedin(jQuery('.s2s_linkedin'));
    }
    if(jQuery('.s2s_gplus').length >= 1){
      gplus();
    }
    if(jQuery('.s2s_tumblr').length >= 1){
      tumblr();
    }
    if(jQuery('.s2s_vk').length >= 1){
      vk();
    }
  }



  /*v2.0.5*/
  /*CTA*/

  /*2.0.4*/
  if(s2s_cta_active==1){

    jQuery('.s2s_cta_wrapper .s2s_supra_contenedor').removeClass('s2s_pos_fixed');
    jQuery('.s2s_cta_wrapper .s2s_supra_contenedor').removeClass('s2s_fixed_vertical');
    jQuery('.s2s_cta_wrapper .s2s_contenedor').addClass('btn-group').removeClass('btn-group-vertical');

    //CTA close button
    jQuery('.s2s_cta_close').click(function(e){
      e.stopImmediatePropagation(); e.preventDefault();
      jQuery('.s2s_cta_active').val('0');
      //destroy
      jQuery('.s2s_cta_wrapper').removeClass('s2s_cta').delay(1000).queue(function() { jQuery(this).remove(); });
      //add cookie to dont show
      if(getCookie("s2s_cta_close") == null){
        setCookie("s2s_cta_close",1,365);
      }else if(getCookie("s2s_cta_close") == "1"){
        setCookie("s2s_cta_close",2,365);
      }

    });


    /*cta right always is_on_screen*/
    if(jQuery('.s2s_cta_wrapper').length >= 1){
      if(jQuery('.s2s_cta_wrapper').hasClass('cta_pos_right')){
        jQuery('.s2s_cta_wrapper .globo').addClass('is_on_screen');
      }
    }


    var _throttleTimer = null;
    var _throttleDelay = 100;
    var _th_nearend = false;

    function ScrollHandler(e) {

        if(jQuery('.s2s_cta_active').val()==0){
          return;
        }
        //throttle event:
        clearTimeout(_throttleTimer);
        _throttleTimer = setTimeout(function () {

            //do work
            if (jQuery(window).scrollTop() + jQuery(window).height() > jQuery(document).height() - 100){
              //console.log('near end');
              
              if(_th_nearend == false){

                //console.log(_th_nearend);
                jQuery('.s2s_cta_wrapper').addClass('s2s_cta');

                //clear button size classes
                jQuery('.s2s_cta_wrapper .s2s_supra_contenedor .btn').removeClass('btn-lg').removeClass('btn-xs').removeClass('btn-sm');

                cta_default_social();
                _th_nearend = true;

              }

            }else if(_th_nearend == true){
              _th_nearend = false;
              //console.log(_th_nearend);
              jQuery('.s2s_cta_wrapper').removeClass('s2s_cta');
                //buttons(jQuery('.s2s_cta_wrapper .s2s_twitter'));
                cta_default_social();
            }

        }, _throttleDelay);
    }
    jQuery(window).off('scroll', ScrollHandler).on('scroll', ScrollHandler);
  }

  //function to open the selected social network to open when CTA activates
  function cta_default_social(){

    var cta_def = jQuery('.s2s_cta_default').val();
    if(jQuery('.s2s_cta_wrapper').length <= 0){
      return;
    }

    if(cta_def == '0'){
      //nothing to declare

    }else if(cta_def == 'twitter'){
      buttons(jQuery('.s2s_cta_wrapper .s2s_twitter'));
      if(checkCookie()) twitter();
    }else if(cta_def == 'facebook'){
      buttons(jQuery('.s2s_cta_wrapper .s2s_facebook'));
      facebook();
    }else if(cta_def == 'pinterest'){
      buttons(jQuery('.s2s_cta_wrapper .s2s_pinterest'));
      pinterest();
    }else if(cta_def == 'linkedin'){
      buttons(jQuery('.s2s_cta_wrapper .s2s_linkedin'));
      linkedin();
    }else if(cta_def == 'gplus'){
      buttons(jQuery('.s2s_cta_wrapper .s2s_gplus'));
      gplus();
    }else if(cta_def == 'wapp'){
      buttons(jQuery('.s2s_cta_wrapper .s2s_wapp'));
      //tumblr();
    }else if(cta_def == 'tumblr'){
      buttons(jQuery('.s2s_cta_wrapper .s2s_tumblr'));
      tumblr();
    }else if(cta_def == 'vk'){
      buttons(jQuery('.s2s_cta_wrapper .s2s_vk'));
      vk();
    }

  }

  function is_on_screen(objeto){
    //objecto = globo

    if(jQuery('.s2s_supra_contenedor').hasClass('s2s_pos_fix_x_right')){
      return;
    }
    if(objeto.hasClass('is_on_screen')){
      return;
    }


    var obj_x = objeto.offset().left;
    var obj_y = objeto.offset().top;

    //variable obj w = obj.offsetWidth
    var obj_w = jQuery(objeto).outerWidth();
    //variable obj h = obj.offsetHeight
    var obj_h = jQuery(objeto).outerHeight();

    //prevent left
    if(obj_w>=obj_x){
      var resta_prevent_left = obj_w-obj_x;
      //var resta_prevent_left = 0;
     //console.log("es mayor!"+resta_prevent_left);
    }else{
       var resta_prevent_left = 0;
    }

    //variable window w = window.innerWidth
    var window_w = jQuery(window).width();
    //variable window h = window.innerHeight 
    var window_h = jQuery(window).height();

    var suma_obj_x = obj_x+obj_w;
    var suma_obj_y = obj_y+obj_h;

    var button_w = jQuery(objeto).parent('div.s2s_btn').children('a').outerWidth();

    var preventX = (obj_w-button_w)*-1;

    //console.log(obj_x+'|'+obj_w+'|'+suma_obj_x);
    //console.log(objeto);

    if(suma_obj_x < 0){
      //console.log("fuera left");
    }else if(suma_obj_y < 0){
      //console.log("fuera top");
      //console.log(suma_obj_y+'< 0');
    }else if(suma_obj_x > window_w){
      //objeto.addClass('preventOffscreenX');
      //console.log('sale_x_la_derecha');
      //objeto.css('left', preventX+resta_prevent_left);
      objeto.addClass('is_on_screen').addClass('s2s_prevent_right');

    }else if(suma_obj_y > window_h){
      //console.log("else");
      //console.log('y'+suma_obj_y+'>'+window_h);
      //CTA
      if(objeto.parents('.s2s_cta_wrapper').length >= 1){
        objeto.css('left', 'inherit'); 
        objeto.removeClass('is_on_screen');
      }

    }else{
      //console.log("else");
      //objeto.css('left', 'inherit');
      //objeto.css('left', 'inherit'); 
      objeto.removeClass('is_on_screen');
    }

    /*fix little arrow V4*/
    boton_w = (jQuery(objeto).parent().width()-4)/2;
    boton_w = jQuery(objeto).parent().outerWidth()/2-8;

    //boton_w_i = (jQuery(objeto).parent().width()-3)/2;
    //flecha_w = jQuery(objeto).children('.s2s_flecha').width();
    flecha_w = 16/4;


    if(objeto.hasClass('is_on_screen')){
      objeto.children('.s2s_flecha').css('right',boton_w+resta_prevent_left+'px');
      objeto.children('.s2s_flecha').css('left','');
    }else{
      objeto.children('.s2s_flecha').css('left',boton_w+'px');
      objeto.children('.s2s_flecha').css('right','');
    }
  }


  function s2s_mas(){

    jQuery('.s2s_mas').click(function(e){

      e.stopImmediatePropagation(); e.preventDefault();  
        
      //oculto todos
      jQuery('.s2s_contenedor > div').removeClass('active').removeClass('active_done');

      if(jQuery(this).hasClass('s2s_mas_opened')){
        jQuery(this).removeClass('s2s_mas_opened').removeClass('active');
        jQuery(this).parent().children('.afterplus').removeClass('afterplus_open');

      }else{
        var cosa = jQuery(this);
        jQuery(this).addClass('active').addClass('s2s_mas_opened');
        jQuery(this).parent().children('.afterplus').addClass('afterplus_open');

      }

    }); 
  }


  function s2s_k2_remove_social(){

    if(s2s_k2_remove_social){
      if(jQuery('.itemSocialSharing').length >=1){
        jQuery('.itemSocialSharing').remove();
      }
    }
  }

  function s2s_count(){
      var check_counts = false;  

      if(s2s_checkCookie == 1){
        if(checkCookie() == false){
          //console.log("return because checkcookies is not checked");
          jQuery('.s2s_badge').hide();
          return;
        }else{
          //console.log("DE nuevo");
          jQuery('.s2s_badge').show();
        }
      }

      if(facebook_like_count!= 0 || facebook_share_count != 0 || facebook_total_count != 0){
        check_counts = true;
      }
      
      if(pinterest_count!= 0) check_counts = true;
      if(linkedin_count!= 0) check_counts = true;
      if(tumblr_count!= 0) check_counts = true;
      if(vk_b_count!= 0) check_counts = true;


      if(check_counts){

        var init_donreach = false;
        var data_donreach;

        jQuery('.social2s_url').each(function( index ) {

          var s2s_url = jQuery(this).val();

 
          //
          //FACEBOOK COUNT
          //
          if(facebook_like_count!= 0 || facebook_share_count!= 0 || facebook_total_count!= 0){

            var s2s_fb_count_share = jQuery(this).parent().find('.s2s_fb_count_share');
            var s2s_fb_count_like = jQuery(this).parent().find('.s2s_fb_count_like');
            //var s2s_fb_count_total = jQuery(this).parent().find('.s2s_fb_count_total');
/*
window.fbAsyncInit = function() {
    FB.init({
      appId            : '514279921989553',
      autoLogAppEvents : true,
      xfbml            : true,
      version          : 'v3.3'
    });
  };
*/

            //api v3.x+ 

            /*
            FB.api(
              '/https://jtotal.org/joomla/plugins/social2s',
              'GET',
              {"fields":"engagement{share_count}"},
              function(response) {
                 console.log(response);
              }
            );


            
            FB.ui({
              method: 'share_open_graph',
              action_type: 'og.likes',
              action_properties: JSON.stringify({
                object:'https://jtotal.org/joomla/plugins/social2s',
              })
            }, function(response){
              // Debug response (optional)
              console.log(response);
            });
            */




            //api v2.2+ 
            /*
            jQuery.getJSON('//graph.facebook.com/?id='+s2s_url+'&format=json', function (data) {
            //jQuery.getJSON('//graph.facebook.com/?id=https://www.mashshare.net&format=json', function (data) {

              //console.log(jQuery(this).parent().next(".s2s_supra_contenedor"));
              if(facebook_count_hide != 0){             

                if(data.share.share_count!='0'){
                  s2s_fb_count_share.text(data.share.share_count);
                }else{
                  s2s_fb_count_share.remove();
                }

                if(data.share.comment_count!='0'){
                  s2s_fb_count_like.text(data.share.comment_count);
                }else{
                  s2s_fb_count_like.remove();
                }

              }else{
                s2s_fb_count_share.text(data.share.share_count);
                s2s_fb_count_like.text(data.share.comment_count);
                //s2s_fb_count_total.text(data[0].total_count);
              }

            });
          */





          } //end fb


          //
          //PINTEREST
          //
          if(pinterest_count != 0){

            var s2s_pinterest_count = jQuery(this).parent().find('.s2s_pinterest_badge_count');

            var pin = 'https://api.pinterest.com/v1/urls/count.json?callback=?';
            jQuery.getJSON(pin, {
              url: s2s_url,
            }).done(function (data) {

              var s2s_pinterest_count_hide = jQuery('.s2s_pinterest_count_hide').val();

              if(s2s_pinterest_count_hide=='1'){

                if(data.count!='0'){
                  s2s_pinterest_count.text(data.count);
                }else{
                  s2s_pinterest_count.remove();
                }

              }else{
                s2s_pinterest_count.text(data.count);
              }

            });
          }//end pinterest

          //linkedin
          if(linkedin_count != 0){

              var s2s_linkedin_count = jQuery(this).parent().find('.s2s_linkedin_badge_count');

              jQuery.ajax({
                  dataType: "jsonp",
                  url: "https://www.linkedin.com/countserv/count/share",
                  data: {
                      callback: "?",
                      format: "jsonp",
                      url: s2s_url
                  }
              }).done(function(data) {
                  var s2s_linkedin_count_hide = jQuery('.s2s_linkedin_count_hide').val();

                  if(s2s_linkedin_count_hide=='1'){

                    if(data.count!='0'){
                      s2s_linkedin_count.text(data.count);
                    }else{
                      s2s_linkedin_count.remove();
                    }

                  }else{
                    s2s_linkedin_count.text(data.count);
                  }
              });
          }



          //tumblr
          if(tumblr_count != 0){

              var s2s_tumblr_count = jQuery(this).parent().find('.s2s_tumblr_badge_count');

              var pin = '//api.tumblr.com/v2/share/stats';
              jQuery.getJSON(pin, {
                url: s2s_url,
              }).done(function (data) {

                if(tumblr_count_hide){
                  if(data.count!='0'){
                    s2s_tumblr_count.text(data.count);
                  }else{
                    s2s_tumblr_count.remove();
                  }
                }else{
                  s2s_tumblr_count.text(data.count);
                }

              });
          }

          //vk and GPLUS (donreach)
          if(jQuery('.s2s_vk_count').val() == "1" || jQuery('.s2s_gplus_b_count').val() == "1"){
              
              //TEST
              //s2s_url = 'http://9gag.com/';

              if(init_donreach){
                return;
              }
              var s2s_gplus_b_count = jQuery(this).parent().find('.s2s_gplus_b_badge_count');
              var s2s_vk_count = jQuery(this).parent().find('.s2s_vk_badge_count');
                
              jQuery.ajax({
                  dataType: "jsonp",
                  crossDomain: true,
                  url: "https://count.donreach.com/?url="+s2s_url+"&providers=google,vk"
              }).done(function(data) {             

                  //google plus
                  if(jQuery('.s2s_gplus_b_count').val() == "1"){

                      var s2s_gplus_b_count_hide = jQuery('.s2s_gplus_b_count_hide').val();
                      
                      if(s2s_gplus_b_count_hide=='1'){
                        
                        if(data.shares.google != '0'){
                          s2s_gplus_b_count.text(data.shares.google);
                        }else{
                          s2s_gplus_b_count.remove();
                        }
                      }else{
                        s2s_gplus_b_count.text(data.shares.google);
                      }

                  }

                  //vk
                  if(jQuery('.s2s_vk_count').val() == "1"){

                    var s2s_vk_count_hide = jQuery('.s2s_vk_count_hide').val();

                    if(s2s_vk_count_hide=='1'){
                      if(data.shares.vk!='0'){
                        s2s_vk_count.text(data.shares.vk);
                      }else{
                        s2s_vk_count.remove();
                      }
                    }else{

                      s2s_vk_count.text(data.shares.vk);
                    }
                  }

              });  

              init_donreach = true;

          }

        });
      }
  }




});



}


