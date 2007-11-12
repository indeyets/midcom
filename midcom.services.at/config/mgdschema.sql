CREATE TABLE midcom_services_at_entry_db (
  id int(11) NOT NULL auto_increment,
  status int(11) NOT NULL default '0',
  start int(11) NOT NULL default '0',
  component VARCHAR(255) NOT NULL default '',
  method VARCHAR(255) NOT NULL default '',
  argumentsstore text NOT NULL default '',
#Required metadata
  guid varchar(80) NOT NULL default '',
  created datetime NOT NULL default '0000-00-00 00:00:00',
  creator int(11) NOT NULL default '0',
  revisor int(11) NOT NULL default '0',
  revised datetime NOT NULL default '0000-00-00 00:00:00',
  revision int(11) NOT NULL default '0',
  sitegroup int(11) NOT NULL default '0',
  PRIMARY KEY (id),
  KEY midcom_services_at_entry_db_sitegroup_idx(sitegroup),
  KEY midcom_services_at_entry_db_start_idx(start)
);
ALTER TABLE midcom_services_at_entry_db ADD COLUMN host int(11) NOT NULL default '0';
