<?php
/*
 * @version		$Id: default.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

$doc = JFactory::getDocument();
$doc->addStyleSheet( AllVideoShareUtils::prepareURL( 'administrator/components/com_allvideoshare/assets/css/allvideoshare.css' ), 'text/css', 'screen' );
?>

<div id="avs-dashboard" class="avs dashboard">
	<div class="row-fluid">
    
    	<!-- Left Column -->
    	<div class="span7">
        
			<div class="cpanel hidden-phone">
                <div class="icon">
                    <a title="<?php echo JText::_( 'ADD_NEW_CATEGORY' ); ?>" href="index.php?option=com_allvideoshare&view=categories&task=add">
                        <img src="components/com_allvideoshare/assets/images/add-category.png" alt="<?php echo JText::_( 'ADD_NEW_CATEGORY' ); ?>" />
                        </i><span><?php echo JText::_( 'ADD_NEW_CATEGORY' ); ?></span>
                    </a>
                </div>
                
                <div class="icon">
                    <a title="<?php echo JText::_( 'ADD_NEW_VIDEO' ); ?>" href="index.php?option=com_allvideoshare&view=videos&task=add">
                        <img src="components/com_allvideoshare/assets/images/add-video.png" alt="<?php echo JText::_( 'ADD_NEW_VIDEO' ); ?>" />
                        <span><?php echo JText::_( 'ADD_NEW_VIDEO' ); ?></span>
                    </a>
                </div>
                
                <div class="icon">
                    <a title="<?php echo JText::_( 'APPROVAL' ); ?>" href="index.php?option=com_allvideoshare&view=approval">
                        <img src="components/com_allvideoshare/assets/images/approval.png" alt="<?php echo JText::_( 'APPROVAL' ); ?>" />
                        <span><?php echo JText::_( 'APPROVAL' ); ?></span>
                    </a>
                </div>
                
                <div class="icon">
                    <a title="<?php echo JText::_( 'ADD_NEW_ADVERT' ); ?>" href="index.php?option=com_allvideoshare&view=commercials&task=add">
                        <img src="components/com_allvideoshare/assets/images/add-advert.png" alt="<?php echo JText::_( 'ADD_NEW_ADVERT' ); ?>" />
                        <span><?php echo JText::_( 'ADD_NEW_ADVERT' ); ?></span>
                    </a>
                </div>
                
                <div class="icon">
                    <a title="<?php echo JText::_( 'PLAYERS' ); ?>" href="index.php?option=com_allvideoshare&view=players">
                        <img src="components/com_allvideoshare/assets/images/players.png" alt="<?php echo JText::_( 'PLAYERS' ); ?>" />
                        <span><?php echo JText::_( 'PLAYERS' ); ?></span>
                    </a>
                </div>
                
                <div class="icon">
                    <a title="<?php echo JText::_( 'CONFIG' ); ?>" href="index.php?option=com_allvideoshare&view=config">
                        <img src="components/com_allvideoshare/assets/images/config.png" alt="<?php echo JText::_( 'CONFIG' ); ?>" />
                        <span><?php echo JText::_( 'CONFIG' ); ?></span>
                    </a>
                </div>
                
                <div class="icon">
                    <a title="<?php echo JText::_( 'LICENSING' ); ?>" href="index.php?option=com_allvideoshare&view=licensing">
                        <img src="components/com_allvideoshare/assets/images/licensing.png" alt="<?php echo JText::_( 'LICENSING' ); ?>" />
                        <span><?php echo JText::_( 'LICENSING' ); ?></span>
                    </a>
                </div>
            
                <div class="clearfix"></div>
            </div>
            
            <div class="well">
                <h2 class="center"><?php echo JText::_( 'YOU_HAVE_INSTALLED' ); ?></h2>
                <p class="text-center">
                    <?php 
                    printf( 
                        'All Video Share - <strong>%s</strong>%s Version',
                        '3.4.0',
                        ( 0 == $this->config->is_premium ? ' <span class="label label-warning">FREE</span>' : '' ) 
                    ); 
                    ?>
                </p>
            
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <td><?php echo JText::_( 'WEBSITE' ); ?></td>
                            <td>https://allvideoshare.mrvinoth.com/</td>
                        </tr>
                        <tr>
                            <td><?php echo JText::_( 'SUPPORT_MAIL' ); ?></td>
                            <td><a href="mailto:admin@mrvinoth.com">admin@mrvinoth.com</a>
                        <tr>
                            <td><?php echo JText::_( 'FORUM_LINK' ); ?></td>
                            <td><a href="https://allvideoshare.mrvinoth.com/forum/" target="_blank">Click Here</a></td>
                        </tr>
                    </tbody>
                </table>

                <?php if ( 0 == $this->config->is_premium ) : ?>
                    <p class="text-center">
                        <a href="https://allvideoshare.mrvinoth.com/#pricing" class="btn btn-success" target="_blank"><span aria-hidden="true" class="icon-cart"></span>&nbsp;Upgrade to PRO</a>
                    </p>
                <?php endif; ?>
            </div>

        </div>
        
        <!-- Right Column -->
		<div class="span5">        
        	<div class="accordion" id="accordion">
            
            	<!-- Server Information -->
            	<div class="accordion-group">
    				<div class="accordion-heading">
      					<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
        					<?php echo JText::_( 'SERVER_INFORMATION' ); ?>
      					</a>
    				</div>
    				<div id="collapseOne" class="accordion-body collapse in">
      					<div class="accordion-inner">
        					<table class="table table-striped">
                        	<?php
                        	foreach ( $this->serverDetails as $key => $item ) {
                            	$color  = ( $item['value'] == JText::_( 'ALL_VIDEO_SHARE_NO' ) ) ? '#FF0000' : '#339900';
                            	$status = $item['value'];
                            	?>
                            	<tr>
                                	<td class="padlft"><?php echo $item['name']; ?></td>
                                	<td align="center" style="color:<?php echo $color; ?>"><?php echo $status; ?></td>
                            	</tr>
                            	<?php
                        	}
                        	?>
                        	</table>
      					</div>
    				</div>
  				</div>
                
                <!-- Reently Added Videos -->
                <div class="accordion-group">
    				<div class="accordion-heading">
      					<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">
        					<?php echo JText::_( 'RECENTLY_ADDED_VIDEOS' ); ?>
      					</a>
    				</div>
    				<div id="collapseTwo" class="accordion-body collapse">
      					<div class="accordion-inner">
        					<table class="table table-striped">
                            	<thead>
                                    <tr>
                                        <th><?php echo JText::_( 'VIDEO_TITLE' ); ?></th>
                                        <th class="center" width="12%"><?php echo JText::_( 'USER' ); ?></th>
                                        <th class="center" width="12%"><?php echo JText::_( 'PUBLISHED' ); ?></th>
                                    </tr>
                                </thead>
                           		<?php
                            	foreach ( $this->recentVideos as $key => $item ) {
									$editLink = JRoute::_( 'index.php?option=com_allvideoshare&view=videos&task=edit&' . JSession::getFormToken() . '=1&cid[]=' . $item->id );
                               		$publishIcon = ( $item->published == 0 ) ? 'icon-unpublish' : 'icon-publish';
                               		?>
                               		<tr>
                                   		<td><a href="<?php echo $editLink; ?>"><?php echo $item->title; ?></a></td>
                                   		<td class="center"><?php echo $item->user; ?></td>
                                   		<td class="center"><span class="<?php echo $publishIcon; ?>">&nbsp;</span></td>
                               		</tr>
                            	<?php } ?>
                          	</table>
      					</div>
    				</div>
  				</div>
                
                <!-- Most Viewed Videos -->
                <div class="accordion-group">
    				<div class="accordion-heading">
      					<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseThree">
        					<?php echo JText::_( 'MOST_VIEWED_VIDEOS' ); ?>
      					</a>
    				</div>
    				<div id="collapseThree" class="accordion-body collapse">
      					<div class="accordion-inner">
        					<table class="adminlist table table-striped">
                                <thead>
                                    <tr>
                                        <th><?php echo JText::_( 'VIDEO_TITLE' ); ?></th>
                                        <th class="center" width="12%"><?php echo JText::_( 'VIEWS' ); ?></th>
                                        <th class="center" width="12%"><?php echo JText::_( 'PUBLISHED' ); ?></th>
                                    </tr>
                                </thead>
                                <?php
                                foreach ( $this->popularVideos as $key => $item ) {
                                    $editLink = JRoute::_( 'index.php?option=com_allvideoshare&view=videos&task=edit&' . JSession::getFormToken() . '=1&cid[]=' . $item->id );
                               		$publishIcon = ( $item->published == 0 ) ? 'icon-unpublish' : 'icon-publish';
                                    ?>
                                    <tr>
                                        <td><a href="<?php echo $editLink; ?>"><?php echo $item->title; ?></a></td>
                                        <td class="center"><?php echo $item->views; ?></td>
                                        <td class="center"><span class="<?php echo $publishIcon; ?>">&nbsp;</span></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </table>
      					</div>
    				</div>
  				</div>
                
            </div>        
		</div>

	</div>
    
    <!-- Copyright -->
    <div class="form-actions text-center muted">Copyright (c) 2012 - 2019 <a href="https://allvideoshare.mrvinoth.com/" target="_blank">MrVinoth</a>. All rights reserved.</div>
</div>