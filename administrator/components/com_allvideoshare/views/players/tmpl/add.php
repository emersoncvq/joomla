<?php
/*
 * @version		$Id: add.php 3.3.0 2019-01-25 $
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
		
		if ( pressbutton == 'cancel' ) {		
        	submitform( pressbutton );			
    	} else {
		
			var f = document.adminForm;			

        	if ( document.formvalidator.isValid( f ) ) {
            	submitform( pressbutton );    
        	};
			
    	}; 
		
	};
");
?>

<div id="avs-players" class="avs players add">
  	<form action="index.php" method="post" name="adminForm" id="adminForm" class="form-horizontal form-validate">
        <div class="row-fluid">
        
            <!-- GENERAL_SETTINGS -->
            <fieldset>
                <legend><?php echo JText::_( 'GENERAL_SETTINGS' ); ?></legend>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'TYPE' ); ?></label>
                    <div class="controls">
                        <?php
                        if ( 1 == $this->config->is_premium ) {
                            echo AllVideoShareHtml::RadioGroup(
                                'type',
                                array(
                                    'allvideoshare' => JText::_( 'FLASH' ),
									'mediaelement'  => JText::_( 'HTML5' )
                                ),							
                                ( $this->item->type ) ? $this->item->type : 'allvideoshare'
                            );
                        } else {
                            echo AllVideoShareHtml::RadioGroupPlayer();
                        }
                        ?>
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'NAME' ); ?><span class="star">&nbsp;*</span></label>
                    <div class="controls">
                        <input type="text" name="name" class="required" />
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'PLAYER_WIDTH' ); ?></label>
                    <div class="controls">
                        <p class="help-block"><?php echo JText::_( 'PLAYER_WIDTH_DESCRIPTION' ); ?></p>
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'PLAYER_RATIO' ); ?></label>
                    <div class="controls">
                    	<div class="input-append">
                        	<input type="text" name="ratio" value="<?php echo ( $this->item->ratio > 0 ) ? $this->item->ratio : 56.25; ?>" />
                        	<span class="add-on">%</span>
                        </div>
                        <p class="help-block"><?php echo JText::_( 'PLAYER_RATIO_DESCRIPTION' ); ?></p>
                        <br />
                        <p class="help-block"><strong>56.25</strong> - <?php echo JText::_( 'WIDE_SCREEN_TV' ); ?></p>
                        <p class="help-block"><strong>62.50</strong> - <?php echo JText::_( 'MONITOR_SCREENS' ); ?></p>
                        <p class="help-block"><strong>75</strong> - <?php echo JText::_( 'CLASSIC_TV' ); ?></p>
                        <p class="help-block"><strong>67</strong> - <?php echo JText::_( 'PHOTO_CAMERA' ); ?></p>
                        <p class="help-block"><strong>100</strong> - <?php echo JText::_( 'SQUARE' ); ?></p>
                        <p class="help-block"><strong>41.70</strong> - <?php echo JText::_( 'CINEMASCOPE' ); ?></p>
                    </div>
                </div>
  
                <div class="control-group avs-toggle-fields avs-allvideoshare-fields">
                    <label class="control-label"><?php echo JText::_( 'BUFFER_TIME' ); ?></label>
                    <div class="controls">
                        <input type="text" name="buffer" value="<?php echo $this->item->buffer; ?>" />
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'VOLUME_LEVEL' ); ?></label>
                    <div class="controls">
                        <input type="text" name="volumelevel" value="<?php echo $this->item->volumelevel; ?>" />
                    </div>
                </div>
                
                <div class="control-group avs-toggle-fields avs-allvideoshare-fields">
                    <label class="control-label"><?php echo JText::_( 'STRETCH' ); ?></label>
                    <div class="controls">
                        <?php
                            echo AllVideoShareHtml::ListItems(
                                'stretch',
                                array(
                                    'uniform'  => JText::_( 'UNIFORM' ),
                                    'fill'     => JText::_( 'FILL' ),
                                    'original' => JText::_( 'ORIGINAL' ),
                                    'exactfit' => JText::_( 'EXACT_FIT' ),
                                ),							
                                $this->item->stretch
                            );
                        ?>
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'LOOP' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::ListBoolean( 'loop', $this->item->loop ); ?>
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'AUTOSTART' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::ListBoolean( 'autostart', $this->item->autostart ); ?>
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'PUBLISH' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::ListBoolean( 'published', $this->item->published ); ?>
                    </div>
                </div>
            </fieldset>

            <!-- ENABLE_OR_DISABLE_SKIN_ELEMENTS -->
            <fieldset>
                <legend><?php echo JText::_( 'ENABLE_OR_DISABLE_SKIN_ELEMENTS' ); ?></legend>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'CONTROLBAR' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::ListBoolean( 'controlbar', $this->item->controlbar ); ?>
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'DURATION_DOCK' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::ListBoolean( 'durationdock', $this->item->durationdock ); ?>
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'TIMER_DOCK' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::ListBoolean( 'timerdock', $this->item->timerdock ); ?>
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'FULLSCREEN_DOCK' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::ListBoolean( 'fullscreendock', $this->item->fullscreendock ); ?>
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'HD_DOCK' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::ListBoolean( 'hddock', $this->item->hddock ); ?>
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'EMBED_DOCK' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::ListBoolean( 'embeddock', $this->item->embeddock ); ?>
                    </div>
                </div>

                <div class="control-group avs-toggle-fields avs-mediaelement-fields">
                    <label class="control-label"><?php echo JText::_( 'SHARE_DOCK' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::ListBoolean( 'sharedock', $this->item->sharedock ); ?>
                    </div>
                </div>
                
                <div class="control-group avs-toggle-fields avs-allvideoshare-fields">
                    <label class="control-label"><?php echo JText::_( 'FACEBOOK_DOCK' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::ListBoolean( 'facebookdock', $this->item->facebookdock ); ?>
                    </div>
                </div>
                
                <div class="control-group avs-toggle-fields avs-allvideoshare-fields">
                    <label class="control-label"><?php echo JText::_( 'TWITTER_DOCK' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::ListBoolean( 'twitterdock', $this->item->twitterdock ); ?>
                    </div>
                </div> 
            </fieldset>
            
            <!-- COLOR_YOUR_SKIN -->
            <fieldset class="avs-toggle-fields avs-allvideoshare-fields">
                <legend><?php echo JText::_( 'COLOR_YOUR_SKIN' ); ?></legend>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'CONTROLBAR_OUTLINE_COLOR' ); ?></label>
                    <div class="controls">
                        <input type="text" name="controlbaroutlinecolor" value="<?php echo $this->item->controlbaroutlinecolor; ?>" />
                    </div>
                </div> 
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'CONTROLBAR_BG_COLOR' ); ?></label>
                    <div class="controls">
                        <input type="text" name="controlbarbgcolor" value="<?php echo $this->item->controlbarbgcolor; ?>" />
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'CONTROLBAR_OVERLAY_COLOR' ); ?></label>
                    <div class="controls">
                        <input type="text" name="controlbaroverlaycolor" value="<?php echo $this->item->controlbaroverlaycolor; ?>" />
                    </div>
                </div> 
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'CONTROLBAR_OVERLAY_ALPHA' ); ?></label>
                    <div class="controls">
                        <input type="text" name="controlbaroverlayalpha" value="<?php echo $this->item->controlbaroverlayalpha; ?>" />
                    </div>
                </div> 
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'ICON_COLOR' ); ?></label>
                    <div class="controls">
                        <input type="text" name="iconcolor" value="<?php echo $this->item->iconcolor; ?>" />
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'PROGRESSBAR_BG_COLOR' ); ?></label>
                    <div class="controls">
                        <input type="text" name="progressbarbgcolor" value="<?php echo $this->item->progressbarbgcolor; ?>" />
                    </div>
                </div> 
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'PROGRESSBAR_BUFFER_COLOR' ); ?></label>
                    <div class="controls">
                        <input type="text" name="progressbarbuffercolor" value="<?php echo $this->item->progressbarbuffercolor; ?>" />
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'PROGRESSBAR_SEEK_COLOR' ); ?></label>
                    <div class="controls">
                        <input type="text" name="progressbarseekcolor" value="<?php echo $this->item->progressbarseekcolor; ?>" />
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'VOLUMEBAR_BG_COLOR' ); ?></label>
                    <div class="controls">
                        <input type="text" name="volumebarbgcolor" value="<?php echo $this->item->volumebarbgcolor; ?>" />
                    </div>
                </div> 
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'VOLUMEBAR_SEEK_COLOR' ); ?></label>
                    <div class="controls">
                        <input type="text" name="volumebarseekcolor" value="<?php echo $this->item->volumebarseekcolor; ?>" />
                    </div>
                </div> 
            </fieldset>
            
             <!-- RELATED_VIDEOS_INSIDE_THE_PLAYER -->
            <fieldset class="avs-toggle-fields avs-allvideoshare-fields">
                <legend><?php echo JText::_( 'RELATED_VIDEOS_INSIDE_THE_PLAYER' ); ?></legend>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'RELATED_VIDEOS' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::ListBoolean( 'playlist', $this->item->playlist ); ?>
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'RELATED_VIDEOS_BG_COLOR' ); ?></label>
                    <div class="controls">
                        <input type="text" name="playlistbgcolor" size="60" value="<?php echo $this->item->playlistbgcolor; ?>" />
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'CUSTOM_PLAYER_PAGE' ); ?></label>
                    <div class="controls">
                        <input type="text" name="customplayerpage" size="60" value="<?php echo $this->item->customplayerpage; ?>" />
                        <span class="help-block">
                            <a href="http://allvideoshare.mrvinoth.com/custom-player-page-url" target="_blank"><?php echo JText::_( 'WHAT_IS_THIS' ); ?></a>
                        </span> 
                    </div>
                </div>
            </fieldset>
            
            <!-- ADVERTISEMENTS -->
            <fieldset>
                <legend><?php echo JText::_( 'ADVERTISEMENTS' ); ?></legend>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'AD_ENGINE' ); ?></label>
                    <div class="controls">
                        <?php
                        if ( 1 == $this->config->is_premium ) {
                            echo AllVideoShareHtml::RadioGroup(
                                'ad_engine',
                                array(
                                    'custom' => JText::_( 'CUSTOM' ),
                                    'vast'   => JText::_( 'VAST_VPAID' )
                                ),							
                                ! empty( $this->item->ad_engine ) ? $this->item->ad_engine : 'custom'
                            );
                        } else {
                            echo AllVideoShareHtml::RadioGroupAdEngine();
                        }
                        ?>                         
                    </div>                    
                </div>

                <div class="control-group avs-toggle-fields avs-ad-custom-fields">
                    <label class="control-label"><?php echo JText::_( 'PREROLL' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::ListBoolean( 'preroll', $this->item->preroll ); ?>
                    </div>
                </div>
                
                <div class="control-group avs-toggle-fields avs-ad-custom-fields">
                    <label class="control-label"><?php echo JText::_( 'POSTROLL' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::ListBoolean( 'postroll', $this->item->postroll ); ?>
                    </div>
                </div>

                <div class="control-group avs-toggle-fields avs-ad-vast-fields">
                    <label class="control-label"><?php echo JText::_( 'VAST_URL' ); ?></label>
                    <div class="controls">
                        <input type="text" name="vast_url" value="<?php echo $this->item->vast_url; ?>" />
                        <p class="help-block"><?php echo sprintf( JText::_( 'VAST_URL_DESCRIPTION' ), 'https://allvideoshare.mrvinoth.com/ad-tag-variables-macros' ); ?></p>
                    </div>
                </div>

                <div class="control-group avs-toggle-fields avs-ad-vast-fields">
                    <label class="control-label"><?php echo JText::_( 'VPAID_MODE' ); ?></label>
                    <div class="controls">
                        <?php
                            echo AllVideoShareHtml::ListItems(
                                'vpaid_mode',
                                array(
                                    'enabled'  => JText::_( 'ENABLED' ),
                                    'insecure' => JText::_( 'INSECURE' ),
                                    'disabled' => JText::_( 'DISABLED' )
                                ),							
                                ! empty( $this->item->vpaid_mode ) ? $this->item->vpaid_mode : 'insecure'
                            );
                        ?>
                    </div>
                </div>

                <div class="control-group avs-toggle-fields avs-ad-vast-fields">
                    <label class="control-label"><?php echo JText::_( 'LIVESTREAM_AD_INTERVAL' ); ?></label>
                    <div class="controls">
                        <input type="text" name="livestream_ad_interval" value="<?php echo ! empty( $this->item->livestream_ad_interval ) ? $this->item->livestream_ad_interval : 300; ?>" />
                        <p class="help-block"><?php echo JText::_( 'LIVESTREAM_AD_INTERVAL_DESCRIPTION' ); ?></p>
                    </div>
                </div>
            </fieldset>
        
        </div>

        <input type="hidden" name="boxchecked" value="1" />
        <input type="hidden" name="option" value="com_allvideoshare" />
        <input type="hidden" name="view" value="players" />
        <input type="hidden" name="task" value="" />
        <?php echo JHTML::_( 'form.token' ); ?>
  	</form>
</div>