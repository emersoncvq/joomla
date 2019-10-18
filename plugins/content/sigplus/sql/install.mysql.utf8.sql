--
-- sigplus Image Gallery Plus plug-in for Joomla
-- Copyright (C) 2009-2017 Levente Hunyadi. All rights reserved.
-- Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
-- http://hunyadi.info.hu/sigplus
--

DROP TABLE IF EXISTS
	`#__sigplus_data`,
	`#__sigplus_imageview`,
	`#__sigplus_caption`,
	`#__sigplus_image`,
	`#__sigplus_view`,
	`#__sigplus_hierarchy`,
	`#__sigplus_foldercaption`,
	`#__sigplus_folder`,
	`#__sigplus_property`,
	`#__sigplus_country`,
	`#__sigplus_language`;

CREATE TABLE `#__sigplus_language` (
	`langid` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	-- language ISO code such as hu or en
	`lang` CHAR(2) NOT NULL,
	PRIMARY KEY (`langid`),
	UNIQUE (`lang`)
) AUTO_INCREMENT=1, DEFAULT CHARSET=ascii, ENGINE=InnoDB;

CREATE TABLE `#__sigplus_country` (
	`countryid` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	-- country ISO code such as HU or US
	`country` CHAR(2) NOT NULL,
	PRIMARY KEY (`countryid`),
	UNIQUE (`country`)
) AUTO_INCREMENT=1, DEFAULT CHARSET=ascii, ENGINE=InnoDB;

--
-- Metadata property names.
--
CREATE TABLE `#__sigplus_property` (
	`propertyid` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`propertyname` VARCHAR(255) CHARACTER SET ascii NOT NULL,
	PRIMARY KEY (`propertyid`),
	UNIQUE (`propertyname`)
) AUTO_INCREMENT=1, DEFAULT CHARSET=utf8, ENGINE=InnoDB;

--
-- Image gallery folders.
--
CREATE TABLE `#__sigplus_folder` (
	`folderid` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	-- relative path w.r.t. Joomla root, absolute path, or URL
	`folderurl` VARCHAR(767) CHARACTER SET binary NOT NULL,
	-- last modified time for folder
	`foldertime` DATETIME,
	-- HTTP ETag
	`entitytag` VARCHAR(255) CHARACTER SET ascii,
	PRIMARY KEY (`folderid`),
	UNIQUE (`folderurl`)
) AUTO_INCREMENT=1, DEFAULT CHARSET=utf8, ENGINE=InnoDB;

--
-- Folder caption filters
--
CREATE TABLE `#__sigplus_foldercaption` (
	`folderid` INT UNSIGNED NOT NULL,
	-- pattern to match labels against
	`pattern` VARCHAR(128) NOT NULL,
	-- language associated with caption filter
	`langid` SMALLINT UNSIGNED NOT NULL,
	-- country associated with caption filter
	`countryid` SMALLINT UNSIGNED NOT NULL,
	-- pattern priority
	`priority` SMALLINT UNSIGNED NOT NULL,
	-- title for images that match pattern in folder as an HTML string
	`title` TEXT,
	-- summary text for images that match pattern in folder as an HTML string
	`summary` TEXT,
	PRIMARY KEY (`folderid`,`pattern`,`langid`,`countryid`),
	CONSTRAINT `#__FK_sigplus_foldercaption_language` FOREIGN KEY (`langid`) REFERENCES `#__sigplus_language`(`langid`) ON DELETE CASCADE,
	CONSTRAINT `#__FK_sigplus_foldercaption_country` FOREIGN KEY (`countryid`) REFERENCES `#__sigplus_country`(`countryid`) ON DELETE CASCADE,
	CONSTRAINT `#__FK_sigplus_foldercaption_folder` FOREIGN KEY (`folderid`) REFERENCES `#__sigplus_folder`(`folderid`) ON DELETE CASCADE,
	INDEX `#__IX_sigplus_foldercaption_priority` (`priority`)
) DEFAULT CHARSET=utf8, ENGINE=InnoDB;

--
-- Ancestor-descendant relationships for image gallery folders.
--
CREATE TABLE `#__sigplus_hierarchy` (
	`ancestorid` INT UNSIGNED NOT NULL,
	`descendantid` INT UNSIGNED NOT NULL,
	`depthnum` SMALLINT UNSIGNED NOT NULL,
	PRIMARY KEY (`ancestorid`,`descendantid`),
	CONSTRAINT `#__FK_sigplus_hierarchy_ancestor` FOREIGN KEY (`ancestorid`) REFERENCES `#__sigplus_folder`(`folderid`) ON DELETE CASCADE,
	CONSTRAINT `#__FK_sigplus_hierarchy_descendant` FOREIGN KEY (`descendantid`) REFERENCES `#__sigplus_folder`(`folderid`) ON DELETE CASCADE,
	INDEX `#__IX_sigplus_hierarchy_depthnum` (`depthnum`)
) DEFAULT CHARSET=utf8, ENGINE=InnoDB;

