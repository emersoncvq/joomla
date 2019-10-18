<?php
/*
 * @version		$Id: player.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class AllVideoSharePlayer {
	
	public $item = array();	
	
	private $player = array();	
	private $ratio = 56.25;	
	private $license = array();
	private $isMobile = 0;
	private $showGdprConsent = 0;
	
	public function build( $videoId = 1, $playerId = 1, $followURL = 0 ) {

		$app = JFactory::getApplication();	
		
		$html = '';

		// get video data
		$isCategorySlg = 0;
		if ( 'com_allvideoshare' == $app->input->get( 'option' ) && 'category' == $app->input->get( 'view' ) ) {
		 	$isCategorySlg = 1;
		}
		 
		$slug = AllVideoShareUtils::getSlug();
		if ( $followURL == 1 && ! empty( $slug ) && ! $isCategorySlg ) {
		 	$this->item = $this->getVideoBySlug();
		} else {
		 	$this->item = $this->getVideoById( $videoId );
		}
		
		$this->license = $this->getLicenseData();
		 
		// if video found
		if ( ! empty( $this->item ) ) {
			
			if ( preg_match( '/iPhone|iPod|iPad|BlackBerry|Android/', $_SERVER['HTTP_USER_AGENT'] ) ) {
				$this->isMobile = 1;
			}
			
			// get player settings
			$this->player = $this->getPlayerById( $playerId );
		
			// config
			$config = AllVideoShareUtils::getConfig();

			// decide player engine
			$engine = $this->player->type;			

			if ( 'thirdparty' == $this->item->type ) {
				$engine = 'thirdparty';
			} else {
				if ( 1 == $config->is_premium ) {
					if ( 'rtmp' == $this->item->type ) {
						$engine = 'flash';
					}
		
					if ( 1 == $this->isMobile || in_array( $this->item->type, array( 'youtube', 'vimeo', 'hls' ) ) ) {
						$engine = 'mediaelement';
					}
				} else {
					$engine = 'flash';
					
					if ( 1 == $this->isMobile ) {
						$engine = 'html';
					}
	
					if ( 'youtube' == $this->item->type ) {
						$engine = 'thirdparty';
					}
				}
			}			

			// GDPR			
			if ( ! empty( $config->show_gdpr_consent ) ) {
				// Get the cookie
				$value = $app->input->cookie->get( 'avs_gdpr_consent', null );

				// If there's no cookie value, require GDPR consent
				if ( $value == null ) {
					$this->showGdprConsent = 1;
				}            
			}

			// build
			switch ( $engine ) {								
				case 'thirdparty':
					if ( 'youtube' == $this->item->type ) {
						$queryString = parse_url( $this->item->video, PHP_URL_QUERY );
						parse_str( $queryString, $args );
						
						$src = 'https://www.youtube.com/embed/' . $args['v'] . '?rel=0&showinfo=0&iv_load_policy=3&modestbranding=1';
						if ( ! $this->isMobile ) {
							if ( $this->player->autostart ) $src .= '&autoplay=1';
							if ( $this->player->loop ) $src .= '&loop=1';
						}

						$this->item->thirdparty = sprintf( '<iframe src="%s" frameborder="0" allowfullscreen></iframe>', $src );
					}

					$html = '<div class="avs-player" style="padding-bottom: ' . $this->ratio . '%;">';
					if ( $this->showGdprConsent ) {
						JHtml::_( 'jquery.framework' );
						
						$doc = JFactory::getDocument();
						$doc->addScript( AllVideoShareUtils::prepareURL(  'components/com_allvideoshare/assets/js/allvideoshare.js' ) );

						$html .= str_replace( ' src=', ' data-src=', $this->item->thirdparty );
						$html .= sprintf( '<div class="avs-gdpr-wrapper" style="background-image: url(%s);">', $this->item->thumb );
						$html .= '<div class="avs-gdpr-consent-block">';
						$html .= sprintf( '<div class="avs-gdpr-consent-message">%s</div>', JText::_( 'GDPR_CONSENT_MESSAGE' ) );
						$html .= sprintf( '<div class="avs-gdpr-consent-button" data-baseurl="%s">%s</div>', JURI::root(), JText::_( 'GDPR_CONSENT_BUTTON_LABEL' ) );
						$html .= '</div>';
						$html .= '</div>';
					} else {
						$html .= $this->item->thirdparty;
					}
					$html .= '</div>';
					break;
				case 'mediaelement':
					$url = JURI::root() . 'index.php?option=com_allvideoshare&view=player&vid=' . $this->item->id . '&pid=' . $this->player->id . '&format=raw';
					$html = sprintf( '<div class="avs-player" style="padding-bottom: %s;"><iframe width="560" height="315" src="%s" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>', $this->ratio . '%', $url );
					break;
				case 'html':
					$html = $this->getHtml5Player();	
					break;				
				default:
					$html = $this->getFlashPlayer();
			}
			 
			// update views
			if ( ! $this->showGdprConsent || 'flash' == $engine ) {
				$this->updateViews( $this->item->slug );
			}
			 
		}
		 
		// return...
		return $html;
		 
	}
	
	public function getHtml5Player() {
	
		$html = sprintf( '<div class="avs-player avs-video-%d" style="padding-bottom: %s;">', $this->item->id, $this->ratio . '%' );
		
		if ( 'rtmp' == $this->item->type ) {
	     	$html .= sprintf( '<video onclick="this.play();" poster="%s" controls><source src="%s" type="application/x-mpegurl" /></video>', $this->item->thumb, $this->item->hls );
		} else {
	    	$html .= sprintf( '<video onclick="this.play();" poster="%s" controls><source src="%s" /></video>', $this->item->thumb, $this->item->video );
        }
		
		$html .= '</div>';
		 
		return $html;
		 
	}

	public function getFlashPlayer() {

		$app = JFactory::getApplication();
		$config = JFactory::getConfig();
		
		$flashvars = sprintf( 'base=%s&vid=%d&pid=%d&sef=%d', JURI::root(), $this->item->id, $this->player->id, ( 1 == $config->get( 'sef' ) ? 1 : 0 ) );
		if ( $lang = $app->input->get( 'lang' ) ) {
			$flashvars .= "&amp;lang=$lang";
		}
		
		$swf = AllVideoShareUtils::prepareURL( 'components/com_allvideoshare/player.swf' );				
		
		$html = sprintf( '<div class="avs-player" style="padding-bottom: %s;">', $this->ratio . '%' );
		$html .= '<object name="player" width="100%" height="100%">';
    	$html .= sprintf( '<param name="movie" value="%s" />', $swf );
    	$html .= '<param name="wmode" value="opaque" />';
    	$html .= '<param name="allowfullscreen" value="true" />';
    	$html .= '<param name="allowscriptaccess" value="always" />';
    	$html .= sprintf( '<param name="flashvars" value="%s" />', $flashvars );
    	$html .= sprintf( '<object type="application/x-shockwave-flash" data="%s" width="100%%" height="100%%">', $swf );
      	$html .= sprintf( '<param name="movie" value="%s" />', $swf );
      	$html .= '<param name="wmode" value="opaque" />';
      	$html .= '<param name="allowfullscreen" value="true" />';
      	$html .= '<param name="allowscriptaccess" value="always" />';
      	$html .= sprintf( '<param name="flashvars" value="%s" />', $flashvars );
    	$html .= '</object>';
  	 	$html .= '</object>';
		$html .= '</div>';
		 
		return $html;
		
	}	
	
	public function buildEmbed( $videoId = 1, $playerId = 1 ) {
	
		$html  = '<!DOCTYPE html>';
		$html .= '<html>';
		$html .= '<head>';
		$html .= sprintf( '<link rel="stylesheet" href="%s" />', AllVideoShareUtils::prepareURL( 'components/com_allvideoshare/assets/css/allvideoshare.css', false ) );
		$html .= '<style type="text/css">body, iframe { margin: 0 !important; padding: 0 !important; background: transparent !important; }</style>';
		$html .= '</head>';
		$html .= '<body>';
		$html .= $this->build( $videoId, $playerId );
		$html .= '</body>';
		$html .= '</html>';
		
		return $html;
		
	}
	
	public function getVideoBySlug() {	
		 
        $db = JFactory::getDBO();
		 
		$slug = AllVideoShareUtils::getSlug();
        $query = "SELECT * FROM #__allvideoshare_videos WHERE slug=" . $db->Quote( $slug );
        $db->setQuery( $query );
        $item = $db->loadObject();
		 
		if ( ! empty( $item ) ) {
			if ( ! empty( $item->preview ) ) {
				$item->thumb = $item->preview;
			}
		}
		 
        return $item;
		 
	}
	
	public function getVideoById( $id ) {
	
        $db = JFactory::getDBO();
		 
        $query = "SELECT * FROM #__allvideoshare_videos WHERE published=1 AND id=" . (int) $id;
        $db->setQuery( $query );
        $item = $db->loadObject();
		 
		if ( ! empty( $item ) ) {
			if ( ! empty( $item->preview ) ) {
				$item->thumb = $item->preview;
			}
		}
		 
        return $item;
		 
	}

	public function getPlayerById( $id ) {
	
        $db = JFactory::getDBO();
		 
        $query = "SELECT * FROM #__allvideoshare_players WHERE published=1 AND id=" . (int) $id;
        $db->setQuery( $query );
        $item = $db->loadObject();
		 
		// fallback to the default player profile
		if ( empty( $item ) ) {
		 	$query = "SELECT * FROM #__allvideoshare_players WHERE id=1";
        	$db->setQuery( $query );
        	$item = $db->loadObject();
		}
		 
		// set player scaling ratio
		if ( $item->ratio > 0 ) {
		 	$this->ratio = $item->ratio;
		}
		 
        return $item;
		 
	}
	
	public function getLicenseData() {
	
        $db = JFactory::getDBO();	
		 	 
        $query = "SELECT * FROM #__allvideoshare_licensing WHERE id=1";
        $db->setQuery( $query );
        $item = $db->loadObject();

        return $item;
		 
	}	
	
	public function updateViews() {

		$app = JFactory::getApplication();	
		$db = JFactory::getDBO();
		$session = JFactory::getSession();
		
		$avs = array();
		$arr = array();
		
		if ( $session->get( 'avs' ) ) {
			$arr = $session->get( 'avs' );
		}
		
		if ( ! in_array( $this->item->slug, $arr ) ) {
	    	$avs = $arr;
		    $avs[] = $this->item->slug;				
	 
		 	$query = "UPDATE #__allvideoshare_videos SET views=views+1 WHERE slug=" . $db->Quote( $this->item->slug );
    	 	$db->setQuery( $query );
		 	$db->query();
		 
		 	$session->set( 'avs', $avs );
		}
		
	}
		
}