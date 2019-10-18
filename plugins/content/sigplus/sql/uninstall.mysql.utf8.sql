--
-- sigplus Image Gallery Plus plug-in for Joomla
-- Copyright (C) 2009-2017 Levente Hunyadi. All rights reserved.
-- Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
-- http://hunyadi.info.hu/sigplus
--

SET @SIGPLUS_OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS = 0;
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
SET FOREIGN_KEY_CHECKS = @SIGPLUS_OLD_FOREIGN_KEY_CHECKS;
