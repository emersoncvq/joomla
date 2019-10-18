<?php
/*
 * @version		$Id: view.html.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class AllVideoShareViewSearch extends AllVideoShareView {

	public function display( $tpl = null ) {
	
	    $app = JFactory::getApplication();
		
		$this->config = AllVideoShareUtils::getConfig();
		
		$model = $this->getModel();

		$this->params = $app->getParams();
		
		$this->rows = $this->params->get( 'no_of_rows', $this->config->rows );
		$this->cols = $this->params->get( 'no_of_cols', $this->config->cols );
		
		$limit = (int) $this->rows * (int) $this->cols;
		$this->items = $model->getItems( $limit );
		$this->pagination = $model->getPagination();
		
		$this->q = $app->getUserStateFromRequest( 'com_allvideoshare.search', 'q', '', 'string' );
		
		$this->setHeaders();
		
        parent::display( $tpl );
		
    }
	
	public function setHeaders() {
	
		$doc = JFactory::getDocument();
		
		if ( $this->config->load_bootstrap_css ) {
			$doc->addStyleSheet( AllVideoShareUtils::prepareURL( 'components/com_allvideoshare/assets/css/bootstrap.css' ), 'text/css', 'screen' );
		}
		
		if ( $this->config->load_icomoon_font ) {
			$doc->addStyleSheet( JURI::root( true ) . '/media/jui/css/icomoon.css', 'text/css', 'screen' );
		}

		$doc->addStyleSheet( AllVideoShareUtils::prepareURL( 'components/com_allvideoshare/assets/css/allvideoshare.css' ), 'text/css', 'screen' );
		
		if ( ! empty( $this->config->custom_css ) ) {
			$doc->addStyleDeclaration( $this->config->custom_css );
		}
	
	}
	
}