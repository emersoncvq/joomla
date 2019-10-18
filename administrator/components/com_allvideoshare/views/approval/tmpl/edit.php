<?php
/*
 * @version		$Id: edit.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

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
	
			document.formvalidator.setHandler( 'video', function( value ) {
				if ( 'general' == f.type.value ) {		
					if ( 'upload' == f.type_video.value ) {
						var value = f.upload_video.value;
						var url = value.split('.').pop();
						return ( url != '' ) ? /mp4|m4v|mov|flv/.test( url.toLowerCase() ) : true;
					} else if ( 'url' == f.type_video.value ) {
						var value = f.video.value;
						var url = value.split('.').pop();
						return /mp4|m4v|mov|flv/.test( url.toLowerCase() );
					};
				};
				
				return true;				
			});
			
			document.formvalidator.setHandler( 'hd', function( value ) {
				if ( 'general' == f.type.value ) {	
					if ( 'upload' == f.type_hd.value ) {
						var value = f.upload_hd.value;
						var url = value.split('.').pop();
						return ( url != '' ) ? /mp4|m4v|mov|flv/.test( url.toLowerCase() ) : true;
					} else if ( 'url' == f.type_hd.value ) {
						var value = f.hd.value;
						var url = value.split('.').pop();
						return ( url != '' ) ? /mp4|m4v|mov|flv/.test( url.toLowerCase() ) : true;
					};
				};
				
				return true;
			});
			
			document.formvalidator.setHandler( 'thumb', function( value ) {
				if ( 'upload' == f.type_thumb.value ) {
					var value = f.upload_thumb.value;
					var url = value.split('.').pop();
					return ( url != '' ) ? /jpg|jpeg|png|gif/.test( url.toLowerCase() ) : true;
				} else if ( 'url' == f.type_thumb.value ) {
					var value = f.thumb.value;
					var url = value.split('.').pop();
					return ( url != '' ) ? /jpg|jpeg|png|gif/.test( url.toLowerCase() ) : true;
				};
					
				return true;
			});
			
			document.formvalidator.setHandler( 'rtmp', function( value ) {
				if ( 'rtmp' == f.type.value ) {
					return value !== '';
				};
				
				return true;
			});
			
			document.formvalidator.setHandler( 'hls', function( value ) {
				if ( 'rtmp' == f.type.value ) {
					var url = value.split('.').pop();
					return ( url !== '' ) ? /m3u8/.test( url.toLowerCase() ) : true;
				} else if ( 'hls' == f.type.value ) {
					var url = value.split('.').pop();
					return /m3u8/.test( url.toLowerCase() );
				};
				
				return true;
			});
			
			document.formvalidator.setHandler( 'thirdparty', function( value ) {
				if ( 'thirdparty' == f.type.value ) {
					return value !== '';
				};
				
				return true;
			});
			
        	if ( document.formvalidator.isValid( f ) ) {
            	submitform( pressbutton );    
        	};
			
    	};  
		
	};
");
?>

<div id="avs-videos" class="avs videos edit">
  	<form action="index.php" method="post" name="adminForm" id="adminForm" class="form-horizontal form-validate" enctype="multipart/form-data">
      	<div class="row-fluid">
        
        	<!-- GENERAL_SETTINGS -->
            <fieldset>
            
            	<legend><?php echo JText::_( 'GENERAL_SETTINGS' ); ?></legend>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'TITLE' ); ?><span class="star">&nbsp;*</span></label>
                    <div class="controls">
                        <input type="text" id="title" name="title" class="required" value="<?php echo htmlentities( $this->item->title ); ?>" />
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'SLUG' ); ?></label>
                    <div class="controls">
                        <input type="text" id="slug" name="slug" value="<?php echo $this->item->slug; ?>" />
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'SELECT_A_CATEGORY' ); ?><span class="star">&nbsp;*</span></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::ListCategories( 'catid', $this->item->catid, 'class="required"' ); ?>
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'TYPE' ); ?></label>
                    <div class="controls">
                        <?php
                        $type = $this->item->type;

                        if ( 'url' == $type || 'general' == $type ) {
                            $type = 'general';
                        }

                        if ( 1 == $this->config->is_premium ) {
                            echo AllVideoShareHtml::ListItems(
                                'type',
                                array(
                                    'general'    => JText::_( 'SELF_HOSTED_EXTERNAL_URL' ),
                                    'youtube'    => JText::_( 'YOUTUBE' ),
									'vimeo'      => JText::_( 'VIMEO' ),
                                    'rtmp'       => JText::_( 'RTMP_STREAMING' ),
									'hls'        => JText::_( 'HLS' ),
                                    'thirdparty' => JText::_( 'THIRD_PARTY_EMBEDCODE' ),
                                ),							
                                $type
                            );
                        } else {
                            echo AllVideoShareHtml::ListTypes(
                                'type',
                                array(
                                    'general' => JText::_( 'SELF_HOSTED_EXTERNAL_URL' ),                                    
                                    'rtmp'    => JText::_( 'RTMP_STREAMING' )
                                ),							
                                $type
                            );
                        }
                        ?>
                    </div>
                </div>
                
                <div class="control-group avs-toggle-fields avs-general-fields">
                    <label class="control-label"><?php echo JText::_( 'VIDEO' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::FileUploader( 'video', $this->item->video ); ?>
                    </div>
                </div>
                
                <div class="control-group avs-toggle-fields avs-general-fields">
                    <label class="control-label"><?php echo JText::_( 'HD_VIDEO' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::FileUploader( 'hd', $this->item->hd ); ?>
                    </div>
                </div>
                
                <div class="control-group avs-toggle-fields avs-rtmp-fields">
                    <label class="control-label"><?php echo JText::_( 'STREAMER' ); ?><span class="star">&nbsp;*</span></label>
                    <div class="controls">
                        <input type="text" id="streamer" name="streamer" class="required validate-rtmp" value="<?php echo $this->item->streamer; ?>" />
                    </div>
                </div>
                
                <div class="control-group avs-toggle-fields avs-youtube-fields avs-vimeo-fields avs-rtmp-fields">
                    <label class="control-label"><?php echo JText::_( 'VIDEO' ); ?><span class="star">&nbsp;*</span></label>
                    <div class="controls">
                        <input type="text" id="external" name="external" class="required validate-external" value="<?php echo $this->item->video; ?>" />
                    </div>
                </div>
                
                <div class="control-group avs-toggle-fields avs-rtmp-fields avs-hls-fields">
                    <label class="control-label"><?php echo JText::_( 'HLS' ); ?><span class="star" style="display: none;">&nbsp;*</span></label>
                    <div class="controls">
                        <input type="text" id="hls" name="hls" class="validate-hls" value="<?php echo $this->item->hls; ?>" />
                    </div>
                </div>
                
                <div class="control-group avs-toggle-fields avs-rtmp-fields">
                    <label class="control-label"><?php echo JText::_( 'TOKEN' ); ?></label>
                    <div class="controls">
                        <input type="text" id="token" name="token" value="<?php echo $this->item->token; ?>" />
                    </div>
                </div>
                
                <div class="control-group avs-toggle-fields avs-thirdparty-fields">
                    <label class="control-label"><?php echo JText::_( 'THIRD_PARTY_EMBEDCODE' ); ?><span class="star">&nbsp;*</span></label>
                    <div class="controls">
                        <textarea name="thirdparty" id="thirdparty" class="required validate-thirdparty" rows="6" cols="50"><?php echo $this->item->thirdparty; ?></textarea>
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'THUMB' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::FileUploader( 'thumb', $this->item->thumb ); ?>
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'USER' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::ListUsers( 'user', $this->item->user ); ?>
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'ACCESS' ); ?></label>
                    <div class="controls">
                    	<?php
							if( 'public' == $this->item->access ) {
								$access = 1;
							} elseif ( 'registered' == $this->item->access ) {
								$access = 2;
							} else {
								$access = (int) $this->item->access;
							}
		
							echo JHtml::_( 'access.level', 'access', $access, '', false );
						?>
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'FEATURED' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::ListBoolean( 'featured', $this->item->featured ); ?>
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'DATE_ADDED' ); ?></label>
                    <div class="controls">
                        <?php echo JHTML::calendar( $this->item->created_date, 'created_date', 'published_up', "%Y-%m-%d %H:%M:%S", '' ); ?>
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'PUBLISH' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::ListBoolean( 'published', $this->item->published ); ?>
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'DESCRIPTION' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::Editor( 'description', $this->item->description ); ?>
                    </div>
                </div>

            </fieldset>
            
            <!-- ADVANCED_SETTINGS -->
            <fieldset>
            
            	<legend><?php echo JText::_( 'ADVANCED_SETTINGS' ); ?></legend>

                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'META_KEYWORDS' ); ?></label>
                    <div class="controls">
                        <textarea name="tags" rows="3"><?php echo $this->item->tags; ?></textarea>
                        <span class="help-block"><?php echo JText::_( 'META_KEYWORDS_DESCRIPTION' ); ?></span>
                    </div>
                </div>
                
                 <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'META_DESCRIPTION' ); ?></label>
                    <div class="controls">
                        <textarea name="metadescription" rows="3"><?php echo $this->item->metadescription; ?></textarea>
                    </div>
                </div>
                
            </fieldset>
        
        </div>
        <input type="hidden" name="boxchecked" value="1" />
        <input type="hidden" name="option" value="com_allvideoshare" />
        <input type="hidden" name="view" value="videos" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
        <?php echo JHTML::_( 'form.token' ); ?>
  	</form>
</div>