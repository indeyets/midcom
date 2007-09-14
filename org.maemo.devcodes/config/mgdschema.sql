CREATE TABLE org_maemo_devcodes_device (
  id int(11) NOT NULL auto_increment,
  sitegroup int(11) NOT NULL default 0,
  node int(11) NOT NULL default 0,
  codename varchar(255) NOT NULL default '',
  title varchar(255) NOT NULL default '',
  notes longtext NOT NULL default '',
  start datetime NOT NULL default '0000-00-00 00:00:00',
  end datetime NOT NULL default '0000-00-00 00:00:00',
# other fields/indexes as ALTER TABLE statements
  PRIMARY KEY  (id),
  KEY org_maemo_devcodes_device_sitegroup_idx (sitegroup),
  KEY org_maemo_devcodes_device_node_idx (node),
  KEY org_maemo_devcodes_device_codename_idx (codename),
  KEY org_maemo_devcodes_device_start_idx (start),
  KEY org_maemo_devcodes_device_end_idx (end)
);
CREATE TABLE org_maemo_devcodes_code (
  id int(11) NOT NULL auto_increment,
  sitegroup int(11) NOT NULL default 0,
  device int(11) NOT NULL default 0,
  code varchar(255) NOT NULL default '',
  area varchar(255) NOT NULL default '',
  recipient int(11) NOT NULL default 0,
# other fields/indexes as ALTER TABLE statements
  PRIMARY KEY  (id),
  KEY org_maemo_devcodes_code_sitegroup_idx (sitegroup),
  KEY org_maemo_devcodes_code_device_idx (device),
  KEY org_maemo_devcodes_code_code_idx (code),
  KEY org_maemo_devcodes_code_area_idx (area),
  KEY org_maemo_devcodes_code_recipient_idx (recipient)
);
CREATE TABLE org_maemo_devcodes_application (
  id int(11) NOT NULL auto_increment,
  sitegroup int(11) NOT NULL default 0,
  device int(11) NOT NULL default 0,
  applicant int(11) NOT NULL default 0,
  summary varchar(255) NOT NULL default '',
  notes longtext NOT NULL default '',
# other fields/indexes as ALTER TABLE statements
  PRIMARY KEY  (id),
  KEY org_maemo_devcodes_application_sitegroup_idx (sitegroup),
  KEY org_maemo_devcodes_application_device_idx (device),
  KEY org_maemo_devcodes_application_applicant_idx (applicant)
);
ALTER TABLE org_maemo_devcodes_application ADD COLUMN state int(11) NOT NULL default 0;
ALTER TABLE org_maemo_devcodes_application ADD COLUMN code int(11) NOT NULL default 0;

