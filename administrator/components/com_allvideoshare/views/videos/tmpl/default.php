<?php
/*
 * @version		$Id: default.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<div id="avs-videos" class="avs videos">
  	<form action="index.php?option=com_allvideoshare&view=videos" method="post" name="adminForm" id="adminForm">
    	<div id="filter-bar" class="btn-toolbar">
        	<div class="filter-search btn-group pull-left">
	    		<input type="text" name="search" id="search" placeholder="<?php echo JText::_( 'JSEARCH_FILTER' ); ?>" value="<?php echo $this->lists['search'] ?>" class="hasTooltip" title="<?php echo JHtml::tooltipText( 'FILTER_BY_TITLE' ); ?>" />
	  		</div>
      		<div class="btn-group pull-left">
	    		<button type="submit" class="btn hasTooltip" title="<?php echo JHtml::tooltipText( 'JSEARCH_FILTER_SUBMIT' ); ?>"><i class="icon-search"></i></button>
				<button type="button" class="btn hasTooltip" title="<?php echo JHtml::tooltipText( 'JSEARCH_FILTER_CLEAR' ); ?>" onclick="document.getElementById('search').value=''; document.getElementById('filter_state').value=-1; document.getElementById('filter_category').value=''; this.form.submit();"><i class="icon-remove"></i></button>
	  		</div>
            <div class="btn-group pull-right hidden-phone">
				<?php echo $this->pagination->getLimitBox(); ?>
		  	</div>
      		<div class="btn-group pull-right hidden-phone">
				<?php echo $this->lists['state']; ?>
	  		</div>
            <div class="btn-group pull-right hidden-phone">
				<?php echo $this->lists['categories']; ?>
	  		</div>
        	<div class="clearfix"></div>
	 	</div>
        
    	<table class="table table-striped">
          	<thead>
                <tr>
                  	<th class="center hidden-phone" width="5%">#</th>
                  	<th class="center hidden-phone" width="5%"><input type="checkbox" name="toggle" value="" onClick="Joomla.checkAll( this );" /></th>
                  	<th><?php echo JText::_( 'VIDEO_TITLE' ); ?></th>
                  	<th class="hidden-phone" width="12%"><?php echo JText::_( 'CATEGORY' ); ?></th>
                  	<th class="center hidden-phone" width="14%"><?php echo JText::_( 'POSITION' ); ?>&nbsp;&nbsp; <?php echo JHTML::_( 'grid.order',  $this->items ); ?></th>
                  	<th class="hidden-phone" width="8%"><?php echo JText::_( 'USER' ); ?></th>
                  	<th class="center hidden-phone" width="8%"><?php echo JText::_( 'VIEWS' ); ?></th>
                  	<th class="center hidden-phone" width="8%"><?php echo JText::_( 'FEATURED' ); ?></th>
                  	<th class="center" width="8%"><?php echo JText::_( 'PUBLISHED' ); ?></th>
                  	<th class="center hidden-phone" width="8%"><?php echo JText::_( 'ID' ); ?></th>
                </tr>
          	</thead>
      		
            <tbody>
			<?php
			$n = count( $this->items );
            foreach ( $this->items as $key => $item ) {
                $editLink  = JRoute::_( 'index.php?option=com_allvideoshare&view=videos&task=edit&' . JSession::getFormToken() . '=1&cid[]=' . $item->id );
                $checked   = JHTML::_( 'grid.id', $key, $item->id );
                $color     = ( $item->featured == 0 ) ? '#FF0000' : '#339900';
                $featured  = ( $item->featured == 0 ) ? JText::_( 'No' ) : JText::_( 'Yes' );
                $published = JHTML::_( 'grid.published', $item, $key );			
            	?>
                <tr>
                  	<td class="center hidden-phone"><?php echo ( $this->limitstart + $key + 1 ); ?></td>
                  	<td class="center hidden-phone"><?php echo $checked; ?></td>
                  	<td><a href="<?php echo $editLink; ?>"><?php echo $item->title; ?></a></td>
                  	<td class="hidden-phone"><?php echo $item->category; ?></td>
                  	<td class="center hidden-phone order">
                  		<input type="text" name="order[]" class="input-mini center pull-left" value="<?php echo $item->ordering; ?>" />
						<div class="btn-group pull-right">
							<?php echo $this->pagination->orderUpIcon( $key, ( $item->catid == @$this->items[ $key - 1 ]->catid ), 'orderup', 'MOVE_UP' ) . $this->pagination->orderDownIcon( $key, $n, ( $item->catid == @$this->items[ $key + 1 ]->catid ), 'orderdown', 'MOVE_DOWN' ); ?>
						</div>
                  	</td>
                  	<td class="hidden-phone"><?php echo $item->user; ?></td>
                  	<td class="center hidden-phone"><?php echo $item->views; ?></td>
                  	<td class="center hidden-phone"><?php echo $featured; ?></td>
                  	<td class="center"><?php echo $published; ?></td>
                  	<td class="center hidden-phone"><?php echo $item->id; ?></td>
                </tr>
        		<?php
        	}
			?>
      		</tbody>
      	
        	<tfoot>
        		<tr>
          			<td colspan="10"><?php echo $this->pagination->getListFooter(); ?></td>
        		</tr>
      		</tfoot>
    	</table>
    
    	<input type="hidden" name="boxchecked" value="0" />
    	<input type="hidden" name="option" value="com_allvideoshare" />
    	<input type="hidden" name="view" value="videos" />
    	<input type="hidden" name="task" value="" />
    	<?php echo JHTML::_( 'form.token' ); ?>
  	</form>
</div>