--
-- sigplus Image Gallery Plus plug-in for Joomla
-- Copyright (C) 2009-2017 Levente Hunyadi. All rights reserved.
-- Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
-- http://hunyadi.info.hu/sigplus
--

DROP TABLE IF EXISTS [#__sigplus_data];
DROP TABLE IF EXISTS [#__sigplus_imageview];
DROP TABLE IF EXISTS [#__sigplus_caption];
DROP TABLE IF EXISTS [#__sigplus_image];
DROP TABLE IF EXISTS [#__sigplus_view];
DROP TABLE IF EXISTS [#__sigplus_hierarchy];
DROP TABLE IF EXISTS [#__sigplus_foldercaption];
DROP TABLE IF EXISTS [#__sigplus_folder];
DROP TABLE IF EXISTS [#__sigplus_property];
DROP TABLE IF EXISTS [#__sigplus_country];
DROP TABLE IF EXISTS [#__sigplus_language];

CREATE TABLE [#__sigplus_language] (
	[langid] smallint NOT NULL IDENTITY,
	-- language ISO code such as hu or en
	[lang] char(2) NOT NULL,
	PRIMARY KEY ([langid]),
	UNIQUE ([lang])
);

CREATE TABLE [#__sigplus_country] (
	[countryid] smallint NOT NULL IDENTITY,
	-- country ISO code such as HU or US
	[country] char(2) NOT NULL,
	PRIMARY KEY ([countryid]),
	UNIQUE ([country])
);

--
-- Metadata property names.
--
CREATE TABLE [#__sigplus_property] (
	[propertyid] int NOT NULL IDENTITY,
	[propertyname] varchar(255) NOT NULL,
	PRIMARY KEY ([propertyid]),
	UNIQUE ([propertyname])
);

--
-- Image gallery folders.
--
CREATE TABLE [#__sigplus_folder] (
	[folderid] int NOT NULL IDENTITY,
	-- relative path w.r.t. Joomla root, absolute path, or URL
	[folderurl] varchar(767) NOT NULL,
	-- last modified time for folder
	[foldertime] datetime,
	-- HTTP ETag
	[entitytag] varchar(255),
	PRIMARY KEY ([folderid]),
	UNIQUE ([folderurl])
);

--
-- Folder caption filters
--
CREATE TABLE [#__sigplus_foldercaption] (
	[folderid] int NOT NULL,
	-- pattern to match labels against
	[pattern] varchar(128) NOT NULL,
	-- language associated with caption filter
	[langid] smallint NOT NULL,
	-- country associated with caption filter
	[countryid] smallint NOT NULL,
	-- pattern priority
	[priority] smallint NOT NULL,
	-- title for images that match pattern in folder as an HTML string
	[title] nvarchar(max),
	-- summary text for images that match pattern in folder as an HTML string
	[summary] nvarchar(max),
	PRIMARY KEY ([folderid],[pattern],[langid],[countryid]),
	CONSTRAINT [#__FK_sigplus_foldercaption_language] FOREIGN KEY ([langid]) REFERENCES [#__sigplus_language]([langid]) ON DELETE CASCADE,
	CONSTRAINT [#__FK_sigplus_foldercaption_country] FOREIGN KEY ([countryid]) REFERENCES [#__sigplus_country]([countryid]) ON DELETE CASCADE,
	CONSTRAINT [#__FK_sigplus_foldercaption_folder] FOREIGN KEY ([folderid]) REFERENCES [#__sigplus_folder]([folderid]) ON DELETE CASCADE,
	INDEX [#__IX_sigplus_foldercaption_priority] ([priority])
);

--
-- Ancestor-descendant relationships for image gallery folders.
--
CREATE TABLE [#__sigplus_hierarchy] (
	[ancestorid] int NOT NULL,
	[descendantid] int NOT NULL,
	[depthnum] smallint NOT NULL,
	PRIMARY KEY ([ancestorid],[descendantid]),
	CONSTRAINT [#__FK_sigplus_hierarchy_ancestor] FOREIGN KEY ([ancestorid]) REFERENCES [#__sigplus_folder]([folderid]) ON DELETE CASCADE,
	CONSTRAINT [#__FK_sigplus_hierarchy_descendant] FOREIGN KEY ([descendantid]) REFERENCES [#__sigplus_folder]([folderid]),  -- ON DELETE CASCADE
	INDEX [#__IX_sigplus_hierarchy_depthnum] ([depthnum])
);

--
-- Folder views.
--
CREATE TABLE [#__sigplus_view] (
	[viewid] int NOT NULL IDENTITY,
	-- unique value computed from preview width, height, cropping and watermarking settings
	[hash] binary(16) NOT NULL,
	-- folder identifier
	[folderid] int NOT NULL,
	-- preview width for images in gallery
	[preview_width] smallint NOT NULL,
	-- preview height for images in gallery
	[preview_height] smallint NOT NULL,
	-- cropping mode for images in gallery
	[preview_crop] bit NOT NULL,
	-- HTTP ETag
	[entitytag] varchar(255),
	PRIMARY KEY ([viewid]),
	UNIQUE ([hash]),
	CONSTRAINT [#__FK_sigplus_view_folder] FOREIGN KEY ([folderid]) REFERENCES [#__sigplus_folder]([folderid]) ON DELETE CASCADE
);

--
-- Image data (excluding metadata).
--
CREATE TABLE [#__sigplus_image] (
	[imageid] int NOT NULL IDENTITY,
	[folderid] int,
	[fileurl] varchar(767) NOT NULL,
	[filename] varchar(255) NOT NULL,
	[filetime] datetime,
	[filesize] int NOT NULL,
	[width] smallint NOT NULL,
	[height] smallint NOT NULL,
	PRIMARY KEY ([imageid]),
	UNIQUE ([fileurl]),
	CONSTRAINT [#__FK_sigplus_image_folder] FOREIGN KEY ([folderid]) REFERENCES [#__sigplus_folder]([folderid]) ON DELETE CASCADE
);

--
-- Image captions.
--

CREATE TABLE [#__sigplus_caption] (
	[imageid] int NOT NULL,
	[langid] smallint NOT NULL,
	[countryid] smallint NOT NULL,
	[ordnum] smallint,
	-- image title HTML string
	[title] nvarchar(max),
	-- image description HTML string
	[summary] nvarchar(max),
	[last_modified] datetime NULL DEFAULT GETDATE(),
	PRIMARY KEY ([imageid],[langid],[countryid]),
	INDEX [#__IX_sigplus_caption_ordnum] ([ordnum]),
	CONSTRAINT [#__FK_sigplus_caption_language] FOREIGN KEY ([langid]) REFERENCES [#__sigplus_language]([langid]) ON DELETE CASCADE,
	CONSTRAINT [#__FK_sigplus_caption_country] FOREIGN KEY ([countryid]) REFERENCES [#__sigplus_country]([countryid]) ON DELETE CASCADE,
	CONSTRAINT [#__FK_sigplus_caption_image] FOREIGN KEY ([imageid]) REFERENCES [#__sigplus_image]([imageid]) ON DELETE CASCADE
);

--
-- Image views that associate images with preview sizes.
--
CREATE TABLE [#__sigplus_imageview] (
	[imageid] int NOT NULL,
	[viewid] int NOT NULL,
	[thumb_fileurl] varchar(767),
	[thumb_filetime] datetime,
	[thumb_width] smallint NOT NULL,
	[thumb_height] smallint NOT NULL,
	[preview_fileurl] varchar(767),
	[preview_filetime] datetime,
	[preview_width] smallint NOT NULL,
	[preview_height] smallint NOT NULL,
	[retina_fileurl] varchar(767),
	[retina_filetime] datetime,
	[retina_width] smallint NOT NULL,
	[retina_height] smallint NOT NULL,
	[watermark_fileurl] varchar(767),
	[watermark_filetime] datetime,
	PRIMARY KEY ([imageid],[viewid]),
	CONSTRAINT [#__FK_sigplus_imageview_image] FOREIGN KEY ([imageid]) REFERENCES [#__sigplus_image]([imageid]) ON DELETE CASCADE,
	CONSTRAINT [#__FK_sigplus_imageview_view] FOREIGN KEY ([viewid]) REFERENCES [#__sigplus_view]([viewid]) -- ON DELETE CASCADE
);

--
-- Image metadata.
--
CREATE TABLE [#__sigplus_data] (
	[imageid] int NOT NULL,
	[propertyid] int NOT NULL,
	-- metadata property value as an HTML string
	[textvalue] nvarchar(max),
	PRIMARY KEY ([imageid], [propertyid]),
	CONSTRAINT [#__FK_sigplus_data_image] FOREIGN KEY ([imageid]) REFERENCES [#__sigplus_image]([imageid]) ON DELETE CASCADE,
	CONSTRAINT [#__FK_sigplus_data_property] FOREIGN KEY ([propertyid]) REFERENCES [#__sigplus_property]([propertyid]) ON DELETE CASCADE
);
