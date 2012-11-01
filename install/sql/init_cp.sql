-- Inital Tableset for %PROJECTNAME% to work

SET FOREIGN_KEY_CHECKS=0;


--  Drop Tables

DROP TABLE IF EXISTS %TABLE_PREFIX_CP%event;
DROP TABLE IF EXISTS %TABLE_PREFIX_CP%page;
DROP TABLE IF EXISTS %TABLE_PREFIX_CP%pageview;
DROP TABLE IF EXISTS %TABLE_PREFIX_CP%pageview_archiv;
DROP TABLE IF EXISTS %TABLE_PREFIX_CP%uc;

--  Create Tables 
CREATE TABLE %TABLE_PREFIX_CP%event
(
	uc CHAR(32) NOT NULL COMMENT 'Uniqe Client that raised the event',
	counterId MEDIUMINT UNSIGNED NOT NULL COMMENT 'ID of the Counter',
	eventtypeId SMALLINT UNSIGNED NOT NULL COMMENT 'Type of the Event',
	pageId BIGINT UNSIGNED NOT NULL COMMENT 'ID of the Page where the event raised',
	ctimestamp TIMESTAMP DEFAULT '0000-00-00 00:00:00' COMMENT 'Datetime of request at clientside',
	stimestamp TIMESTAMP DEFAULT '0000-00-00 00:00:00' COMMENT 'Datetime of request at serverside',
	visitsession VARCHAR(250) NOT NULL COMMENT 'Session of the visit (this cookie will be deleted with browserclose)',
	KEY (eventtypeId),
	KEY (uc),
	KEY (counterId)
)  COMMENT='%PROJECTNAME% - Tracked Events';


CREATE TABLE %TABLE_PREFIX_CP%page
(
	pageId BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID of tracked page',
	domain VARCHAR(255) NOT NULL COMMENT 'Domain of the page',
	url VARCHAR(255) COMMENT 'url of the Page',
	PRIMARY KEY (pageId)
)  COMMENT='%PROJECTNAME% - Requested pages';


CREATE TABLE %TABLE_PREFIX_CP%pageview
(
	uc CHAR(32) NOT NULL COMMENT 'Uniqe Client who did that request',
	pageviewId BIGINT UNSIGNED NOT NULL COMMENT 'ID will increment per uc',
	pageId BIGINT UNSIGNED NOT NULL COMMENT 'Id of the Page that was requested',
	counterId MEDIUMINT UNSIGNED NOT NULL COMMENT 'ID des Counters mit dem der Zugriff getracked wurde',
	ctimestamp TIMESTAMP DEFAULT '0000-00-00 00:00:00' COMMENT 'Datetime of request at clientside',
	stimestamp TIMESTAMP DEFAULT '0000-00-00 00:00:00' COMMENT 'Datetime of request at serverside',
	referer VARCHAR(255) COMMENT 'Referer of the Pagerequest',
	viewport VARCHAR(15) COMMENT 'viewport, viewed format',
	visitsession VARCHAR(250) NOT NULL COMMENT 'Session of the visit (this cookie will be deleted with browserclose)',
	PRIMARY KEY (uc, pageviewId),
	KEY (ctimestamp),
	KEY (uc),
	KEY (counterId)
)  COMMENT='%PROJECTNAME% - Pageimpressions';


CREATE TABLE %TABLE_PREFIX_CP%pageview_archiv
(
	uc CHAR(32) NOT NULL COMMENT 'Uniqe Client who did that request',
	pageviewId BIGINT UNSIGNED NOT NULL COMMENT 'ID will increment per uc',
	pageId BIGINT UNSIGNED NOT NULL COMMENT 'Id of the Page that was requested',
	counterId MEDIUMINT UNSIGNED NOT NULL COMMENT 'ID des Counters mit dem der Zugriff getracked wurde',
	ctimestamp TIMESTAMP DEFAULT '0000-00-00 00:00:00' COMMENT 'Datetime of request at clientside',
	stimestamp TIMESTAMP DEFAULT '0000-00-00 00:00:00' COMMENT 'Datetime of request at serverside',
	referer VARCHAR(255) COMMENT 'Referer of the Pagerequest',
	viewport VARCHAR(15) COMMENT 'viewport, viewed format',
	visitsession VARCHAR(250) NOT NULL COMMENT 'Session of the visit (this cookie will be deleted with browserclose)'
) ENGINE = ARCHIVE COMMENT='%PROJECTNAME% - Archivetable of Pageimpressions';


CREATE TABLE %TABLE_PREFIX_CP%uc
(
	uc CHAR(32) NOT NULL COMMENT 'Unique client identifier (md5 from session and useragent)',
	ip VARCHAR(15) NOT NULL COMMENT 'IP of the uniqe client',
	useragent VARCHAR(200) NOT NULL COMMENT 'Useragent of the UC',
	session VARCHAR(250) NOT NULL COMMENT 'Session thats transmitted from UC',
	screenresolution VARCHAR(15) COMMENT 'Screen resolution',
	PRIMARY KEY (uc),
	UNIQUE INDEX ip_useragent_session (session ASC, useragent ASC)
) ;

SET FOREIGN_KEY_CHECKS=1;

--  Create Triggers

-- Sets the Correct PageviewId depending on uc
delimiter //
CREATE TRIGGER `pageviewId` BEFORE INSERT ON `%TABLE_PREFIX_CP%pageview`
FOR EACH ROW BEGIN
declare v_id bigint unsigned default 0;
select max(pageviewId) + 1 into v_id from %TABLE_PREFIX_CP%pageview where uc = new.uc;
if(v_id IS NULL) THEN
set v_id = 1;
END IF;
set new.pageviewId = v_id;
END//

-- Empty Trigger for Eventstats

CREATE TRIGGER createStatsEvents AFTER INSERT ON %TABLE_PREFIX_CP%event
FOR EACH ROW BEGIN
END//

-- Empty Trigger for Pageviewstats
DROP TRIGGER IF EXISTS `createStatsPageview`//
CREATE TRIGGER `createStatsPageview` AFTER INSERT ON `cp_pageview`
 FOR EACH ROW BEGIN
END
//

delimiter ;