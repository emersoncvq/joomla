<?php
/*
 * @version		$Id: categories.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class AllVideoShareControllerCategories extends AllVideoShareController {
	
	public function categories() {
	    
		$model = $this->getModel( 'categories' );
		
	    $view = $this->getView( 'categories', 'html' );		
        $view->setModel( $model, true );
		$view->setLayout( 'default' );
		$view->display();
		
	}
	
	public function add() {
		
		$model = $this->getModel( 'categories' );	
		
	    $view = $this->getView( 'categories' , 'html' );
        $view->setModel( $model, true );
		$view->setLayout( 'add' );
		$view->add();
		
	}
	
	public function edit()	{
	
		AllVideoShareUtils::checkToken();
		
		$model = $this->getModel( 'categories' );
		
	    $view = $this->getView( 'categories' , 'html' );
        $view->setModel( $model, true );
		$view->setLayout( 'edit' );
		$view->edit();
		
	}
		
	public function delete() {
	
		AllVideoShareUtils::checkToken();
		
		$model = &$this->getModel( 'categories' );
	 	$model->delete();
		
	}
	
	public function publish() {
	
		AllVideoShareUtils::checkToken();
		
		$model = $this->getModel( 'categories' );
        $model->publish();
		
    }
	
    public function unpublish() {
        $this->publish();
    }	
	
	public function save()	{
	
		AllVideoShareUtils::checkToken();
		
		$model = $this->getModel( 'categories' );
	  	$model->save();
		
	}
	
	public function apply() {
		$this->save();
	}
	
	public function cancel() {
	
		AllVideoShareUtils::checkToken();
		
		$model = $this->getModel( 'categories' );
	    $model->cancel();
		
	}
	
	public function saveorder() {
	
		$model = $this->getModel( 'categories' );
	  	$model->saveOrder();	
			
	}
	
	public function orderup() {
		
		$model = $this->getModel( 'categories' );
		$model->move( -1 );
		
	}
	
	public function orderdown() {
		
		$model = $this->getModel( 'categories' );
		$model->move( 1 );
		
	}	
		
}