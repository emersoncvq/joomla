<?php
/*
 * @version		$Id: default.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

JHTML::_( 'behavior.formvalidation' );
JHtml::_( 'jquery.framework' );

$doc = JFactory::getDocument();
$doc->addStyleSheet( AllVideoShareUtils::prepareURL( 'administrator/components/com_allvideoshare/assets/css/allvideoshare.css' ), 'text/css', 'screen' );
$doc->addScript( AllVideoShareUtils::prepareURL( 'administrator/components/com_allvideoshare/assets/js/allvideoshare.js' ) );
$doc->addScriptDeclaration("
	Joomla.submitbutton = function( pressbutton ) {
    	submitform( pressbutton ); 
	};
");
?>

<div id="avs-config" class="avs config">
  	<form action="index.php?option=com_allvideoshare&view=config" method="post" name="adminForm" id="adminForm" class="form-horizontal form-validate" enctype="multipart/form-data">
    	<ul class="nav nav-tabs">
        	<li class="active"><a href="#avs-general-settings" data-toggle="tab"><?php echo JText::_( 'GENERAL_SETTINGS' ); ?></a></li>
            <li><a href="#avs-video-page-settings" data-toggle="tab"><?php echo JText::_( 'VIDEO_PAGE_SETTINGS' ); ?></a></li>
            <li><a href="#avs-front-end-user-settings" data-toggle="tab"><?php echo JText::_( 'FRONT_END_USER_SETTINGS' ); ?></a></li>
            <li><a href="#avs-custom-css-settings" data-toggle="tab"><?php echo JText::_( 'CUSTOM_CSS' ); ?></a></li>
      	</ul>
        
        <div class="tab-content">
        
        	<!-- General Settings -->
        	<div class="tab-pane active" id="avs-general-settings">
            	<div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'LOAD_BOOTSTRAP_CSS' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::ListBoolean( 'load_bootstrap_css', $this->item->load_bootstrap_css ); ?>
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'LOAD_ICOMOON_FONT' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::ListBoolean( 'load_icomoon_font', $this->item->load_icomoon_font ); ?>
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'NO_OF_ROWS' ); ?></label>
                    <div class="controls">
                        <input type="text" name="rows" value="<?php echo $this->item->rows; ?>" />
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'NO_OF_COLS' ); ?></label>
                    <div class="controls">
                        <input type="text" name="cols" value="<?php echo $this->item->cols; ?>" />
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'IMAGE_WIDTH' ); ?></label>
                    <div class="controls">
                        <p class="help-block"><?php echo JText::_( 'IMAGE_WIDTH_DESCRIPTION' ); ?></p>
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'IMAGE_RATIO' ); ?></label>
                    <div class="controls">
                        <div class="input-append">
                        	<input type="text" name="image_ratio" value="<?php echo ( $this->item->image_ratio > 0 ) ? $this->item->image_ratio : 56.25; ?>" />
                        	<span class="add-on">%</span>
                        </div>
                        <p class="help-block"><?php echo JText::_( 'IMAGE_RATIO_DESCRIPTION' ); ?></p>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'VIDEO_MENU_ID' ); ?></label>
                    <div class="controls">
                        <input type="text" name="itemid_video" value="<?php echo $this->item->itemid_video; ?>" />
                        <p class="help-block"><?php echo JText::_( 'VIDEO_MENU_ID_DESCRIPTION' ); ?></p>
                    </div>
                </div>
                
                <fieldset> 
                	<legend><?php echo JText::_( 'RSS_FEED_SETTINGS' ); ?></legend>
                    
                    <div class="control-group">
                    	<label class="control-label"><?php echo JText::_( 'ENABLE_RSS_FEED' ); ?></label>
                    	<div class="controls">
                        	<?php echo AllVideoShareHtml::ListBoolean( 'show_feed', $this->item->show_feed ); ?>
                            <p class="help-block"><?php echo JText::_( 'ENABLE_RSS_FEED_DESCRIPTION' ); ?></p>
                    	</div>
                	</div>
                    
                    <div class="control-group">
                    	<label class="control-label"><?php echo JText::_( 'FEED_LIMIT' ); ?></label>
                    	<div class="controls">
                        	<input type="text" name="feed_limit" value="<?php echo $this->item->feed_limit; ?>" />
                    	</div>
                	</div>  
                </fieldset>

                <fieldset> 
                	<legend><?php echo JText::_( 'GDPR_SETTINGS' ); ?></legend>
                    
                    <div class="control-group">
                    	<label class="control-label"><?php echo JText::_( 'SHOW_GDPR_CONSENT' ); ?></label>
                    	<div class="controls">
                        	<?php echo AllVideoShareHtml::ListBoolean( 'show_gdpr_consent', $this->item->show_gdpr_consent ); ?>
                            <p class="help-block"><?php echo JText::_( 'SHOW_GDPR_CONSENT_DESCRIPTION' ); ?></p>
                    	</div>
                	</div> 
                </fieldset>     
            </div>
            
            <!-- Video Page Settings -->
            <div class="tab-pane" id="avs-video-page-settings">
            	<div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'SELECT_THE_PLAYER' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::ListPlayers( 'playerid', $this->item->playerid ); ?>
                    </div>
                </div> 
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'LAYOUT_TYPE' ); ?></label>
                    <div class="controls">
                        <?php
                            echo AllVideoShareHtml::ListItems(
                                'layout',
                                array(
                                    'all'           => JText::_( 'PLAYER_WITH_COMMENTS_AND_RELATED_VIDEOS' ),
                                    'comments'      => JText::_( 'PLAYER_WITH_COMMENTS_ONLY' ),
									'relatedvideos' => JText::_( 'PLAYER_WITH_RELATED_VIDEOS_ONLY' ),
									'none'          => JText::_( 'PLAYER_ONLY' ),
                                ),							
                                $this->item->layout
                            );
                        ?>
                    </div>
                </div> 
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'SHOW_VIDEO_TITLE' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::ListBoolean( 'title', $this->item->title ); ?>
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'SHOW_VIDEO_DESCRIPTION' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::ListBoolean( 'description', $this->item->description ); ?>
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'SHOW_CATEGORY_NAME' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::ListBoolean( 'category', $this->item->category ); ?>
                    </div>
                </div> 
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'SHOW_VIEW_COUNT' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::ListBoolean( 'views', $this->item->views ); ?>
                    </div>
                </div> 
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'SHOW_SEARCH_BOX' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::ListBoolean( 'search', $this->item->search ); ?>
                    </div>
                </div>
                
                <fieldset class="avs-toggle-fields avs-all-fields avs-comments-fields avs-relatedvideos-fields"> 
                	<legend><?php echo JText::_( 'COMMENTS_SETTINGS' ); ?></legend>
                    
                    <div class="control-group">
                        <label class="control-label"><?php echo JText::_( 'COMMENTS_TYPE' ); ?></label>
                        <div class="controls">
                            <?php
								echo AllVideoShareHtml::ListItems(
									'comments_type',
									array(
										'facebook'  => JText::_( 'FACEBOOK_COMMENTS' ),
										'jcomments' => JText::_( 'JCOMMENTS' ),
										'komento'   => JText::_( 'KOMENTO' )
									),							
									$this->item->comments_type
								);
							?>
                        </div>
                    </div>
                
                	<div class="control-group avs-facebook-fields">
                        <label class="control-label"><?php echo JText::_( 'FACEBOOK_APP_ID' ); ?></label>
                        <div class="controls">
                            <input type="text" name="fbappid" value="<?php echo $this->item->fbappid; ?>" />
                            <span class="help-block">
                                <a href="https://developers.facebook.com/docs/apps/register" target="_blank">
                                    <?php echo JText::_( 'CREATE_AN_APP_ID' ); ?>
                                </a>
                            </span>
                        </div>
                    </div>
                    
                    <div id="data_comments_posts" class="control-group avs-facebook-fields">
                        <label class="control-label"><?php echo JText::_( 'NO_OF_POSTS' ); ?></label>
                        <div class="controls">
                            <input type="text" name="comments_posts" value="<?php echo $this->item->comments_posts; ?>" />
                        </div>
                    </div>
                    
                    <div id="data_comments_color" class="control-group avs-facebook-fields">
                        <label class="control-label"><?php echo JText::_( 'COLOR_SCHEME' ); ?></label>
                        <div class="controls">
                            <?php
								echo AllVideoShareHtml::ListItems(
									'comments_color',
									array(
										'light' => JText::_( 'LIGHT' ),
										'dark'  => JText::_( 'DARK' )
									),							
									$this->item->comments_color
								);
							?>
                        </div>
                    </div>                    
                </fieldset>                                      
            </div>
            
            <!-- Front-end User Settings -->
            <div class="tab-pane" id="avs-front-end-user-settings">
            	<div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'AUTO_APPROVE_USER_VIDEOS' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::ListBoolean( 'auto_approval', $this->item->auto_approval ); ?>
                    </div>
                </div>
                
                <h3><?php echo JText::_( 'ALLOW_USERS_TO_ADD' ); ?></h3>
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'YOUTUBE_VIDEOS' ); ?></label>
                    <div class="controls">
                        <?php
                        if ( 1 == $this->item->is_premium ) {
                            echo AllVideoShareHtml::ListBoolean( 'type_youtube', $this->item->type_youtube );
                        } else {
                            echo '<span style="color: red;">PRO Only</span>';
                        }
                        ?>
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'VIMEO_VIDEOS' ); ?></label>
                    <div class="controls">
                        <?php
                        if ( 1 == $this->item->is_premium ) {
                            echo AllVideoShareHtml::ListBoolean( 'type_vimeo', $this->item->type_vimeo );
                        } else {
                            echo '<span style="color: red;">PRO Only</span>';
                        }
                        ?>
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'RTMP_VIDEOS' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::ListBoolean( 'type_rtmp', $this->item->type_rtmp ); ?>
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'HLS_VIDEOS' ); ?></label>
                    <div class="controls">
                        <?php
                        if ( 1 == $this->item->is_premium ) {
                            echo AllVideoShareHtml::ListBoolean( 'type_hls', $this->item->type_hls );
                        } else {
                            echo '<span style="color: red;">PRO Only</span>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            
            <!-- Custom CSS Settings -->
            <div class="tab-pane" id="avs-custom-css-settings">
            	<textarea name="custom_css" style="width:99%; height:300px;"><?php echo $this->item->custom_css; ?></textarea>
            </div>
            
        </div>

        <input type="hidden" name="boxchecked" value="1">
        <input type="hidden" name="option" value="com_allvideoshare" />
        <input type="hidden" name="view" value="config" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="id" value="1">
        <?php echo JHTML::_( 'form.token' ); ?>
  	</form>
</div>