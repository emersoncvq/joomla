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

<div id="avs-user" class="avs user <?php echo $this->escape( $this->params->get( 'pageclass_sfx' ) ); ?>">
	<div class="page-header">
  		<h1> <?php echo JText::_( 'MY_VIDEOS' ); ?> </h1>
    </div>
    
  	<form action="<?php echo JRoute::_( 'index.php?option=com_allvideoshare&view=user' ); ?>" name="avsForm" id="avsForm" method="post">
    	<div class="btn-toolbar">
        	<div class="btn-group pull-left">
	    		<input type="text" name="s" id="filter_search" value="<?php echo htmlspecialchars( $this->s ); ?>" title="<?php echo JText::_( 'SEARCH_MY_VIDEOS' ); ?>" />
	  		</div>
      		<div class="btn-group pull-left">
	    		<button type="submit" class="btn" title="<?php echo JText::_( 'GO' ); ?>"><i class="icon-search"></i></button>
				<button type="button" class="btn" title="<?php echo JText::_( 'RESET' ); ?>" onclick="document.getElementById('filter_search').value=''; this.form.submit();"><i class="icon-remove"></i></button>
	  		</div>
            <div class="pull-right">
            	<a class="btn btn-primary" href="<?php echo JRoute::_( 'index.php?option=com_allvideoshare&view=user&task=add&'.JSession::getFormToken().'=1'.$itemId ); ?>">
					<?php echo JText::_( 'ADD_NEW_VIDEO' ); ?>
                </a>
            </div>
            <div class="clearfix"></div>
        </div>
        
    	<table class="table table-striped">
      		<thead>
        		<tr>
          			<th class="center" width="7%">#</th>
          			<th width="33%"><?php echo JText::_( 'TITLE' ); ?></th>
          			<th class="center" width="7%"><?php echo JText::_( 'ID' ); ?></th>
          			<th width="18%"><?php echo JText::_( 'CATEGORY' ); ?></th>          
          			<th class="center" width="15%"><?php echo JText::_( 'PUBLISHED' ); ?></th>
          			<th class="center" width="25%"><?php echo JText::_( 'ACTIONS' ); ?></th>
        		</tr>
      		</thead>
      		<tbody>
				<?php
                foreach ( $this->items as $key => $item ) {
                    $editLink   = JRoute::_( 'index.php?option=com_allvideoshare&view=user&task=edit&' . JSession::getFormToken() . '=1&id=' . $item->id . $itemId );
                    $deleteLink = JRoute::_( 'index.php?option=com_allvideoshare&view=user&task=delete&' . JSession::getFormToken() . '=1&id=' . $item->id . $itemId );
                    ?>
                    <tr>
                        <td class="center"><?php echo ( $this->limitstart + $key + 1 ); ?></td>
                        <td><a href="<?php echo $editLink; ?>"> <?php echo $item->title; ?></a></td>
                        <td class="center"><?php echo $item->id; ?></td>
                        <td><?php echo $item->category; ?></td>          
                        <td class="center"><i class="icon-<?php echo $item->published == 0 ? 'remove' : 'checkmark'; ?>">&nbsp;</i></td>
                        <td class="center">
                            <div class="btn-group">
                                <a class="btn btn-small btn-default" href="<?php echo $editLink; ?>"><?php echo JText::_( 'EDIT' ); ?></a>
                                <a class="btn btn-small btn-danger" href="<?php echo $deleteLink; ?>"><?php echo JText::_( 'DELETE' ); ?></a>
                            </div>
                        </td>
                    </tr>
                    <?php
                }
                ?>
      		</tbody>
		</table>
    
    	<div class="pagination"><?php echo $this->pagination->getPagesLinks(); ?></div>
    
    	<input type="hidden" name="Itemid" value="<?php echo $app->input->getInt('Itemid'); ?>" />
    	<?php echo JHTML::_( 'form.token' ); ?>
  	</form>
</div>