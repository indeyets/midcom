#Create tables for org.openpsa.documents
#Copy of article table sans some irrelevant data
CREATE TABLE org_openpsa_document (
  id int(11) NOT NULL auto_increment,
  nextVersion int(11) NOT NULL default '0',
  topic int(11) NOT NULL default '0',
  title varchar(255) NOT NULL default '',
  abstract longtext NOT NULL,
  content longtext NOT NULL,
  created datetime NOT NULL default '0000-00-00 00:00:00',
  creator int(11) NOT NULL default '0',
  author int(11) NOT NULL default '0',
  revisor int(11) NOT NULL default '0',
  revision int(11) NOT NULL default '0',
  approver int(11) NOT NULL default '0',
  revised datetime NOT NULL default '0000-00-00 00:00:00',
  approved datetime NOT NULL default '0000-00-00 00:00:00',
  score int(11) NOT NULL default '0',
  sitegroup int(11) NOT NULL default '0',
  PRIMARY KEY (id),
  KEY topic(topic,title(14)),
  KEY oodocument_sitegroup_idx(sitegroup)
);
#Additional stuff
ALTER TABLE org_openpsa_document ADD COLUMN docStatus INT(11) NOT NULL default '0';
ALTER TABLE org_openpsa_document ADD COLUMN keywords VARCHAR(255) NOT NULL default '';
#Used to determine object subtype (project vs task, etc)
ALTER TABLE org_openpsa_document ADD COLUMN orgOpenpsaObtype INT(11) NOT NULL default '0';
#will contain bitmask as integer
ALTER TABLE org_openpsa_document ADD COLUMN orgOpenpsaWgtype INT(11) NOT NULL default '0';
ALTER TABLE org_openpsa_document ADD COLUMN orgOpenpsaAccesstype int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_document ADD COLUMN orgOpenpsaOwnerWg VARCHAR(255) NOT NULL default '';
alter table org_openpsa_document add column guid varchar(80) NOT NULL default '';

