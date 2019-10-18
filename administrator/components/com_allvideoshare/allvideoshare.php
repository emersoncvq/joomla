<?php
/*
 * @version		$Id: allvideoshare.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$app = JFactory::getApplication();
$user = JFactory::getUser();

// define constants
define( 'ALLVIDEOSHARE_UPLOAD_DIR', '/media/com_allvideoshare/' );
define( 'ALLVIDEOSHARE_UPLOAD_BASE', JPATH_ROOT . ALLVIDEOSHARE_UPLOAD_DIR );
define( 'ALLVIDEOSHARE_UPLOAD_BASEURL', JURI::root( true ) . ALLVIDEOSHARE_UPLOAD_DIR );
define( 'ALLVIDEOSHARE_USERID', $user->get( 'id' ) );
define( 'ALLVIDEOSHARE_USERNAME', $user->get( 'username' ) );

// register libraries
JLoader::register( 'AllVideoShareController', JPATH_COMPONENT . '/controllers/controller.php' );
JLoader::register( 'AllVideoShareModel', JPATH_COMPONENT . '/models/model.php' );
JLoader::register( 'AllVideoShareView', JPATH_COMPONENT . '/views/view.php' );
JLoader::register( 'AllVideoShareHtml', JPATH_COMPONENT . '/libraries/html.php' );
JLoader::register( 'AllVideoShareUpload', JPATH_COMPONENT . '/libraries/upload.php' );
JLoader::register( 'AllVideoShareUtils', JPATH_COMPONENT . '/libraries/utils.php' );

// require the base controller
$view = $app->input->get( 'view', 'dashboard' );
$controller = JString::strtolower( $view );
require_once JPATH_COMPONENT . "/controllers/{$controller}.php";

// initialize the controller
$obj = 'AllVideoShareController' . $controller;
$controller = new $obj();

// perform the request task
$task = $app->input->get( 'task', $view );
$controller->execute( $task );
$controller->redirect();