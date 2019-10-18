<?php
/*
 * @version		$Id: default.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/
 
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<div class="avs player <?php echo htmlspecialchars( $params->get( 'moduleclass_sfx' ) ); ?>">
	<?php if ( $params->get( 'title' ) ) : ?>
		<h3><?php echo $video->title; ?></h3>
	<?php endif; ?>

	<?php echo $player; ?>

	<?php if ( $params->get( 'description' ) ) : ?>
		<p style="margin-top: 15px;"><?php echo $video->description; ?></p>
	<?php endif; ?>
</div>