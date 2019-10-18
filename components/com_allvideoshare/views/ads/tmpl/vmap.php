<?php
/*
 * @version		$Id: default.php 3.3.0 2019-01-10 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?>

<vmap:VMAP xmlns:vmap="http://www.iab.net/videosuite/vmap" version="1.0">
  <?php if ( ! empty( $this->player->hasPreroll ) ) : ?>
    <vmap:AdBreak timeOffset="start" breakType="linear" breakId="preroll">
      <vmap:AdSource id="preroll-ad" allowMultipleAds="false" followRedirects="true">
        <vmap:AdTagURI templateType="vast3">
          <![CDATA[ <?php echo JURI::root(); ?>index.php?option=com_allvideoshare&view=ads&task=vast&id=<?php echo $this->player->prerollId; ?>&format=xml ]]>
        </vmap:AdTagURI>
      </vmap:AdSource>
    </vmap:AdBreak>
  <?php endif; ?>

  <?php if ( ! empty( $this->player->hasPostroll ) ) : ?>
    <vmap:AdBreak timeOffset="end" breakType="linear" breakId="postroll">
      <vmap:AdSource id="postroll-ad" allowMultipleAds="false" followRedirects="true">
        <vmap:AdTagURI templateType="vast3">
          <![CDATA[ <?php echo JURI::root(); ?>index.php?option=com_allvideoshare&view=ads&task=vast&id=<?php echo $this->player->postrollId; ?>&format=xml ]]>
        </vmap:AdTagURI>
      </vmap:AdSource>
    </vmap:AdBreak>
  <?php endif; ?>
</vmap:VMAP>