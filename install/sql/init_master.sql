-- Inital Tableset for %PROJECTNAME% to work

SET FOREIGN_KEY_CHECKS=0;


--  Drop Tables, Stored Procedures and Views 

DROP TABLE IF EXISTS %TABLE_PREFIX_MASTER%account;
DROP TABLE IF EXISTS %TABLE_PREFIX_MASTER%counter;
DROP TABLE IF EXISTS %TABLE_PREFIX_MASTER%eventtyp;
DROP TABLE IF EXISTS %TABLE_PREFIX_MASTER%searchengines;
DROP TABLE IF EXISTS %TABLE_PREFIX_MASTER%stat_pages;


CREATE TABLE %TABLE_PREFIX_MASTER%account
(
	accountId SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID of the account',
	PRIMARY KEY (accountId)
)  COMMENT='%PROJECTNAME% - Account table';


CREATE TABLE %TABLE_PREFIX_MASTER%counter
(
	counterId MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID of the Counter',
	accountId SMALLINT UNSIGNED NOT NULL COMMENT 'ID of the account that ownes that counter',
	counterType ENUM('KU') NOT NULL COMMENT 'Type of the account',
	domainregex VARCHAR(250) NOT NULL COMMENT 'Regex that catches all domains that should be tracked',
	domainregexSql VARCHAR(250) NOT NULL COMMENT 'Sameregex as domainregex but prepared for use with MySQL',
	PRIMARY KEY (counterId),
	KEY (accountId)
)  COMMENT='%PROJECTNAME% - Countertable';


CREATE TABLE %TABLE_PREFIX_MASTER%eventtype
(
	eventtypeId SMALLINT UNSIGNED NOT NULL COMMENT 'ID of the eventtype',
	description VARCHAR(250) NOT NULL COMMENT 'Description of the eventtype',
	flag VARCHAR(50) NOT NULL COMMENT 'Flag thats transmitted with the event to identify the eventtype',
	PRIMARY KEY (eventtypeId),
	UNIQUE UQ_cp_eventtyp_eventtypId(eventtypeId),
	UNIQUE UQ_cp_eventtyp_flag(flag)
)  COMMENT='%PROJECTNAME% - Eventtypes';

-- @todo: correct handing and definition of searchengines
CREATE TABLE %TABLE_PREFIX_MASTER%searchengines
(
	searchengineId MEDIUMINT UNSIGNED NOT NULL COMMENT 'ID of the searchengine',
	description VARCHAR(70) NOT NULL COMMENT 'Description of the searchengine',
	domain VARCHAR(250) NOT NULL COMMENT 'Domain der Suchmaschniene',
	urlregex VARCHAR(250) NOT NULL COMMENT 'Regularexpression der URL',
	searchregex VARCHAR(250) NOT NULL COMMENT '',
	PRIMARY KEY (searchengineId)
)  COMMENT='%PROJECTNAME% - Definition of searchengines';

CREATE TABLE %TABLE_PREFIX_MASTER%stat_pages
(
	pageId BIGINT UNSIGNED NOT NULL COMMENT 'Id of the page for which the stats should be created',
	counterId SMALLINT UNSIGNED NOT NULL COMMENT 'ID of the counter where that rule occures',
	PRIMARY KEY (pageId, counterId)
)  COMMENT='%PROJECTNAME% - Defines for which pages detailed stat will be created';

SET FOREIGN_KEY_CHECKS=1;
