jQuery(document).ready(function(e) {

  art_pos_fixed();
  category_same_article();


  s2s_interface_j3();
  s2s_interface_j4();

  var license = jQuery('.jtotal_license_check').val();


//update from social2s_v3

var s2sv3_email = jQuery('#jform_params_s2s_license_email').val();
if(s2sv3_email != ''){
  if(jQuery('#jform_params_jtotal_email').val() == ''){
    jQuery('#jform_params_jtotal_email').val(s2sv3_email);
  }
  if(jQuery('#jform_params_jtotal_key').val() == ''){
    jQuery('#jform_params_jtotal_key').val('SOCIAL2SV3');
  } 
}





  if(license=='0'){
    /*
    jQuery('.fa-star').parent().click(function(){
        //alert(license);

        var mylicensewarning = '<div class="alert alert-error"> This is a PRO feature. Changes will not be saved. </div>';
        
        if(jQuery(this).parents('.control-group').children('.alert').length <= 0){
          jQuery(this).parents('.control-group').append(mylicensewarning);
        }

    });
  */

    //disable button
    var queen = jQuery('.fa-chess-queen').parent().addClass('disabled').css("pointer-events","none");

    /*queen.each(function(){
      var mifor = jQuery(this).attr('for');

      if(mifor){
        jQuery('#'+mifor).addClass('disabled').css("pointer-events","none");
      }

      
    });*/
     


    //addClass('disabled').css("pointer-events","none");
    

    //jQuery('.fa-star').parent().attr( "disabled", "disabled" );

    //funny error with modern base
    //jQuery('#jform_params_social2s_base1').attr('checked', 'checked');
    //jQuery('#jform_params_social2s_base1').attr('checked', '');
  }


  //current version
  var current_version = jQuery('#s2s_db_version').val();


  //INTERFACE//
  function s2s_interface_j3(){
    if(jversion > '3') return;

    //ACCORDIONS
    jQuery('.accordion-group').each(function(index, value){

      jQuery(this).children('.accordion-body').append(jQuery(this).children('.control-group'));
      jQuery(this).unwrap();

    });

    change_s2s_version_j3();

    jQuery('.s2s_version h4').append(' <span class="badge">db:'+current_version+'</span>');
    
    jQuery('body').addClass('s2s_j3');

    /*TO IMPROVE scrollto*/
      jQuery('.accordion-heading').click(function(){

          jQuery('html').animate({
              scrollTop: jQuery(this).offset().top-140
          }, 600).delay( 600 );

      });
  
   
    //coloco todos los estados de acorderon abiertos
    jQuery.each(jQuery('.accordion-body'), function(){
           
      if(jQuery(this).hasClass('in')){
        jQuery(this).parent().addClass('s2s_acc_open');
      }else{
        jQuery(this).parent().removeClass('s2s_acc_open');
      }

    });
  }

  function s2s_interface_j4(){
    if(jversion < '4') return;

    //ACCORDIONS
    jQuery('.card').each(function(index, value){
      jQuery(this).siblings('.control-label').addClass("s2s_group");
      jQuery(this).find('.card-body').append(jQuery(this).children('.control-group'));


      jQuery(this).unwrap();
    });

    //current version
    jQuery('.s2s_version h4').append(' <span class="badge">db:'+current_version+'</span>');

    change_s2s_version_j4();


    //body class
    jQuery('body').addClass('s2s_j4');

   
  }




  /*ORDER*/
  jQuery( "#sortable" ).sortable({

    stop:function(event,ui){ 

      //get order
      var orden = jQuery('.s2s_order li');
      var order_num = '';

      //bucle order
      jQuery.each(orden, function(index, value){

        order_num += jQuery(value).attr('data-s2s');
        if(index <=12){
          order_num +=',';
        }
      });

      jQuery('.input_s2s_orden').val(order_num);
      console.log(order_num);
     
    }

  });

  //click
  var clicked = false;
 
 jQuery('.accordion-heading').click(function(event){
    //event.preventDefault();
    if(clicked) return;
    clicked = true;
    var open  = jQuery(this).next().hasClass('in');
    
    if(!open){
      jQuery(this).parent().addClass('s2s_acc_open');
    }else{
      jQuery(this).parent().removeClass('s2s_acc_open');
    }
    setTimeout(function(){
      clicked = false;
    }, 400);

  });





});


function check_license(v3 = false){

  if(jQuery('#jform_params_jtotal_email').val() == ''){
     jQuery('#jform_params_jtotal_email').addClass('invalid');
  }else if(v3){
    jQuery('#jform_params_jtotal_key').val('SOCIAL2SV3');
    Joomla.submitbutton('plugin.apply');
  }else{
    Joomla.submitbutton('plugin.apply');
  }

}




function change_s2s_version_j3(){
  if(jQuery('.s2s_version').length >= 1){
   //logo
   var logo = jQuery('.s2s_logo_portada').parent().parent();
   jQuery('#general div.span3').append('<div class="control-group s2s_logo_right"></div>');
   jQuery('.s2s_logo_right').append(logo);
       //version
   var bloque = jQuery('.s2s_version').parent().parent();
   jQuery('#general div.span3').append(bloque);
  }

}

function change_s2s_version_j4(){

  if(jQuery('.s2s_version').length >= 1){
   //logo
   var logo = jQuery('.s2s_logo_portada').parent().parent();
   jQuery('#general > div.row > div.card div.card-body').append('<div class="control-group s2s_logo_right"></div>');
   jQuery('.s2s_logo_right').append(logo);
       //version
   var bloque = jQuery('.s2s_version').parent().parent();
   jQuery('#general > div.row > div.card div.card-body').append(bloque);
  }
}


