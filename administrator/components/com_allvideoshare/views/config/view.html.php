<?php
/*
 * @version		$Id: view.html.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class AllVideoShareViewConfig extends AllVideoShareView {

    public function display( $tpl = null ) {
	
	    $model = $this->getModel();
		
		$this->item = $model->getItem();
		
		JToolBarHelper::title( JText::_( 'ALL_VIDEO_SHARE' ), 'cogs' );	
		JToolBarHelper::save( 'save', JText::_( 'SAVE' ) );			
		
		AllVideoShareUtils::subMenus();	
		
        parent::display( $tpl );
		
    }
	
}