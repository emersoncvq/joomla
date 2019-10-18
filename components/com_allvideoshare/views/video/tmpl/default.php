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
$itemId = $app->input->getInt( 'Itemid' ) ? '&Itemid=' . $app->input->getInt( 'Itemid' ) : '';
?>

<div id="avs-video" class="avs video <?php echo $this->escape( $this->params->get('pageclass_sfx') ); ?>">
	<?php if ( $this->config->title ) : ?>
		<div class="page-header">
			<h1><?php echo $this->escape( $this->item->title ); ?></h1>
    	</div>
    <?php endif; ?>
    
    <div class="row-fluid">
		<?php
            $meta = array();
            
            if ( $this->config->category ) {
                $meta[] = sprintf( '<span><i class="icon-folder"></i>&nbsp;%s</span>', $this->item->category );
            }
            
            if ( $this->config->views ) {
                $meta[] = sprintf( '<span>%d&nbsp;%s</span>', $this->item->views, JText::_( 'VIEWS' ) );
            }
            
			if ( count( $meta ) ) {
            	printf( '<div class="pull-left muted">%s</div>', implode( ' / ', $meta ) );
			}
        ?>
        
        <?php if ( $this->config->search ) : ?>
        	<div class="pull-right">
            	<form action="<?php echo JRoute::_( 'index.php?option=com_allvideoshare&view=search' . $itemId ); ?>" class="form-validate" method="post">
                    <input type="hidden" name="option" value="com_allvideoshare" />
                    <input type="hidden" name="view" value="search" />
                    <input type="hidden" name="Itemid" value="<?php echo $app->input->getInt( 'Itemid' ); ?>" />
                    <input type="text" name="q" class="required" />
                   	<button type="submit" class="btn btn-default"><?php echo JText::_( 'GO' ); ?></button>
                </form>
            </div>
        <?php endif; ?>
        
        <div class="clearfix"></div>
    </div>
    
    <?php 
		// player
		echo $this->player;	
		
		echo '<p></p>';
		
		// description	
		if ( $this->config->description ) echo $this->item->description;
		
		// comments, relates videos
		if ( 'all' == $this->config->layout ) {
			echo $this->loadTemplate( 'comments' );
			echo $this->loadTemplate( 'related' );
		} elseif ( 'comments' == $this->config->layout ) {
			echo $this->loadTemplate( 'comments' );
		} elseif ( 'relatedvideos' == $this->config->layout ) {
			echo $this->loadTemplate( 'related' );
		}
	?>
</div>