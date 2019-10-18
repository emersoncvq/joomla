<?php
/*
 * @version		$Id: upload.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// include library dependencies
jimport( 'joomla.filesystem.file' );

class AllVideoShareUpload {
	
	public static function doUpload( $field, $dir, $return = '' ) {
	
		$app = JFactory::getApplication();
			
		$fileName = '';
		
		// check whether the file exists
		if ( $files = $app->input->files->get( $field ) ) {
			$fileName = $files['name'];
			$fileTemp = $files['tmp_name'];
			$fileSize = $files['size'];
		}
		
		if ( empty( $fileName ) ) {
			return $return;
		}
		
		$format = strtolower( JFile::getExt( $fileName ) );		
		$allowable = array( 'jpeg', 'jpg', 'png', 'gif', 'mp4', 'm4v', 'mov', 'webm', 'ogv', 'flv' );
		if ( ! in_array( $format, $allowable ) ) {
			return;
		}
		
		$imginfo = null;
		$images = array( 'jpeg', 'jpg', 'png', 'gif' );
		
		if ( in_array( $format, $images ) ) {
		
			if ( ( $imginfo = getimagesize( $fileTemp ) ) === FALSE ) {
				return;
			}
			
		} else {
		
			$allowed = false;
			$allowed_mime = array( 'video/*' );
			$illegal_mime = array( 'application/x-shockwave-flash', 'application/msword', 'application/excel', 'application/pdf', 'application/powerpoint', 'application/x-zip', 'text/plain', 'text/css', 'text/html', 'text/php', 'text/x-php', 'application/php', 'application/x-php', 'application/x-httpd-php', 'application/x-httpd-php-source' );	
			
			if ( function_exists( 'finfo_open' ) ) {
				
				$finfo = finfo_open( FILEINFO_MIME );
				$type = finfo_file( $finfo, $fileTemp );				
				finfo_close( $finfo );
				
			} elseif ( function_exists( 'mime_content_type' ) ) {			
				$type = mime_content_type( $fileTemp );
			}
			
			if ( strlen( $type ) && ! in_array( $type, $illegal_mime ) ) {
			
				list( $m1, $m2 )= explode( '/', $type );
				
				foreach ( $allowed_mime as $k => $v ) {
                   	list ( $v1, $v2 ) = explode( '/', $v );
                   	if ( ( $v1 == '*' && $v2 == '*' ) || ( $v1 == $m1 && ( $v2 == $m2 || $v2 == '*' ) ) ) {
                       	$allowed = true;
                       	break;
                   	}
               	}
				
				if ( $allowed == false ) return;
				
			}			
		}
		
		$xss_check = JFile::read( $fileTemp, false, 256 );
		$html_tags = array( 'abbr', 'acronym', 'address', 'applet', 'area', 'audioscope', 'base', 'basefont', 'bdo', 'bgsound', 'big', 'blackface', 'blink', 'blockquote', 'body', 'bq', 'br', 'button', 'caption', 'center', 'cite', 'code', 'col', 'colgroup', 'comment', 'custom', 'dd', 'del', 'dfn', 'dir', 'div', 'dl', 'dt', 'em', 'embed', 'fieldset', 'fn', 'font', 'form', 'frame', 'frameset', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'head', 'hr', 'html', 'iframe', 'ilayer', 'img', 'input', 'ins', 'isindex', 'keygen', 'kbd', 'label', 'layer', 'legend', 'li', 'limittext', 'link', 'listing', 'map', 'marquee', 'menu', 'meta', 'multicol', 'nobr', 'noembed', 'noframes', 'noscript', 'nosmartquotes', 'object', 'ol', 'optgroup', 'option', 'param', 'plaintext', 'pre', 'rt', 'ruby', 's', 'samp', 'script', 'select', 'server', 'shadow', 'sidebar', 'small', 'spacer', 'span', 'strike', 'strong', 'style', 'sub', 'sup', 'table', 'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead', 'title', 'tr', 'tt', 'ul', 'var', 'wbr', 'xml', 'xmp', '!DOCTYPE', '!--' );
		foreach ( $html_tags as $tag ) {
			if ( stristr( $xss_check, '<' . $tag . ' ' ) || stristr( $xss_check, '<' . $tag . '>' ) || stristr( $xss_check, '<?php' ) ) {
				return;
			}
		}
 
		// remove anything that is not a-z, 0-9 or a dot from the file name
 		$fileName = preg_replace( "/[^a-zA-Z0-9.]/", "", $fileName );
		$fileName = strtolower( $fileName );
		
		// add some unique strings in the file name to avoid issues with the
		// files uploaded in the same name
		$fileName = JFile::stripExt( $fileName );		
		$fileName = uniqid( $fileName ) . '.' . $format;
		
		// upload
		$uploadPath = ALLVIDEOSHARE_UPLOAD_BASE . $dir . '/' . $fileName;
 
		if ( ! JFile::upload( $fileTemp, $uploadPath ) ) {
       		$app->enqueueMessage( JText::_( 'ERROR_MOVING_FILE' ), 'error' );
        	return;
		}
		
		return ALLVIDEOSHARE_UPLOAD_BASEURL . $dir . '/' . $fileName;
		
    }
		
}