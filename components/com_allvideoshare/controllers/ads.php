<?php
/*
 * @version		$Id: ads.php 3.3.0 2019-01-10 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class AllVideoShareControllerAds extends AllVideoShareController {
	
	public function vast() {
		$model = $this->getModel( 'ads' );
		
	    $view = $this->getView( 'ads', 'xml' );	
		$view->setModel( $model, true );		
		$view->setLayout( 'vast' );
		$view->vast();
	}

	public function vmap() {
		$model = $this->getModel( 'ads' );
		
	    $view = $this->getView( 'ads', 'xml' );	
		$view->setModel( $model, true );		
		$view->setLayout( 'vmap' );
		$view->vmap();
	}

	public function impression() {
		$model = $this->getModel( 'ads' );
		$model->impression();
	}

	public function click() {
		$model = $this->getModel( 'ads' );
		$model->click();
	}
			
}