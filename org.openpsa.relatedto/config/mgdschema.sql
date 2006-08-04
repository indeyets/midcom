#SalesProject
CREATE TABLE org_openpsa_relatedto (
  fromComponent varchar(255) NOT NULL default '',
  fromClass varchar(255) NOT NULL default '',
  fromGuid varchar(80) NOT NULL default '',
  fromExtra varchar(255) NOT NULL default '',
  toComponent varchar(255) NOT NULL default '',
  toClass varchar(255) NOT NULL default '',
  toGuid varchar(80) NOT NULL default '',
  toExtra varchar(255) NOT NULL default '',
  status int(11) NOT NULL default '0',
#
  id int(11) NOT NULL auto_increment,
  sitegroup int(11) NOT NULL default '0',
  guid varchar(80) NOT NULL default '',
  PRIMARY KEY (id),
  KEY org_openpsa_relatedto_fromComponent_idx(fromComponent),
  KEY org_openpsa_relatedto_fromClass_idx(fromClass),
  KEY org_openpsa_relatedto_fromGuid_idx(fromGuid),
  KEY org_openpsa_relatedto_toComponent_idx(toComponent),
  KEY org_openpsa_relatedto_toClass_idx(toClass),
  KEY org_openpsa_relatedto_toGuid_idx(toGuid),
  KEY org_openpsa_relatedto_sitegroup_idx(sitegroup)
);
#metadata fields
ALTER TABLE org_openpsa_relatedto ADD COLUMN created datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_relatedto ADD COLUMN creator int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_relatedto ADD COLUMN revisor int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_relatedto ADD COLUMN revised datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_relatedto ADD COLUMN revision int(11) NOT NULL default '0';

