<?php
/*
 * @version		$Id: view.html.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class AllVideoShareViewCategory extends AllVideoShareView {

    public function display( $tpl = null ) {
	
	    $app = JFactory::getApplication();
		
		$this->config = AllVideoShareUtils::getConfig();
		
		$model = $this->getModel();

		$this->item = $model->getItem();
		
		if ( ! $this->item ) {
			$app->enqueueMessage( JText::_( 'ITEM_NOT_FOUND' ), 'notice' );
			return true;
		}
		
		if ( ! AllVideoShareUtils::hasPermission( $this->item->access ) ) {
		
			if ( ! ALLVIDEOSHARE_USERID ) {
				$uri = JFactory::getURI();
				$loginURL = JRoute::_( 'index.php?option=com_users&view=login&return=' . base64_encode( $uri->toString() ) . '&Itemid=' . $app->input->getInt( 'Itemid' ) );
				$app->redirect( $loginURL, JText::_( 'YOU_NEED_TO_REGISTER_TO_VIEW_THIS_PAGE' ) );	
			} else {
				$app->enqueueMessage( JText::_( 'ACCESS_DENIED' ), 'notice' );
			}
			
			return true;
			
		}

		$this->params = $app->getParams();
		
		$this->rows = $this->params->get( 'no_of_rows', $this->config->rows );
		$this->cols = $this->params->get( 'no_of_cols', $this->config->cols );

		$limit = (int) $this->rows * (int) $this->cols;
		$this->videos = $model->getVideos( $this->item->id, $this->params, $limit );
		$this->pagination = $model->getPagination( $this->item->id, $this->params );
		$this->feedHTML = $this->getFeedHTML();
		
		$this->categories = $model->getSubCategories( $this->item->id, $this->params );
		
		if ( ! $this->videos && ! $this->categories ) {
			$app->enqueueMessage( JText::_( 'ITEM_NOT_FOUND' ), 'notice' );
			return true;
		}

		$this->setHeaders();
		$this->generateBreadcrumbs( $this->item );
				
        parent::display( $tpl );
		
    }
	
	public function getFeedHTML() {
	
		$html = '';
		
		if ( $this->config->show_feed ) {
			$slug = AllVideoShareUtils::getSlug();
			
			$url  = JRoute::_( "index.php?option=com_allvideoshare&view=category&slg=$slug" );
			$url .= ! strpos( $url, '?' ) ? '?format=feed&type=rss' : '&format=feed&type=rss';
			
			$image = JURI::root( true ) . "/components/com_allvideoshare/assets/images/rss.png";
			
			$html = sprintf( '<a class="avs-rss-icon" href="%s" target="_blank"><img src="%s" /></a>', $url, $image );
		}	
		
		return $html;
			
	}
	
	public function setHeaders() {
	
		$doc = JFactory::getDocument();
		
		$doc->setTitle( $doc->getTitle() . ' - ' . $this->item->name );
		
		if ( $this->params->get( 'menu-meta_keywords' ) ) {
			$doc->setMetadata( 'keywords', $this->params->get( 'menu-meta_keywords' ) );
		}
		
		if ( ! empty( $this->item->metakeywords ) ) {
			$doc->setMetaData( 'keywords' , $this->item->metakeywords );
		}		
		
		if ( $this->params->get( 'menu-meta_description' ) ) {
			$doc->setDescription( $this->params->get( 'menu-meta_description' ) );
		}
		
		if ( ! empty( $this->item->metadescription ) ) {
			$doc->setDescription( $this->item->metadescription );
		}

		if ( $this->params->get( 'robots' ) ) {
			$doc->setMetadata( 'robots', $this->params->get( 'robots' ) );
		}
		
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
	
	public function generateBreadcrumbs( $item = null ) {
	
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();

		$itemId = $app->input->getInt( 'Itemid' );
		
		jimport( 'joomla.application.pathway' );
		$breadcrumbs = $app->getPathway();
		
		// Hack to force the correct sef url for the component menu
		$brd = $breadcrumbs->getPathway();
		
		if ( ! empty( $brd[0]->link ) ) {
			$brd[0]->link = preg_replace( '/&?option=com_allvideoshare/', '', $brd[0]->link );

			if ( false !== strpos( $brd[0]->link, '&slg=0' ) ) {
				$brd[0]->link = preg_replace( '/&?view=category/', '', $brd[0]->link );
			}
			
			$breadcrumbs->setPathway( $brd );
		}
		// End hack	

		$crumbs = array();
		$index = 0;		
		
		if ( $item->parent != 0 ) {
			$query = 'SELECT * FROM #__allvideoshare_categories WHERE id=' . $item->parent;
			$db->setQuery( $query );
			$itemLevel1 = $db->loadObject();				
					
			if ( $itemLevel1->parent != 0 ) {
				$query = 'SELECT * FROM #__allvideoshare_categories WHERE id=' . $itemLevel1->parent;
				$db->setQuery( $query );
				$itemLevel0 = $db->loadObject();
				
				$crumbs[ $index ][0] = $itemLevel0->name;
				$crumbs[ $index ][1] = JRoute::_( 'index.php?option=com_allvideoshare&Itemid=' . $itemId . '&view=category&slg=' . $itemLevel0->slug );
				$index++;	
			}
			
			$crumbs[ $index ][0] = $itemLevel1->name;
			$crumbs[ $index ][1] = JRoute::_( 'index.php?option=com_allvideoshare&Itemid=' . $itemId . '&view=category&slg=' . $itemLevel1->slug );
			$index++;
		}		
		
        $crumbs[ $index ][0] = $item->name;
		$crumbs[ $index ][1] = JRoute::_( 'index.php?option=com_allvideoshare&Itemid=' . $itemId . '&view=category&slg=' . $item->slug );	

		for ( $i = 0, $n = count( $crumbs ); $i < $n; $i++ ) {
			$breadcrumbs->addItem( $crumbs[ $i ][0], $crumbs[ $i ][1] );
		}
		
    }
	
}