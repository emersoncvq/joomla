<?php
/*
 * @version		$Id: default_related.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$itemId = AllVideoShareUtils::getVideoMenuId( $this->config );		
$itemId = ! empty( $itemId ) ? '&Itemid=' . $itemId : '';
        
$span   = 'span' . floor( 12 / $this->cols );
$column = 0;
?>

<?php if ( count( $this->videos ) ) : ?>
    <h2><?php echo JText::_( 'RELATED_VIDEOS' ); ?></h2>
    
    <div class="row-fluid">
        <ul class="thumbnails">
        <?php 
            foreach ( $this->videos as $item ) {
            
                if ( $column >= $this->cols ) {
                    echo '</ul><ul class="thumbnails">';
                    $column = 0;
                }
            
                $image  = AllVideoShareUtils::getImage( $item->thumb );	
                $target = JRoute::_( 'index.php?option=com_allvideoshare&view=video&slg=' . $item->slug . $itemId );
                ?>    
                <li class="<?php echo $span; ?> avs-video-<?php echo $item->id; ?>">
                    <div class="thumbnail">
                        <a href="<?php echo $target; ?>" class="avs-thumbnail" style="padding-bottom: <?php echo ( $this->config->image_ratio > 0 ) ? $this->config->image_ratio : 56.25; ?>%;">
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
                    if ( $column >= $this->cols ) echo '</ul>';
                    $column++;
            }
        ?>                  
        </ul>
    </div>
    
    <div class="pagination pagination-centered"><?php echo $this->pagination->getPagesLinks(); ?></div>
<?php endif;