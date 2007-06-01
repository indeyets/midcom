CREATE TABLE org_openpsa_query (
  id int(11) NOT NULL auto_increment,
  created datetime NOT NULL default '0000-00-00 00:00:00',
  creator int(11) NOT NULL default '0',
  revisor int(11) NOT NULL default '0',
  revised datetime NOT NULL default '0000-00-00 00:00:00',
  start int(11) NOT NULL default '0',
  end int(11) NOT NULL default '0',
  title varchar(255) NOT NULL default '',
  style varchar(255) NOT NULL default '',
  component varchar(255) NOT NULL default '',
  relatedcomponent varchar(255) NOT NULL default '',
  mimetype varchar(255) NOT NULL default '',
  extension varchar(255) NOT NULL default '',
  orgOpenpsaObtype int(11) NOT NULL default '0',
  sitegroup int(11) NOT NULL default '0',
  PRIMARY KEY (id),
  KEY ooquery_sitegroup_idx(sitegroup)
);
alter table org_openpsa_query add column guid varchar(80) NOT NULL default '';
# 1.7 metadata
#
ALTER TABLE org_openpsa_query ADD COLUMN created datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_query ADD COLUMN creator int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_query ADD COLUMN revisor int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_query ADD COLUMN revised datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_query ADD COLUMN revision int(11) NOT NULL default '0';