--
-- Folder views.
--
CREATE TABLE `#__sigplus_view` (
	`viewid` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	-- unique value computed from preview width, height, cropping and watermarking settings
	`hash` BINARY(16) NOT NULL,
	-- folder identifier
	`folderid` INT UNSIGNED NOT NULL,
	-- preview width for images in gallery
	`preview_width` SMALLINT UNSIGNED NOT NULL,
	-- preview height for images in gallery
	`preview_height` SMALLINT UNSIGNED NOT NULL,
	-- cropping mode for images in gallery
	`preview_crop` BOOLEAN NOT NULL,
	-- HTTP ETag
	`entitytag` VARCHAR(255) CHARACTER SET ascii,
	PRIMARY KEY (`viewid`),
	UNIQUE (`hash`),
	CONSTRAINT `#__FK_sigplus_view_folder` FOREIGN KEY (`folderid`) REFERENCES `#__sigplus_folder`(`folderid`) ON DELETE CASCADE
) AUTO_INCREMENT=1, DEFAULT CHARSET=utf8, ENGINE=InnoDB;

--
-- Image data (excluding metadata).
--
CREATE TABLE `#__sigplus_image` (
	`imageid` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`folderid` INT UNSIGNED,
	`fileurl` VARCHAR(767) CHARACTER SET binary NOT NULL,
	`filename` VARCHAR(255) NOT NULL,
	`filetime` DATETIME,
	`filesize` INT UNSIGNED NOT NULL,
	`width` SMALLINT UNSIGNED NOT NULL,
	`height` SMALLINT UNSIGNED NOT NULL,
	PRIMARY KEY (`imageid`),
	UNIQUE (`fileurl`),
	CONSTRAINT `#__FK_sigplus_image_folder` FOREIGN KEY (`folderid`) REFERENCES `#__sigplus_folder`(`folderid`) ON DELETE CASCADE
) AUTO_INCREMENT=1, DEFAULT CHARSET=utf8, ENGINE=InnoDB;

--
-- Image captions.
--

CREATE TABLE `#__sigplus_caption` (
	`imageid` INT UNSIGNED NOT NULL,
	`langid` SMALLINT UNSIGNED NOT NULL,
	`countryid` SMALLINT UNSIGNED NOT NULL,
	`ordnum` SMALLINT UNSIGNED,
	-- image title HTML string
	`title` TEXT,
	-- image description HTML string
	`summary` TEXT,
	`last_modified` TIMESTAMP,
	PRIMARY KEY (`imageid`,`langid`,`countryid`),
	INDEX `#__IX_sigplus_caption_ordnum` (`ordnum`),
	CONSTRAINT `#__FK_sigplus_caption_language` FOREIGN KEY (`langid`) REFERENCES `#__sigplus_language`(`langid`) ON DELETE CASCADE,
	CONSTRAINT `#__FK_sigplus_caption_country` FOREIGN KEY (`countryid`) REFERENCES `#__sigplus_country`(`countryid`) ON DELETE CASCADE,
	CONSTRAINT `#__FK_sigplus_caption_image` FOREIGN KEY (`imageid`) REFERENCES `#__sigplus_image`(`imageid`) ON DELETE CASCADE
) DEFAULT CHARSET=utf8, ENGINE=InnoDB;

--
-- Image views that associate images with preview sizes.
--
CREATE TABLE `#__sigplus_imageview` (
	`imageid` INT UNSIGNED NOT NULL,
	`viewid` INT UNSIGNED NOT NULL,
	`thumb_fileurl` VARCHAR(767) CHARACTER SET binary,
	`thumb_filetime` DATETIME,
	`thumb_width` SMALLINT UNSIGNED NOT NULL,
	`thumb_height` SMALLINT UNSIGNED NOT NULL,
	`preview_fileurl` VARCHAR(767) CHARACTER SET binary,
	`preview_filetime` DATETIME,
	`preview_width` SMALLINT UNSIGNED NOT NULL,
	`preview_height` SMALLINT UNSIGNED NOT NULL,
	`retina_fileurl` VARCHAR(767) CHARACTER SET binary,
	`retina_filetime` DATETIME,
	`retina_width` SMALLINT UNSIGNED NOT NULL,
	`retina_height` SMALLINT UNSIGNED NOT NULL,
	`watermark_fileurl` VARCHAR(767) CHARACTER SET binary,
	`watermark_filetime` DATETIME,
	PRIMARY KEY (`imageid`,`viewid`),
	CONSTRAINT `#__FK_sigplus_imageview_image` FOREIGN KEY (`imageid`) REFERENCES `#__sigplus_image`(`imageid`) ON DELETE CASCADE,
	CONSTRAINT `#__FK_sigplus_imageview_view` FOREIGN KEY (`viewid`) REFERENCES `#__sigplus_view`(`viewid`) ON DELETE CASCADE
) DEFAULT CHARSET=utf8, ENGINE=InnoDB;

--
-- Image metadata.
--
CREATE TABLE `#__sigplus_data` (
	`imageid` INT UNSIGNED NOT NULL,
	`propertyid` INT UNSIGNED NOT NULL,
	-- metadata property value as an HTML string
	`textvalue` TEXT,
	PRIMARY KEY (`imageid`, `propertyid`),
	CONSTRAINT `#__FK_sigplus_data_image` FOREIGN KEY (`imageid`) REFERENCES `#__sigplus_image`(`imageid`) ON DELETE CASCADE,
	CONSTRAINT `#__FK_sigplus_data_property` FOREIGN KEY (`propertyid`) REFERENCES `#__sigplus_property`(`propertyid`) ON DELETE CASCADE
) DEFAULT CHARSET=utf8, ENGINE=InnoDB;
