<?php
/*
 * @version		$Id: default.php 3.3.0 2019-01-10 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$pixel_image = JURI::root() . 'components/com_allvideoshare/assets/images/pixel.png';
?>

<VAST xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="vast.xsd" version="3.0">
  <Ad id="<?php echo $this->ad->id; ?>">
    <InLine>
      <AdSystem><?php echo $this->sitename; ?></AdSystem>
      <AdTitle><?php echo $this->ad->title; ?></AdTitle>
      <Impression><![CDATA[ <?php echo JURI::root(); ?>index.php?option=com_allvideoshare&view=ads&task=impression&id=<?php echo $this->ad->id; ?> ]]></Impression>
      <Creatives>
        <Creative>
          <Linear>
            <Duration>00:00:30</Duration>
            <TrackingEvents>
              <Tracking event="start"><![CDATA[ <?php echo $pixel_image; ?> ]]></Tracking>
              <Tracking event="firstQuartile"><![CDATA[ <?php echo $pixel_image; ?> ]]></Tracking>
              <Tracking event="midpoint"><![CDATA[ <?php echo $pixel_image; ?> ]]></Tracking>
              <Tracking event="thirdQuartile"><![CDATA[ <?php echo $pixel_image; ?> ]]></Tracking>
              <Tracking event="complete"><![CDATA[ <?php echo $pixel_image; ?> ]]></Tracking>
              <Tracking event="pause"><![CDATA[ <?php echo $pixel_image; ?> ]]></Tracking>
              <Tracking event="mute"><![CDATA[ <?php echo $pixel_image; ?> ]]></Tracking>
              <Tracking event="fullscreen"><![CDATA[ <?php echo $pixel_image; ?> ]]></Tracking>
            </TrackingEvents>
            <VideoClicks>
              <ClickThrough><![CDATA[ <?php echo $this->ad->link; ?> ]]></ClickThrough>
              <ClickTracking><![CDATA[ <?php echo JURI::root(); ?>index.php?option=com_allvideoshare&view=ads&task=click&id=<?php echo $this->ad->id; ?> ]]></ClickTracking>
            </VideoClicks>
            <MediaFiles>
              <MediaFile type="video/mp4" bitrate="300" width="480" height="270">
              <![CDATA[ <?php echo $this->ad->video; ?> ]]>
              </MediaFile>
            </MediaFiles>
           </Linear>
        </Creative>
      </Creatives>
    </InLine>
  </Ad>
</VAST>