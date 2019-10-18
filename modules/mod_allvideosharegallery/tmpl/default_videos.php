<?php 
/*
 * @version		$Id: default_videos.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' ); 

$itemId = AllVideoShareUtils::getVideoMenuId( $config );		
$itemId = ! empty( $itemId ) ? '&Itemid=' . $itemId : '';

$baseUrl  = $params->get( 'link', 'index.php?option=com_allvideoshare&view=video' . $itemId );
$baseUrl  = str_replace( 'slg=', '', $baseUrl );
$baseUrl .= ! strpos( $baseUrl, '?' ) ? '?slg=' : '&slg=';

$more = AllVideoShareGalleryHelper::hasMore( $params );
if ( $params->get( 'category' ) ) {
	$moreURL = AllVideoShareUtils::buildRoute( $params->get( 'category' ), 'category' );
} else {
	$moreURL = AllVideoShareUtils::buildRoute( 0, 'video' );
}

$columns = (int) $params->get( 'columns' );
$span    = 'span' . floor( 12 / $columns );
$column  = 0;
?>

<div class="avs videos <?php echo htmlspecialchars( $params->get( 'moduleclass_sfx' ) ); ?>"> 
    <div class="row-fluid">
    	<ul class="thumbnails">
        	<?php 
  	  		foreach ( $items as $item ) {
			
    			if ( $column >= $columns ) {
					echo '</ul><ul class="thumbnails">';
					$column = 0;
				}
		
				$image  = AllVideoShareUtils::getImage( $item->thumb );	
				$target = JRoute::_( $baseUrl.$item->slug );
    			?>    
                <li class="<?php echo $span; ?> avs-video-<?php echo $item->id; ?>">
                	<div class="thumbnail">
      					<a href="<?php echo $target; ?>" class="avs-thumbnail" style="padding-bottom: <?php echo ( $config->image_ratio > 0 ) ? $config->image_ratio : 56.25; ?>%;">
                        	<div class="avs-image" style="background-image: url(<?php echo $image; ?>);">&nbsp;</div>
                            <img class="avs-play-icon" src="<?php echo JURI::root( true ); ?>/components/com_allvideoshare/assets/images/play.png" alt="<?php echo $item->title; ?>" />
                        </a>
                        <div class="caption">
                        	<h4><a href="<?php echo $target; ?>"><?php echo $item->title; ?></a></h4>
        					<p class="views muted"><?php echo $item->views . ' ' . JText::_( 'VIEWS' ); ?></p>
                        </div>
                    </div>
                </li> 
                <?php
					if ( $column >= $columns ) echo '</ul>';
		  			$column++;
      		}
			?> 
        </ul>
   	</div>
    
    <?php if ( $more ) : ?>
		<a class="btn" href="<?php echo $moreURL; ?>"><?php echo JText::_( 'MORE' ); ?></a>
	<?php endif; ?>
</div>