<?php

/*
 * @version		$Id: script.allvideoshare.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class Com_AllVideoShareInstallerScript {

	public function postflight( $type, $parent ) {
	
		$db = JFactory::getDBO();
		
		$status = new JObject();
		$status->modules = array();
		$status->plugins = array();
		$src = $parent->getParent()->getPath( 'source' );
        $manifest = $parent->getParent()->manifest;
		
		// Install modules
        $modules = $manifest->xpath( 'modules/module' );
        foreach ( $modules as $module ) {
            $name = (string) $module->attributes()->module;
            $client = (string) $module->attributes()->client;
            $path = $src . '/modules/' . $name;
            $installer = new JInstaller;
            $result = $installer->install( $path );
            $status->modules[] = array( 'name' => $name, 'client' => $client, 'result' => $result );
        }
		
		// Install plugins
        $plugins = $manifest->xpath( 'plugins/plugin' );
        foreach ( $plugins as $plugin ) {
            $name = (string) $plugin->attributes()->plugin;
            $group = (string) $plugin->attributes()->group;
            $path = $src . '/plugins/' . $group . '/' . $name;
            $installer = new JInstaller;
            $result = $installer->install( $path );
			
            $query = "UPDATE #__extensions SET enabled=1 WHERE type='plugin' AND element=" . $db->Quote( $name ) . " AND folder=" . $db->Quote( $group );
            $db->setQuery( $query );
            $db->query();
			
            $status->plugins[] = array( 'name' => $name, 'group' => $group, 'result' => $result );
        }
	
		// Insert default data
		$query = "SELECT COUNT(id) FROM #__allvideoshare_config";
		$db->setQuery( $query );
		
		if ( ! $db->loadResult() ) {
		
			// Insert default player data
			$row = new JObject();
			$row->id = 1;
			$row->type = 'mediaelement';
			$row->name = 'Default';
			$row->ratio = 56.25;
			$row->loop = 0;
			$row->autostart = 0;
			$row->buffer = 3;
			$row->volumelevel = 50;
			$row->stretch = 'uniform';
			$row->controlbar = 1;
			$row->durationdock = 1;
			$row->timerdock = 1;
			$row->fullscreendock = 1;
			$row->hddock = 1;
			$row->embeddock = 1;
			$row->sharedock = 1;
			$row->facebookdock = 1;
			$row->twitterdock = 1;
			$row->controlbaroutlinecolor = '0x292929';
			$row->controlbarbgcolor = '0x111111';
			$row->controlbaroverlaycolor = '0x252525';
			$row->controlbaroverlayalpha = 35;
			$row->iconcolor = '0xDDDDDD';
			$row->progressbarbgcolor = '0x090909';
			$row->progressbarbuffercolor = '0x121212';
			$row->progressbarseekcolor = '0x202020';
			$row->volumebarbgcolor = '0x252525';
			$row->volumebarseekcolor = '0x555555';
			$row->ad_engine = 'custom';
			$row->preroll = 0;
			$row->postroll = 0;
			$row->vast_url = '';
			$row->vpaid_mode = 'insecure';
			$row->livestream_ad_interval = 300;
			$row->published = 1;
			$db->insertObject( '#__allvideoshare_players', $row );
	
			// Insert default config data
			$row = new JObject();
			$row->id = 1;
			$row->rows = 3;
			$row->cols = 3;
			$row->image_ratio = 56.25;
			$row->playerid = 1;
			$row->layout = 'none';
			$row->relatedvideoslimit = 4;
			$row->title = 1;
			$row->description = 1;
			$row->category = 1;
			$row->views = 1;
			$row->search = 1;
			$row->comments_type = 'facebook';
			$row->comments_posts = 2;
			$row->comments_color = 'light';
			$row->auto_approval = 1;
			$row->type_youtube = 1;
			$row->type_rtmp = 0;
			$row->load_bootstrap_css = 0;
			$row->load_icomoon_font = 0;
			$row->custom_css = '';
			$row->show_feed = 1;
			$row->feed_limit = 20;
			$row->show_gdpr_consent = 0;
			$row->itemid_video = 0;
			$row->is_premium = 0;
			$db->insertObject( '#__allvideoshare_config', $row );
	
			// Insert licensing data
			$row = new JObject();
			$row->id = 1;
			$row->licensekey = '';
			$row->type = 'upload';
			$row->logo = '';
			$row->logoposition = 'bottomleft';
			$row->logoalpha = 50;
			$row->logotarget = 'http://allvideoshare.mrvinoth.com/';
			$row->displaylogo = 1;
			$db->insertObject( '#__allvideoshare_licensing', $row );
			
		}
			
		$this->installationResults( $status );
		
	}
	
	public function update( $type ) {
	
		$db = JFactory::getDBO();
		
		$fields_config = $db->getTableColumns( '#__allvideoshare_config' );
		$fields_players = $db->getTableColumns( '#__allvideoshare_players' );
		$fields_categories = $db->getTableColumns( '#__allvideoshare_categories' );
		$fields_videos = $db->getTableColumns( '#__allvideoshare_videos' );
		
		// Version 1.1.0
		if ( ! array_key_exists( 'auto_approval', $fields_config ) ) {
			$query = "ALTER TABLE #__allvideoshare_config ADD `auto_approval` TINYINT(4) NOT NULL AFTER `comments_color`";
			$db->setQuery( $query );
			$db->query();
		}

		// Version 1.2.0
		if ( ! array_key_exists( 'controlbar', $fields_players ) ) {
			$query = "ALTER TABLE #__allvideoshare_players ADD `controlbar` TINYINT(4) NOT NULL, ADD `playlist` TINYINT(4) NOT NULL AFTER `stretch`";
			$db->setQuery( $query );
			$db->query();
		}

		if ( ! array_key_exists( 'playlistbgcolor', $fields_players ) ) {
			$query = "ALTER TABLE #__allvideoshare_players ADD `playlistbgcolor` VARCHAR(255) NOT NULL, ADD `customplayerpage` VARCHAR(255) NOT NULL AFTER `volumebarseekcolor`";
			$db->setQuery( $query );
			$db->query();
		}

		if ( ! array_key_exists('type_youtube', $fields_config ) ) {
			$query = "ALTER TABLE #__allvideoshare_config ADD `type_youtube` TINYINT(4) NOT NULL, ADD `type_rtmp` TINYINT(4) NOT NULL AFTER `auto_approval`";
			$db->setQuery( $query );
			$db->query();
		}

		// Version 1.2.3
		if ( ! array_key_exists( 'parent', $fields_categories ) ) {
			$query = "ALTER TABLE #__allvideoshare_categories ADD `parent` INT(10) NOT NULL AFTER `slug`";
			$db->setQuery( $query );
			$db->query();
		}

		if ( ! array_key_exists( 'access', $fields_categories ) ) {
			$query = "ALTER TABLE #__allvideoshare_categories ADD `access` VARCHAR(25) NOT NULL, ADD `ordering` INT(5) NOT NULL, ADD `metakeywords` TEXT NOT NULL, ADD `metadescription` TEXT NOT NULL AFTER `thumb`";
			$db->setQuery( $query );
			$db->query();
		}

		if ( ! array_key_exists( 'metadescription', $fields_videos ) ) {
			$query = "ALTER TABLE #__allvideoshare_videos ADD `metadescription` TEXT NOT NULL AFTER `tags`";
			$db->setQuery( $query );
			$db->query();
		}
	
		if ( ! array_key_exists( 'access', $fields_videos ) ) {
			$query = "ALTER TABLE #__allvideoshare_videos ADD `access` VARCHAR(25) NOT NULL AFTER `views`";
			$db->setQuery( $query );
			$db->query();
		}

		if ( ! array_key_exists( 'comments_type', $fields_config ) ) {
			$query = "ALTER TABLE #__allvideoshare_config ADD `comments_type` VARCHAR(50) NOT NULL AFTER `search`";
			$db->setQuery( $query );
			$db->query();
		}
		
		// Version 2.0.0
		if ( ! array_key_exists( 'fbappid', $fields_config ) ) {
			$query = "ALTER TABLE #__allvideoshare_config ADD `fbappid` VARCHAR(25) NOT NULL AFTER `comments_type`";
			$db->setQuery( $query );
			$db->query();
		}
		
		// Version 2.1.0	
		$query = "CREATE TABLE IF NOT EXISTS `#__allvideoshare_adverts` (
  			`id` int(5) NOT NULL AUTO_INCREMENT,
  			`title` varchar(255) NOT NULL,
  			`type` varchar(25) NOT NULL,
			`method` varchar(25) NOT NULL,
  			`video` varchar(255) NOT NULL,
			`link` varchar(255) NOT NULL,
			`impressions` int(10) NOT NULL,
			`clicks` int(10) NOT NULL,
  			`published` tinyint(4) NOT NULL,
  			PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		$db->setQuery( $query );
		$db->query();
		
		if ( ! array_key_exists( 'preroll', $fields_players ) ) {
			$query = "ALTER TABLE #__allvideoshare_players ADD `preroll` TINYINT(4) NOT NULL, ADD `postroll` TINYINT(4) NOT NULL AFTER `customplayerpage`";
			$db->setQuery( $query );
			$db->query();
		}
		
		// Version 3.0.0
		if ( ! array_key_exists( 'type', $fields_players ) ) {
			$query = "ALTER TABLE #__allvideoshare_players ADD `type` VARCHAR(25) NOT NULL AFTER `id`";
			$db->setQuery( $query );
			$db->query();
		}
		
		if ( ! array_key_exists( 'load_bootstrap_css', $fields_config ) ) {
			$query = "ALTER TABLE #__allvideoshare_config ADD `load_bootstrap_css` TINYINT(4) NOT NULL AFTER `css`";
			$db->setQuery( $query );
			$db->query();
		}
		
		if ( ! array_key_exists( 'load_icomoon_font', $fields_config ) ) {
			$query = "ALTER TABLE #__allvideoshare_config ADD `load_icomoon_font` TINYINT(4) NOT NULL AFTER `load_bootstrap_css`";
			$db->setQuery( $query );
			$db->query();
		}
		
		if ( ! array_key_exists( 'custom_css', $fields_config ) ) {
			$query = "ALTER TABLE #__allvideoshare_config ADD `custom_css` TEXT NOT NULL AFTER `load_icomoon_font`";
			$db->setQuery( $query );
			$db->query();
		}
		
		if ( ! array_key_exists( 'show_feed', $fields_config ) ) {
			$query = "ALTER TABLE #__allvideoshare_config ADD `show_feed` TINYINT(4) NOT NULL AFTER `custom_css`";
			$db->setQuery( $query );
			$db->query();
		}
		
		if ( ! array_key_exists( 'feed_limit', $fields_config ) ) {
			$query = "ALTER TABLE #__allvideoshare_config ADD `feed_limit` INT(10) NOT NULL AFTER `show_feed`";
			$db->setQuery( $query );
			$db->query();
		}
		
		if ( ! array_key_exists( 'created_date', $fields_videos ) ) {
			$query = "ALTER TABLE #__allvideoshare_videos ADD `created_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `published`";
			$db->setQuery( $query );
			$db->query();
		}
		
		$query = "UPDATE #__allvideoshare_videos SET created_date=now() WHERE created_date=" . $db->Quote( '0000-00-00 00:00:00' );
		$db->setQuery( $query );
		$db->query();
		
		// Version 3.1.0
		if ( ! array_key_exists( 'type_vimeo', $fields_config ) ) {
			$query = "ALTER TABLE #__allvideoshare_config ADD `type_vimeo` TINYINT(4) NOT NULL AFTER `type_youtube`";
			$db->setQuery( $query );
			$db->query();
		}
		
		if ( ! array_key_exists( 'type_hls', $fields_config ) ) {
			$query = "ALTER TABLE #__allvideoshare_config ADD `type_hls` TINYINT(4) NOT NULL AFTER `type_rtmp`";
			$db->setQuery( $query );
			$db->query();
		}
		
		if ( ! array_key_exists( 'hls', $fields_videos ) ) {
			$query = "ALTER TABLE #__allvideoshare_videos ADD `hls` VARCHAR(255) NOT NULL AFTER `hd`";
			$db->setQuery( $query );
			$db->query();
		}
		
		// Version 3.2.0
		if ( ! array_key_exists( 'ratio', $fields_players ) ) {
			$query = "ALTER TABLE #__allvideoshare_players ADD `ratio` DECIMAL(16,2) NOT NULL AFTER `name`";
			$db->setQuery( $query );
			$db->query();
		}
		
		if ( ! array_key_exists( 'image_ratio', $fields_config ) ) {
			$query = "ALTER TABLE #__allvideoshare_config ADD `image_ratio` DECIMAL(16,2) NOT NULL AFTER `cols`";
			$db->setQuery( $query );
			$db->query();
		}
		
		if ( ! array_key_exists( 'catid', $fields_videos ) ) {
			$query = "ALTER TABLE #__allvideoshare_videos ADD `catid` INT(5) NOT NULL AFTER `slug`";
			$db->setQuery( $query );
			$db->query();
			
			$query = "SELECT id, name FROM #__allvideoshare_categories";
			$db->setQuery( $query );
         	$items = $db->loadObjectList();
			
			foreach ( $items as $item ) {
				$query = "UPDATE #__allvideoshare_videos SET catid=" . $item->id . " WHERE category=" . $db->Quote( $item->name );
				$db->setQuery ( $query );
				$db->query();
			}
		}

		// Version 3.3.0
		if ( ! array_key_exists( 'show_gdpr_consent', $fields_config ) ) {
			$query = "ALTER TABLE #__allvideoshare_config ADD `show_gdpr_consent` TINYINT(4) NOT NULL AFTER `feed_limit`";
			$db->setQuery( $query );
			$db->query();
		}

		if ( ! array_key_exists( 'itemid_video', $fields_config ) ) {
			$query = "ALTER TABLE #__allvideoshare_config ADD `itemid_video` INT(5) NOT NULL AFTER `show_gdpr_consent`";
			$db->setQuery( $query );
			$db->query();
		}

		if ( ! array_key_exists( 'ad_engine', $fields_players ) ) {
			$query = "ALTER TABLE #__allvideoshare_players ADD `ad_engine` VARCHAR(10) NOT NULL AFTER `customplayerpage`";
			$db->setQuery( $query );
			$db->query();
		}

		if ( ! array_key_exists( 'vast_url', $fields_players ) ) {
			$query = "ALTER TABLE #__allvideoshare_players ADD `vast_url` TEXT NOT NULL, ADD `vpaid_mode` VARCHAR(10) NOT NULL, ADD `livestream_ad_interval` INT(10) NOT NULL AFTER `postroll`";
			$db->setQuery( $query );
			$db->query();
		}

		// Version 3.4.0
		if ( ! array_key_exists( 'is_premium', $fields_config ) ) {
			$query = "ALTER TABLE #__allvideoshare_config ADD `is_premium` TINYINT(4) NOT NULL AFTER `itemid_video`";
			$db->setQuery( $query );
			$db->query();
		}

		$query = "UPDATE #__allvideoshare_config SET is_premium=0 WHERE id=1";
		$db->setQuery( $query );
		$db->query();
		
		if ( ! array_key_exists( 'sharedock', $fields_players ) ) {
			$query = "ALTER TABLE #__allvideoshare_players ADD `sharedock` TINYINT(4) NOT NULL AFTER `embeddock`";
			$db->setQuery( $query );
			$db->query();
		}
		
	}
	
	public function uninstall( $parent ) {
	
		$db = JFactory::getDBO();
		
		$status = new JObject();
		$status->modules = array();
		$status->plugins = array();	
		$manifest = $parent->getParent()->manifest;

		// Uninstall modules
        $modules = $manifest->xpath( 'modules/module' );
        foreach ( $modules as $module ) {
            $name = (string) $module->attributes()->module;
            $client = (string) $module->attributes()->client;
			
            $query = "SELECT `extension_id` FROM `#__extensions` WHERE `type`='module' AND element=" . $db->Quote( $name );
            $db->setQuery( $query );			
            $extensions = $db->loadColumn();
			
            if ( count( $extensions ) ) {
                foreach ( $extensions as $id ) {
                    $installer = new JInstaller;
                    $result = $installer->uninstall( 'module', $id );
                }
                $status->modules[] = array( 'name' => $name, 'client' => $client, 'result' => $result );
            }            
        }
		
		// Uninstall plugins
		$plugins = $manifest->xpath( 'plugins/plugin' );
        foreach ( $plugins as $plugin ) {
            $name  = (string) $plugin->attributes()->plugin;
            $group = (string) $plugin->attributes()->group;
			
            $query = "SELECT `extension_id` FROM #__extensions WHERE `type`='plugin' AND element=" . $db->Quote( $name ) . " AND folder=" . $db->Quote( $group );
            $db->setQuery( $query );
            $extensions = $db->loadColumn();
			
            if( count( $extensions ) ) {
                foreach( $extensions as $id ) {
                    $installer = new JInstaller;
                    $result = $installer->uninstall( 'plugin', $id );
                }
                $status->plugins[] = array( 'name' => $name, 'group' => $group, 'result' => $result );
            }
            
        }
		
        $this->unInstallationResults( $status );
		
	}
	
	public function installationResults( $status ) {
	
		$language = JFactory::getLanguage();
        $language->load( 'com_allvideoshare' );
		?>
  		<table class="table table-striped">
    	  <thead>
      		<tr>
        	  <th colspan="2"><?php echo JText::_( 'EXTENSION' ); ?></th>
        	  <th width="30%"><?php echo JText::_( 'STATUS' ); ?></th>
     		</tr>
    	  </thead>
    	  <tbody>
      		<tr>
        	  <td colspan="2"><?php echo 'All Video Share - '.JText::_( 'COMPONENT' ); ?></td>
        	  <td><strong><?php echo JText::_( 'INSTALLED' ); ?></strong></td>
      		</tr>
            
      		<?php if ( count( $status->modules ) ) : ?>
      			<tr>
        	  		<th><?php echo JText::_( 'MODULE' ); ?></th>
        	  		<th><?php echo JText::_( 'CLIENT' ); ?></th>
        	  		<th></th>
      			</tr>
      			<?php foreach ( $status->modules as $module ) : ?>
      				<tr>
        	  			<td><?php echo $module['name']; ?></td>
        	  			<td><?php echo ucfirst( $module['client'] ); ?></td>
        	  			<td><strong><?php echo ( $module['result'] ) ? JText::_( 'INSTALLED' ) : JText::_( 'NOT_INSTALLED' ); ?></strong></td>
      				</tr>
      			<?php endforeach;?>
      		<?php endif;?>
            
      		<?php if ( count( $status->plugins ) ) : ?>
      			<tr>
        			<th><?php echo JText::_( 'PLUGIN' ); ?></th>
        	  		<th><?php echo JText::_( 'GROUP' ); ?></th>
        	  		<th></th>
      			</tr>
      			<?php foreach ( $status->plugins as $plugin ) : ?>
      				<tr>
       		  			<td><?php echo $plugin['name']; ?></td>
        	  			<td><?php echo ucfirst( $plugin['group'] ); ?></td>
        	  			<td><strong><?php echo ( $plugin['result'] ) ? JText::_( 'INSTALLED' ) : JText::_( 'NOT_INSTALLED' ); ?></strong></td>
      				</tr>
      			<?php endforeach; ?>
      		<?php endif; ?>
            
    	  </tbody>
  		</table>
		<?php
		
	}
	
	public function unInstallationResults( $status ) {
	
		$language = JFactory::getLanguage();
        $language->load( 'com_allvideoshare' );
		?>
  		<table class="table table-striped">
    	  <thead>
      	    <tr>
        	  <th colspan="2"><?php echo JText::_( 'EXTENSION' ); ?></th>
        	  <th width="30%"><?php echo JText::_( 'STATUS' ); ?></th>
      		</tr>
    	  </thead>
    	  <tbody>
      		<tr>
        	  <td colspan="2"><?php echo 'All Video Share - '.JText::_( 'COMPONENT' ); ?></td>
        	  <td><strong><?php echo JText::_( 'REMOVED' ); ?></strong></td>
      		</tr>
            
      		<?php if ( count( $status->modules ) ) : ?>
      			<tr>
              		<th><?php echo JText::_( 'MODULE' ); ?></th>
              		<th><?php echo JText::_( 'CLIENT' ); ?></th>
        	  		<th></th>
      			</tr>
      			<?php foreach ( $status->modules as $module ) : ?>
      				<tr>
        	  			<td><?php echo $module['name']; ?></td>
        	  			<td><?php echo ucfirst( $module['client'] ); ?></td>
        	  			<td><strong><?php echo ( $module['result'] ) ? JText::_( 'REMOVED' ) : JText::_( 'NOT_REMOVED' ); ?></strong></td>
      				</tr>
      			<?php endforeach;?>
      		<?php endif;?>
            
      		<?php if ( count( $status->plugins ) ) : ?>
      			<tr>
        	  		<th><?php echo JText::_( 'PLUGIN' ); ?></th>
          	  		<th><?php echo JText::_( 'GROUP' ); ?></th>
        	  		<th></th>
      			</tr>
      			<?php foreach ( $status->plugins as $plugin ) : ?>
      				<tr>
        	  			<td><?php echo $plugin['name']; ?></td>
        	  			<td><?php echo ucfirst( $plugin['group'] ); ?></td>
        	  			<td><strong><?php echo ( $plugin['result'] ) ? JText::_( 'REMOVED' ) : JText::_( 'NOT_REMOVED' ); ?></strong></td>
      				</tr>
      			<?php endforeach; ?>
      		<?php endif; ?>
    	  </tbody>
  		</table>
		<?php
   	}	
	
}