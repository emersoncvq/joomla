<?php
/*
 * @version		$Id: allvideoshare.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

$app = JFactory::getApplication();
$user = JFactory::getUser();

// define constants
define( 'ALLVIDEOSHARE_UPLOAD_DIR', '/media/com_allvideoshare/' );
define( 'ALLVIDEOSHARE_UPLOAD_BASE', JPATH_ROOT . ALLVIDEOSHARE_UPLOAD_DIR );
define( 'ALLVIDEOSHARE_UPLOAD_BASEURL', JURI::root( true ) . ALLVIDEOSHARE_UPLOAD_DIR );
define( 'ALLVIDEOSHARE_USERID', $user->get( 'id' ) );
define( 'ALLVIDEOSHARE_USERNAME', $user->get( 'username' ) );
define( 'ALLVIDEOSHARE_VERSION', '3.3.0' );

// register libraries
JLoader::register( 'AllVideoShareController', JPATH_COMPONENT_ADMINISTRATOR . '/controllers/controller.php' );
JLoader::register( 'AllVideoShareModel', JPATH_COMPONENT_ADMINISTRATOR . '/models/model.php' );
JLoader::register( 'AllVideoShareView', JPATH_COMPONENT_ADMINISTRATOR . '/views/view.php' );
JLoader::register( 'AllVideoShareHtml', JPATH_COMPONENT_ADMINISTRATOR . '/libraries/html.php' );
JLoader::register( 'AllVideoShareUpload', JPATH_COMPONENT_ADMINISTRATOR . '/libraries/upload.php' );
JLoader::register( 'AllVideoShareUtils', JPATH_COMPONENT_ADMINISTRATOR . '/libraries/utils.php' );

// require the base controller
$slug = AllVideoShareUtils::getSlug();
if ( 'category' == $app->input->get( 'view' ) && empty( $slug ) ) {
	$app->input->set( 'view', 'categories' );
} elseif ( 'video' == $app->input->get( 'view' ) && ! ( $slug || $app->input->get( 'tmpl' ) ) ) {
	$app->input->set( 'view', 'videos' );
}
$view = $app->input->get( 'view', 'categories' );

if ( 'player' == $view || 'ads' == $view ) {
	$app->input->set( 'tmpl', 'component' );
}

$controller = JString::strtolower( $view );
require_once JPATH_COMPONENT . "/controllers/{$controller}.php";

// initialize the controller
$obj = 'AllVideoShareController' . $controller;
$controller = new $obj();

// perform the request task
$controller->execute( $app->input->get( 'task', $view ) );
$controller->redirect();