<?php
/*
 * @version		$Id: default.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$app = JFactory::getApplication();

$itemId = $app->input->getInt( 'Itemid' )  ? '&Itemid=' . $app->input->getInt( 'Itemid' ) : '';
$span   = 'span' . floor( 12 / $this->cols );
$column = 0;
?>

<div id="avs-categories" class="avs categories <?php echo $this->escape( $this->params->get( 'pageclass_sfx' ) ); ?>">
	<?php if ( ! empty( $this->menuTitle ) ) : ?>
        <div class="page-header">
            <h1>
                <?php echo $this->escape( $this->menuTitle ); ?>
                <?php echo $this->feedHTML; ?>
            </h1>
        </div>
    <?php endif; ?>

	<div class="row-fluid">
    	<ul class="thumbnails">
        	<?php
			foreach ( $this->items as $key => $item ) {
			
				if ( $column >= $this->cols ) {
					echo '</ul><ul class="thumbnails">';
					$column = 0;
				}
				
				$image = AllVideoShareUtils::getImage( $item->thumb );	
				$target = JRoute::_( 'index.php?option=com_allvideoshare&view=category&slg=' . $item->slug . $itemId );
				?>
                <li class="<?php echo $span; ?> avs-category-<?php echo $item->id; ?>">
                	<div class="thumbnail">
    					<a href="<?php echo $target; ?>" class="avs-thumbnail" style="padding-bottom: <?php echo ( $this->config->image_ratio > 0 ) ? $this->config->image_ratio : 56.25; ?>%;">
                        	<div class="avs-image" style="background-image: url(<?php echo $image; ?>);">&nbsp;</div>
                        </a>
                        <div class="caption">
        					<h4><a href="<?php echo $target; ?>"><?php echo $item->name; ?></a></h4>
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
</div>