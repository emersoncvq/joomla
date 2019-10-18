<?php 
/*
 * @version		$Id: default_categories.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' ); 

$app = JFactory::getApplication();

$columns = (int) $params->get( 'columns' );
$span    = 'span' . floor( 12 / $columns );
$column  = 0;

$itemId  = $app->input->getInt('Itemid') ? '&Itemid=' . $app->input->getInt('Itemid') : '';

$baseUrl  = $params->get( 'link', 'index.php?option=com_allvideoshare&view=category' . $itemId );
$baseUrl  = str_replace( 'slg=', '', $baseUrl );
$baseUrl .= ! strpos( $baseUrl, '?' ) ? '?slg=' : '&slg=';

$more = AllVideoShareGalleryHelper::hasMore( $params );
$moreURL = AllVideoShareUtils::buildRoute( 0, 'category' );
?>

<div class="avs categories <?php echo htmlspecialchars( $params->get( 'moduleclass_sfx' ) ); ?>">
	<div class="row-fluid">
    	<ul class="thumbnails">
        	<?php
			foreach ( $items as $key => $item ) {
			
				if ( $column >= $columns ) {
					echo '</ul><ul class="thumbnails">';
					$column = 0;
				}
				
				$image  = AllVideoShareUtils::getImage( $item->thumb );	
				$target = JRoute::_( $baseUrl . $item->slug );
				?>
                <li class="<?php echo $span; ?> avs-category-<?php echo $item->id; ?>">
                	<div class="thumbnail">
    					<a href="<?php echo $target; ?>" class="avs-thumbnail" style="padding-bottom: <?php echo ( $config->image_ratio > 0 ) ? $config->image_ratio : 56.25; ?>%;">
                        	<div class="avs-image" style="background-image: url(<?php echo $image; ?>);">&nbsp;</div>
                        </a>
                        <div class="caption">
        					<h4><a href="<?php echo $target; ?>"><?php echo $item->name; ?></a></h4>
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