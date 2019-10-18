<?php
/*
 * @version		$Id: edit.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$app = JFactory::getApplication();

$doc = JFactory::getDocument();
$doc->addScriptDeclaration("
	jQuery( document ).ready(function() {
	
    	var f = document.avsForm;	
	
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
			}
			
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
		
	});
");

$itemId = $app->input->getInt( 'Itemid' ) ? '&Itemid=' . $app->input->getInt( 'Itemid' ) : '';
?>

<div id="avs-videos" class="avs videos edit <?php echo $this->escape( $this->params->get( 'pageclass_sfx' ) ); ?>">
	<div class="page-header">
  		<h1> <?php echo JText::_( 'EDIT_THIS_VIDEO' ); ?> </h1>
    </div>
    
  	<form action="index.php" method="post" name="avsForm" id="avsForm" enctype="multipart/form-data" class="form-horizontal form-validate">
      	<div class="row-fluid">
        
            <fieldset>
                <div class="control-group">
                    <label class="control-label" for="title"><?php echo JText::_( 'TITLE' ); ?><span class="star">&nbsp;*</span></label>
                    <div class="controls">
                        <input type="text" id="title" name="title" class="required" value="<?php echo htmlentities( $this->item->title ); ?>" />
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label" for="category"><?php echo JText::_( 'SELECT_A_CATEGORY' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::ListCategories( 'catid', $this->item->catid, 'class="required"' ); ?>
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label" for="type"><?php echo JText::_( 'TYPE' ); ?></label>
                    <div class="controls">
                        <?php
							$types = array(
								'general' => JText::_( 'SELF_HOSTED_EXTERNAL_URL' )
							);
							
							if ( 1 == $this->config->is_premium && $this->config->type_youtube ) {
								$types['youtube'] = JText::_( 'YOUTUBE' );
							}
							
							if ( 1 == $this->config->is_premium && $this->config->type_vimeo ) {
								$types['vimeo'] = JText::_( 'VIMEO' );
							}
							
							if ( $this->config->type_rtmp ) {
								$types['rtmp'] = JText::_( 'RTMP_STREAMING' );
							}
							
							if ( 1 == $this->config->is_premium && $this->config->type_hls ) {
								$types['hls'] = JText::_( 'HLS' );
							}
							
							$type = $this->item->type;
							if ( 'url' == $type || 'general' == $type ) {
								$type = 'general';
							}
							
							echo AllVideoShareHtml::ListItems( 'type', $types, $type );
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
                    <label class="control-label" for="streamer"><?php echo JText::_( 'STREAMER' ); ?><span class="star">&nbsp;*</span></label>
                    <div class="controls">
                        <input type="text" id="streamer" name="streamer" class="required validate-rtmp" value="<?php echo $this->item->streamer; ?>" />
                    </div>
                </div>
                
                <div class="control-group avs-toggle-fields avs-youtube-fields avs-vimeo-fields avs-rtmp-fields">
                    <label class="control-label" for="external"><?php echo JText::_( 'VIDEO' ); ?><span class="star">&nbsp;*</span></label>
                    <div class="controls">
                        <input type="text" id="external" name="external" class="required validate-external" value="<?php echo $this->item->video; ?>" />
                    </div>
                </div>
                
                <?php if ( $this->config->type_hls ) : ?>
                    <div class="control-group avs-toggle-fields avs-rtmp-fields avs-hls-fields">
                        <label class="control-label" for="hls"><?php echo JText::_( 'HLS' ); ?><span class="star" style="display: none;">&nbsp;*</span></label>
                        <div class="controls">
                            <input type="text" id="hls" name="hls" class="validate-hls" value="<?php echo $this->item->hls; ?>" />
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="control-group avs-toggle-fields avs-rtmp-fields">
                    <label class="control-label" for="token"><?php echo JText::_( 'TOKEN' ); ?></label>
                    <div class="controls">
                        <input type="text" id="token" name="token" value="<?php echo $this->item->token; ?>" />
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'THUMB' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::FileUploader( 'thumb', $this->item->thumb ); ?>
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label" for="description"><?php echo JText::_( 'DESCRIPTION' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::Editor( 'description', $this->item->description ); ?>
                    </div>
                </div>
            </fieldset>
            
            <fieldset>
            	<legend><?php echo JText::_( 'SEO_SETTINGS' ); ?></legend>
                
                <div class="control-group">
                    <label class="control-label" for="tags"><?php echo JText::_( 'META_KEYWORDS' ); ?></label>
                    <div class="controls">
                        <textarea name="tags" rows="3"><?php echo $this->item->tags; ?></textarea>
                        <span class="help-block"><?php echo JText::_( 'META_KEYWORDS_DESCRIPTION' ); ?></span>
                    </div>
                </div>
                
                 <div class="control-group">
                    <label class="control-label" for="metadescription"><?php echo JText::_( 'META_DESCRIPTION' ); ?></label>
                    <div class="controls">
                        <textarea name="metadescription" rows="3"><?php echo $this->item->metadescription; ?></textarea>
                    </div>
                </div>
            </fieldset>
        
        </div>
        <input type="hidden" name="boxchecked" value="1" />
        <input type="hidden" name="option" value="com_allvideoshare" />
        <input type="hidden" name="view" value="user" />
        <input type="hidden" name="task" value="save" />
        <input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
        <input type="hidden" name="Itemid" value="<?php echo $app->input->getInt('Itemid'); ?>" />
        <?php echo JHTML::_( 'form.token' ); ?>
        
        <div class="form-actions muted">
        	<input type="submit" class="btn btn-primary validate" value="<?php echo JText::_( 'SAVE_VIDEO' ); ?>" />
            <a class="btn" href="<?php echo JRoute::_( 'index.php?option=com_allvideoshare&view=user'.$itemId ); ?>"><?php echo JText::_('CANCEL'); ?></a>
       	</div> 
  	</form>
</div>