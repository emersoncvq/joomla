/*
 * @version		$Id: allvideoshare.js 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

jQuery( document ).ready(function() {
		
	// Common: On file adding method changed
	jQuery( 'input[type="radio"]', '.avs-file-uploader-types' ).on( 'change', function() {
																					   
		var type;
		
		if ( this.checked ) {
			type = this.value;
		} else {
			type = ( 'url' == this.value ) ? 'upload' : 'url';
		}
		
		jQuery( this ).closest( '.avs-file-uploader' ).find( '.avs-file-uploader-type' ).hide();
		jQuery( this ).closest( '.avs-file-uploader' ).find( '.avs-file-uploader-type-' + type ).show();
		
		// If Videos form, reset required files
		if( jQuery( '#avs-videos' ).length ) {
			jQuery( '#type', '#avs-videos' ).trigger( 'change' );
		}
		
	});
	
	// Common: On file browse button clicked
	jQuery( '.avs-btn-upload' ).on( 'click', function() {
													  
		var $element = jQuery( this ).closest( '.avs-file-uploader-type-upload' );
		
		$element.find( 'input[type="file"]' ).off( 'change' ).on( 'change', function() {
																					 
			var value = jQuery( this ).val().split( '\\' ).pop();
			$element.find( 'input[type="text"]' ).val( value );
			
		}).trigger( 'click' );     
		
	});
	
	// Videos: On type change
	jQuery( '#type', '#avs-videos' ).on( 'change', function() {
					
		var type = jQuery( this ).val();

		if ( 'pro_only' == type ) {
			alert( 'Sorry, this is a PRO feature.' );
			jQuery( this ).val( 'general' );
			type = 'general';
		}
		
		jQuery( '.avs-toggle-fields', '#avs-videos' ).hide();
		jQuery( '.avs-' + type + '-fields', '#avs-videos' ).show();
		
		// Set required
		jQuery( '#video, #upload-video, #streamer, #external, #hls, #thirdparty', '#avs-videos' ).removeClass( 'required' ).removeAttr( 'required' );
		
		switch( type ) {
			case 'general' :
				var option = jQuery( '#avs-file-uploader-video', '#avs-videos' ).find( 'input[type="radio"]:checked' ).val();
				
				if( option == 'url' ) {
					jQuery( '#video', '#avs-videos' ).addClass( 'required' );
				} else {
					jQuery( '#upload-video', '#avs-videos' ).addClass( 'required' );
				}
				break;
			case 'youtube' :
				jQuery( '#external', '#avs-videos' ).addClass( 'required' );
				break;
			case 'vimeo' :
				jQuery( '#external', '#avs-videos' ).addClass( 'required' );
				break;
			case 'rtmp' :
				jQuery( '.avs-hls-fields', '#avs-videos' ).find( '.star' ).hide();
				jQuery( '#streamer, #external', '#avs-videos' ).addClass( 'required' );
				break;
			case 'hls' :
				jQuery( '#hls', '#avs-videos' ).addClass( 'required' );
				jQuery( '.avs-hls-fields', '#avs-videos' ).find( '.star' ).show();
				break;
			case 'thirdparty' :
				jQuery( '#thirdparty', '#avs-videos' ).addClass( 'required' );
				break;
		}
		
	}).trigger( 'change' );
	
	// Players: On type change
	jQuery( 'input[type="radio"][name="type"]', '#avs-players' ).on( 'change', function() {
					
		var type = jQuery( 'input[name="type"]:checked' ).val();
		
		jQuery( '.avs-allvideoshare-fields, .avs-mediaelement-fields', '#avs-players' ).hide();
		jQuery( '.avs-' + type + '-fields', '#avs-players' ).show();
		
	}).trigger( 'change' );

	// Players: On Ad engine change
	jQuery( 'input[type="radio"][name="ad_engine"]', '#avs-players' ).on( 'change', function() {
					
		var type = jQuery( 'input[name="ad_engine"]:checked' ).val();
		
		jQuery( '.avs-ad-custom-fields, .avs-ad-vast-fields', '#avs-players' ).hide();
		jQuery( '.avs-ad-' + type + '-fields', '#avs-players' ).show();
		
	}).trigger( 'change' );
	
	// Configuration Page: On layout changed
	jQuery( '#layout', '#avs-config' ).on( 'change', function() {
					
		var type = jQuery( this ).val();
		
		jQuery( '.avs-toggle-fields', '#avs-config' ).hide();
		jQuery( '.avs-' + type + '-fields', '#avs-config' ).show();
		
	}).trigger( 'change' );
	
	// Configuration Page: On comments type changed
	jQuery( '#comments_type', '#avs-config' ).on( 'change', function() {
					
		var type = jQuery( this ).val();
		
		jQuery( '.avs-facebook-fields', '#avs-config' ).hide();
		jQuery( '.avs-jcomments-fields', '#avs-config' ).hide();
		
		jQuery( '.avs-'+type+'-fields', '#avs-config' ).show();
		
	}).trigger( 'change' );

});