<?php
/*
 * @version		$Id: view.html.php 3.3.0 2019-01-25 $
 * @package		Joomla
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class AllVideoShareViewUser extends AllVideoShareView {

    public function display( $tpl = null ) {
	
		$app = JFactory::getApplication();
		 
		if ( ! ALLVIDEOSHARE_USERID ) {
			$uri = JFactory::getURI();
			$loginURL = 'index.php?option=com_users&view=login&Itemid=' . $app->input->getInt( 'Itemid' ) . '&return=' . base64_encode( $uri->toString() );
			$app->redirect( $loginURL, JText::_( 'YOU_NEED_TO_REGISTER_TO_VIEW_THIS_PAGE' ) );	
			return true;
		}
		
		$this->limit = $app->getUserStateFromRequest( 'global.list.limit', 'limit', 10, 'int' );
		$this->limitstart = $app->input->get( 'limitstart', '0', 'INT' );
		$this->limitstart = ( $this->limit != 0 ? ( floor( $this->limitstart / $this->limit ) * $this->limit ) : 0 );

		$this->config = AllVideoShareUtils::getConfig();
		
		$model = $this->getModel();
		 
		$this->items = $model->getItems();
		$this->pagination = $model->getPagination();
		$this->params = $app->getParams();
		$this->s = $app->getUserStateFromRequest( 'com_allvideoshare.user.search', 's', '', 'string' );		
		
		$this->setHeaders();
				
        parent::display( $tpl );
		
    }
	
	public function add( $tpl = null ) {
		
		$app = JFactory::getApplication();
		 
		if ( ! ALLVIDEOSHARE_USERID ) {
			$uri = JFactory::getURI();
			$loginURL = 'index.php?option=com_users&view=login&Itemid=' . $app->input->getInt( 'Itemid' ) . '&return=' . base64_encode( $uri->toString() );
			$app->redirect( $loginURL, JText::_( 'YOU_NEED_TO_REGISTER_TO_VIEW_THIS_PAGE' ) );	
			return true;
		}
		
		$this->config = AllVideoShareUtils::getConfig();
		$this->params = $app->getParams();
		
		$this->setHeaders();
				
        parent::display( $tpl );
		
    }
	
	public function edit( $tpl = null ) {
		
		$app = JFactory::getApplication();
		 
		if ( ! ALLVIDEOSHARE_USERID ) {
			$uri = JFactory::getURI();
			$loginURL = 'index.php?option=com_users&view=login&Itemid=' . $app->input->getInt( 'Itemid' ) . '&return=' . base64_encode( $uri->toString() );
			$app->redirect( $loginURL, JText::_( 'YOU_NEED_TO_REGISTER_TO_VIEW_THIS_PAGE' ) );	
			return true;
		}
		
		$this->config = AllVideoShareUtils::getConfig();
		
		$model = $this->getModel();
		
		$this->item = $model->getItem();
		$this->params = $app->getParams();
		
		$this->setHeaders();
				
        parent::display( $tpl );
		
    }
	
	public function setHeaders() {
	
		$doc = JFactory::getDocument();
		
	    if ( $this->params->get( 'menu-meta_keywords' ) ) {
			$doc->setMetadata( 'keywords', $this->params->get( 'menu-meta_keywords' ) );
		}
		
		if ( $this->params->get( 'menu-meta_description' ) ) {
			$doc->setDescription( $this->params->get( 'menu-meta_description' ) );
		}

		if ( $this->params->get( 'robots' ) ) {
			$doc->setMetadata( 'robots', $this->params->get( 'robots' ) );
		}
		
		if ( $this->config->load_bootstrap_css ) {
			$doc->addStyleSheet( AllVideoShareUtils::prepareURL( 'components/com_allvideoshare/assets/css/bootstrap.css' ), 'text/css', 'screen' );
		}
		
		if( $this->config->load_icomoon_font ) {
			$doc->addStyleSheet( JURI::root( true ) . '/media/jui/css/icomoon.css', 'text/css', 'screen' );
		}

		$doc->addStyleSheet( AllVideoShareUtils::prepareURL( 'components/com_allvideoshare/assets/css/allvideoshare.css' ), 'text/css', 'screen' );
		
		if ( ! empty( $this->config->custom_css ) ) {
			$doc->addStyleDeclaration( $this->config->custom_css );
		}
		
		JHTML::_( 'behavior.formvalidation' );
		JHtml::_( 'jquery.framework' );
		$doc->addScript( AllVideoShareUtils::prepareURL( 'components/com_allvideoshare/assets/js/allvideoshare.js' ) );
		
    }
	
}