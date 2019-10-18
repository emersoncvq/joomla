<?php
/*
 * @version		$Id: default_comments.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$jcomments = JPATH_SITE . '/components/com_jcomments/jcomments.php';
$komento   = JPATH_SITE . '/components/com_komento/bootstrap.php';

// load JComments ( only if applicable )
if ( file_exists( $jcomments ) && 'jcomments' == $this->config->comments_type ) {
	require_once( $jcomments );
    echo JComments::showComments( $this->item->id, 'com_allvideoshare', $this->item->title );	
}

// load Komento ( only if applicable )
if ( file_exists( $komento ) && 'komento' == $this->config->comments_type ) {
	$item = new stdClass;
	$item->id = $this->item->id;
	$item->catid = $this->item->catid;
	$item->text = $this->item->description;
	$item->introtext = $this->item->description;
		
	require_once( $komento );
	echo Komento::commentify( 'com_allvideoshare', $item, array() );
}

// load FaceBook ( only if applicable )
if ( 'facebook' == $this->config->comments_type ) : ?>
	<?php
		$db = JFactory::getDBO();		
		$fields_config = $db->getTableColumns( '#__allvideoshare_config' );

		// if upgraded to 3.0 from old All Video Share versions
		if( array_key_exists( 'responsive', $fields_config ) ) {
			$url = JURI::getInstance()->toString();
		} 
		
		// else
		else {
			$url = JURI::root() . 'index.php?option=com_allvideoshare&view=video&slg=' . $this->item->slug;
		}
	?>
	<h2><?php echo JText::_( 'ADD_YOUR_COMMENTS' ); ?></h2>
	<div id="fb-root"></div>
	<div class="fb-comments"
    	data-href="<?php echo $url; ?>"
        data-num-posts="<?php echo $this->config->comments_posts; ?>"
        data-width="100%"
        data-colorscheme="<?php echo $this->config->comments_color; ?>">
    </div>
<?php 
endif;