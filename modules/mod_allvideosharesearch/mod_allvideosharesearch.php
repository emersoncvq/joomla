<?php
/*
 * @version		$Id: mod_allvideosharesearch.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/
 
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Include the syndicate functions only once
require_once( JPATH_ROOT . '/administrator/components/com_allvideoshare/libraries/utils.php' );

$app = JFactory::getApplication();
$doc = JFactory::getDocument();

$config = AllVideoShareUtils::getConfig();

$q = '';
if ( 'com_allvideoshare' == $app->input->get('option') && 'search' == $app->input->get('view') ) {
	$q = $app->getUserStateFromRequest( 'com_allvideoshare.search', 'q', '', 'string' );
}

if ( $config->load_bootstrap_css ) {
	$doc->addStyleSheet( AllVideoShareUtils::prepareURL( 'components/com_allvideoshare/assets/css/bootstrap.css' ), 'text/css', 'screen' );
}
		
if ( $config->load_icomoon_font ) {
	$doc->addStyleSheet( JURI::root( true ) . '/media/jui/css/icomoon.css', 'text/css', 'screen' );
}
	
$doc->addStyleSheet( AllVideoShareUtils::prepareURL( 'components/com_allvideoshare/assets/css/allvideoshare.css' ), 'text/css', 'screen' );

if ( ! empty( $config->custom_css ) ) {
	$doc->addStyleDeclaration( $config->custom_css );
}
		
JHTML::_( 'behavior.formvalidation' );

require( JModuleHelper::getLayoutPath( 'mod_allvideosharesearch' ) );