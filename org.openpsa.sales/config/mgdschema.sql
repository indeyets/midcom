#SalesProject
CREATE TABLE org_openpsa_salesproject (
  id int(11) NOT NULL auto_increment,
  up int(11) NOT NULL default '0',
  owner int(11) NOT NULL default '0',
  customer int(11) NOT NULL default '0',
  status int(11) NOT NULL default '0',
  description text NOT NULL default '',
  orgOpenpsaObtype int(11) NOT NULL default '0',
  sitegroup int(11) NOT NULL default '0',
  title varchar(255) NOT NULL default '',
  start int(11) NOT NULL default '0',
  end int(11) NOT NULL default '0',
  guid varchar(80) NOT NULL default '',
  PRIMARY KEY (id),
  KEY org_openpsa_salesproject_sitegroup_idx(sitegroup),
  KEY org_openpsa_salesproject_up_idx(up),
  KEY org_openpsa_salesproject_status_idx(status),
  KEY org_openpsa_salesproject_customer_idx(customer),
  KEY org_openpsa_salesproject_start_idx(start),
  KEY org_openpsa_salesproject_end_idx(end),
  KEY org_openpsa_salesproject_owner_idx(owner)
);
#metadata fields
ALTER TABLE org_openpsa_salesproject ADD COLUMN created datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_salesproject ADD COLUMN creator int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_salesproject ADD COLUMN revisor int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_salesproject ADD COLUMN revised datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_salesproject ADD COLUMN revision int(11) NOT NULL default '0';

#SalesProject Member
CREATE TABLE org_openpsa_salesproject_member (
  id int(11) NOT NULL auto_increment,
  salesproject int(11) NOT NULL default '0',
  person int(11) NOT NULL default '0',
  extra text NOT NULL default '',
  orgOpenpsaObtype int(11) NOT NULL default '0',
  sitegroup int(11) NOT NULL default '0',
  guid varchar(80) NOT NULL default '',
  PRIMARY KEY (id),
  KEY org_openpsa_salesproject_member_sitegroup_idx(sitegroup),
  KEY org_openpsa_salesproject_member_person_idx(person),
  KEY org_openpsa_salesproject_member_salesproject_idx(salesproject)
);
#metadata fields
ALTER TABLE org_openpsa_salesproject_member ADD COLUMN created datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_salesproject_member ADD COLUMN creator int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_salesproject_member ADD COLUMN revisor int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_salesproject_member ADD COLUMN revised datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_salesproject_member ADD COLUMN revision int(11) NOT NULL default '0';
