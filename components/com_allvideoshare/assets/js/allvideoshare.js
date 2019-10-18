/*
 * @version		$Id: allvideoshare.js 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

jQuery( document ).ready(function() {
		
	// Video Submission Form: On file adding method changed
	jQuery( 'input[type="radio"]', '.avs-file-uploader-types' ).on( 'change', function() {
																					   
		var type;
		
		if ( this.checked ) {
			type = this.value;
		} else {
			type = ( 'url' == this.value ) ? 'upload' : 'url';
		}
		
		jQuery( this ).closest( '.avs-file-uploader' ).find( '.avs-file-uploader-type' ).hide();
		jQuery( this ).closest( '.avs-file-uploader' ).find( '.avs-file-uploader-type-' + type ).show();
		jQuery( '#type', '#avs-videos' ).trigger( 'change' );
		
	});
	
	// Video Submission Form: On file browse button clicked
	jQuery( '.avs-btn-upload' ).on( 'click', function() {
													  
		var $element = jQuery( this ).closest( '.avs-file-uploader-type-upload' );
		
		$element.find( 'input[type="file"]' ).off( 'change' ).on( 'change', function() {
																					 
			var value = jQuery( this ).val().split( '\\' ).pop();
			$element.find( 'input[type="text"]' ).val( value );
			
		}).trigger( 'click' );     
		
	});
	
	// Video Submission Form: On type change
	jQuery( '#type', '#avs-videos' ).on( 'change', function() {
					
		var type = jQuery( this ).val();
		
		jQuery( '.avs-toggle-fields', '#avs-videos' ).hide();
		jQuery( '.avs-'+type+'-fields', '#avs-videos' ).show();
		
		// Set required
		jQuery( '#video, #upload-video, #streamer, #external, #hls', '#avs-videos' ).removeClass( 'required' ).removeAttr( 'required' );
		
		switch ( type ) {
			case 'general':
				var option = jQuery( '#avs-file-uploader-video', '#avs-videos' ).find( 'input[type="radio"]:checked' ).val();
				
				if ( 'url' == option ) {
					jQuery( '#video', '#avs-videos' ).addClass( 'required' );
				} else {
					jQuery( '#upload-video', '#avs-videos' ).addClass( 'required' );
				}
				break;
			case 'youtube':
				jQuery( '#external', '#avs-videos' ).addClass( 'required' );
				break;
			case 'vimeo':
				jQuery( '#external', '#avs-videos' ).addClass( 'required' );
				break;
			case 'rtmp':
				jQuery( '.avs-hls-fields', '#avs-videos' ).find( '.star' ).hide();
				jQuery( '#streamer, #external', '#avs-videos' ).addClass( 'required' );
				break;
			case 'hls':
				jQuery( '#hls', '#avs-videos' ).addClass( 'required' );
				jQuery( '.avs-hls-fields', '#avs-videos' ).find( '.star' ).show();
				break;
		}
		
	}).trigger( 'change' );
	
	// GDPR
	jQuery( '.avs-gdpr-consent-button' ).on( 'click', function() {	
		var $this = jQuery( this );
		$this.html( '...' );

		// Set Cookie
		var xmlhttp;

		if ( window.XMLHttpRequest ) {
			xmlhttp = new XMLHttpRequest();
		} else {
			xmlhttp = new ActiveXObject( 'Microsoft.XMLHTTP' );
		};
		
		xmlhttp.onreadystatechange = function() {                
			if ( 4 == xmlhttp.readyState && 200 == xmlhttp.status ) {                    
				if ( 'success' == xmlhttp.responseText ) {
					var container = $this.closest( '.avs-player' );
		
					var iframe = container.find( 'iframe' ).clone();
					var src = iframe.data( 'src' );
					iframe.attr( 'src', src );
					
					container.html( iframe );	
				}                        
			}                    
		};	

		xmlhttp.open( 'GET', jQuery( this ).data( 'baseurl' ) + 'index.php?option=com_allvideoshare&view=player&task=gdpr&format=raw', true );
		xmlhttp.send();	
	});	
	
});