function apply_cool_stuff(){
  //modern
  jQuery('#jform_params_social2s_base0').attr('checked', '');
  jQuery('#jform_params_social2s_base1').attr('checked', 'checked');
  //twitter cards
  jQuery('#jform_params_twitter_cards0').attr('checked', '');
  jQuery('#jform_params_twitter_cards1').attr('checked', 'checked');
  //fill mode
  jQuery('#jform_params_s2s_art_fill0').attr('checked', '');
  jQuery('#jform_params_s2s_art_fill1').attr('checked', '');
  jQuery('#jform_params_s2s_art_fill2').attr('checked', 'checked');

  //fast load twitter
  jQuery('#jform_params_twitter_fast_as_light0').attr('checked', '');
  jQuery('#jform_params_twitter_fast_as_light1').attr('checked', 'checked');

  //remove credits
  jQuery('#jform_params_s2s_credits0').attr('checked', 'checked');
  jQuery('#jform_params_s2s_credits0').attr('checked', '');

  Joomla.submitbutton('plugin.apply');

}


function art_pos_fixed(){

  //actualizo las clases de categoria
  var pos_fixed_button = jQuery("#jform_params_s2s_art_fixed input:checked").val();

  //if same, invalido valores
  if(pos_fixed_button==0){
    jQuery("#jform_params_s2s_art_mobile_min").addClass('disabled');
    jQuery("#jform_params_s2s_art_fixed_mode").addClass('disabled');
    jQuery("#jform_params_s2s_art_fixed_posx").addClass('disabled');
    jQuery("#jform_params_s2s_art_fixed_posy").addClass('disabled');

    //jQuery("#jform_params_s2s_takefromarticle").removeClass('disabled');
  }else{
    jQuery("#jform_params_s2s_art_mobile_min").removeClass('disabled');
    jQuery("#jform_params_s2s_art_fixed_mode").removeClass('disabled');
    jQuery("#jform_params_s2s_art_fixed_posx").removeClass('disabled');
    jQuery("#jform_params_s2s_art_fixed_posy").removeClass('disabled');
  }

  //modify on click
  jQuery("#jform_params_s2s_art_fixed").on('change', function(){

    var pos_fixed_button = jQuery("#jform_params_s2s_art_fixed input:checked").val();
    if(pos_fixed_button==0){
      jQuery("#jform_params_s2s_art_mobile_min").addClass('disabled');
      jQuery("#jform_params_s2s_art_fixed_mode").addClass('disabled');
      jQuery("#jform_params_s2s_art_fixed_posx").addClass('disabled');
      jQuery("#jform_params_s2s_art_fixed_posy").addClass('disabled');
      //jQuery("#jform_params_s2s_takefromarticle").removeClass('disabled');
    }else{
      var s2s_backend_license_js_check = jQuery(".s2s_backend_license_js_check").val();
      
      if(s2s_backend_license_js_check){
        jQuery("#jform_params_s2s_art_mobile_min").removeClass('disabled');
        jQuery("#jform_params_s2s_art_fixed_mode").removeClass('disabled');
        jQuery("#jform_params_s2s_art_fixed_posx").removeClass('disabled');
        jQuery("#jform_params_s2s_art_fixed_posy").removeClass('disabled');
      }else{
        jQuery(this).children('.active').removeClass('btn-success');
        if(jQuery(this).parent().children('.s2slicensemsg').length <=0){
          jQuery(this).after('<span class="alert alert-warning s2slicensemsg">Pro license required</span>');
        }

      }

    }

  });

}

function category_same_article(){

  //actualizo las clases de categoria
  var cat_same = jQuery("#jform_params_s2s_takefromarticle input:checked").val();

  //if same, invalido valores
  if(cat_same==1){
    jQuery("#attrib-SOCIAL2S_CATEGORIES #jform_params_s2s_display_cat_view").hide();
    jQuery("#jform_params_s2s_display_cat_view-lbl").hide();
    jQuery("#jform_params_s2s_takefromarticle").show();
    jQuery("#attrib-SOCIAL2S_CATEGORIES #category_custom").parent().hide();
    
  }else{

    jQuery("#attrib-SOCIAL2S_CATEGORIES #jform_params_s2s_display_cat_view").show();
    jQuery("#jform_params_s2s_display_cat_view-lbl").show();
    jQuery("#attrib-SOCIAL2S_CATEGORIES #category_custom").parent().show();

  }


  //modify on click
  jQuery("#jform_params_s2s_takefromarticle").on('change', function(){

    var cat_same = jQuery("#jform_params_s2s_takefromarticle input:checked").val();
    if(cat_same==1){
      jQuery("#attrib-SOCIAL2S_CATEGORIES #jform_params_s2s_display_cat_view").hide();
      jQuery("#jform_params_s2s_display_cat_view-lbl").hide();
      jQuery("#jform_params_s2s_takefromarticle").show();
      jQuery("#attrib-SOCIAL2S_CATEGORIES #category_custom").parent().hide();
    }else{
      jQuery("#attrib-SOCIAL2S_CATEGORIES #jform_params_s2s_display_cat_view").show();
      jQuery("#attrib-SOCIAL2S_CATEGORIES #category_custom").parent().show();
      jQuery("#jform_params_s2s_display_cat_view-lbl").show();
    }

  });
 


}
