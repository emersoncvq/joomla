<?php
/*
 * @version		$Id: view.html.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class AllVideoShareViewCommercials extends AllVideoShareView {

    public function display( $tpl = null ) {
	
		$app = JFactory::getApplication();	
		
		$option = $app->input->get( 'option' );
		$view = $app->input->get( 'view' );
		
	    $model = $this->getModel();
		
		$limit = $app->getUserStateFromRequest( 'global.list.limit', 'limit', $app->getCfg( 'list_limit' ), 'int' );
		$limitstart = $app->getUserStateFromRequest( $option . $view . '.limitstart', 'limitstart', 0, 'int' );
		$this->limitstart = ( $limit != 0 ? ( floor( $limitstart / $limit ) * $limit ) : 0 );
		
		$this->items = $model->getItems();
		$this->pagination = $model->getPagination();
		$this->lists = $model->getLists();
		
		JToolBarHelper::title( JText::_( 'ALL_VIDEO_SHARE' ), 'bars' );
		JToolBarHelper::publishList( 'publish', JText::_( 'PUBLISH' ) );
        JToolBarHelper::unpublishList( 'unpublish', JText::_( 'UNPUBLISH' ) );
        JToolBarHelper::deleteList( JText::_( 'ARE_YOU_SURE_WANT_TO_DELETE_SELECTED_ITEMS' ), 'delete', JText::_( 'DELETE' ) );
        JToolBarHelper::editList( 'edit', JText::_( 'EDIT' ) );
        JToolBarHelper::addNew( 'add', JText::_( 'NEW' ) );
		
		AllVideoShareUtils::subMenus();
		
        parent::display( $tpl );
		
    }
	
	public function add( $tpl = null ) {
		
		JToolBarHelper::title( JText::_( 'ADD_NEW_ADVERT' ), 'bars' );
		JToolBarHelper::save( 'save', JText::_( 'SAVE' ) );
        JToolBarHelper::apply( 'apply', JText::_( 'APPLY' ) );
        JToolBarHelper::cancel( 'cancel', JText::_( 'CANCEL' ) );
		
        parent::display( $tpl );
		
    }
	
	public function edit( $tpl = null ) {
	
	    $model = $this->getModel();
		
		$this->item = $model->getItem();
		
		JToolBarHelper::title( JText::_( 'EDIT_THE_ADVERT' ), 'bars' );
		JToolBarHelper::save( 'save', JText::_( 'SAVE' ) );
        JToolBarHelper::apply( 'apply', JText::_( 'APPLY' ) );
        JToolBarHelper::cancel( 'cancel', JText::_( 'CANCEL' ) );
		
        parent::display( $tpl );
		
    }
	
}