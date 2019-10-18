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

        	if ( document.formvalidator.isValid( f ) ) {
            	submitform( pressbutton );
        	};
			
    	};  
		
	};
");
?>

<div id="avs-categories" class="avs categories add">
  	<form action="index.php" method="post" name="adminForm" id="adminForm" class="form-horizontal form-validate" enctype="multipart/form-data">
    	<div class="row-fluid">
        
        	<!-- GENERAL_SETTINGS -->
        	<fieldset>
        
        		<legend><?php echo JText::_( 'GENERAL_SETTINGS' ); ?></legend>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'NAME' ); ?><span class="star">&nbsp;*</span></label>
                    <div class="controls">
                        <input type="text" id="name" name="name" class="required" value="<?php echo $this->item->name; ?>" />
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'SLUG' ); ?></label>
                    <div class="controls">
                        <input type="text" id="slug" name="slug" value="<?php echo $this->item->slug; ?>" />
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'PARENT' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::ListCategories( 'parent', $this->item->parent, '', $this->item->id ); ?>
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'THUMB' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::FileUploader( 'thumb', $this->item->thumb ); ?>
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'ACCESS' ); ?></label>
                    <div class="controls">
                    	<?php
							if ( 'public' == $this->item->access ) {
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
                    <label class="control-label"><?php echo JText::_( 'PUBLISH' ); ?></label>
                    <div class="controls">
                        <?php echo AllVideoShareHtml::ListBoolean( 'published', $this->item->published ); ?>
                    </div>
                </div>
                
            </fieldset>
            
            <!-- ADVANCED_SETTINGS -->
            <fieldset>
            
            	<legend><?php echo JText::_( 'ADVANCED_SETTINGS' ); ?></legend>
            
                <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'META_KEYWORDS' ); ?></label>
                    <div class="controls">
                        <textarea name="metakeywords" rows="3" cols="50" ><?php echo $this->item->metakeywords; ?></textarea>
                        <span class="help-block"><?php echo JText::_( 'META_KEYWORDS_DESCRIPTION' ); ?></span>
                    </div>
                </div>
                
                 <div class="control-group">
                    <label class="control-label"><?php echo JText::_( 'META_DESCRIPTION' ); ?></label>
                    <div class="controls">
                        <textarea name="metadescription" rows="3" cols="50" ><?php echo $this->item->metadescription; ?></textarea>
                    </div>
                </div>
                
            </fieldset>
        
        </div>
     	<input type="hidden" name="boxchecked" value="1" />
        <input type="hidden" name="option" value="com_allvideoshare" />
        <input type="hidden" name="view" value="categories" />
        <input type="hidden" name="task" value="" />
    	<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
        <?php echo JHTML::_( 'form.token' ); ?>
  	</form>
</div>