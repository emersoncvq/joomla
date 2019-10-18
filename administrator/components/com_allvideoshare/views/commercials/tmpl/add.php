<?php
/*
 * @version		$Id: add.php 3.3.0 2019-01-25 $
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
				if ( 'upload' == f.type_video.value ) {
					var value = f.upload_video.value;
					var url = value.split('.').pop();
					return ( url != '' ) ? /mp4|m4v|mov|flv/.test( url.toLowerCase() ) : true;
				} else if ( 'url' == f.type_video.value ) {
					var value = f.video.value;
					var url = value.split('.').pop();
					return ( url != '' ) ? /mp4|m4v|mov|flv/.test( url.toLowerCase() ) : true;
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

<div id="avs-commercials" class="avs commercials add">
  	<form action="index.php" method="post" name="adminForm" id="adminForm" class="form-horizontal form-validate" enctype="multipart/form-data">
  		<div class="control-group">
        	<label class="control-label"><?php echo JText::_( 'TITLE' ); ?><span class="star">&nbsp;*</span></label>
        	<div class="controls">
          		<input type="text" id="title" name="title" class="required" />
        	</div>
      	</div> 
        
        <div class="control-group">
        	<label class="control-label"><?php echo JText::_( 'TYPE' ); ?></label>
        	<div class="controls">
				<?php
                	echo AllVideoShareHtml::ListItems(
						'type',
						array(
							'preroll'  => JText::_( 'PREROLL' ),
					  		'postroll' => JText::_( 'POSTROLL' ),
					  		'both'     => JText::_( 'BOTH' )
						),							
						'both'
					);
				?>
            </div>
      	</div> 
        
        <div class="control-group">
            <label class="control-label"><?php echo JText::_( 'VIDEO' ); ?></label>
            <div class="controls">
                <?php echo AllVideoShareHtml::FileUploader( 'video' ); ?>
            </div>
        </div>
        
        <div class="control-group">
        	<label class="control-label"><?php echo JText::_( 'ADVERTISEMENT_LINK' ); ?></label>
        	<div class="controls"><input type="text" id="link" name="link" /></div>
      	</div> 
        
        <div class="control-group">
        	<label class="control-label"><?php echo JText::_( 'PUBLISH' ); ?></label>
        	<div class="controls"><?php echo AllVideoShareHtml::ListBoolean( 'published' ); ?></div>
      	</div> 

        <input type="hidden" name="boxchecked" value="1" />
        <input type="hidden" name="option" value="com_allvideoshare" />
        <input type="hidden" name="view" value="commercials" />
        <input type="hidden" name="task" value="" />
    	<?php echo JHTML::_( 'form.token' ); ?>
  	</form>
</div>