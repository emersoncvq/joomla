<?php
/*
 * @version		$Id: dashboard.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class AllVideoShareControllerDashboard extends AllVideoShareController {

   public function __construct() { 
          
        $this->item_type = 'Default';		
        parent::__construct();
		
    }
	
	public function dashboard() {
	
	    $model = $this->getModel( 'dashboard' );
		
	    $view = $this->getView( 'dashboard', 'html' );
        $view->setModel( $model, true );
		$view->setLayout( 'default' );
		$view->display();
		
	}
		
}