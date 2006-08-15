CREATE TABLE org_openpsa_task (
  id int(11) NOT NULL auto_increment,
  up int(11) NOT NULL default '0',
  title varchar(255) NOT NULL default '',
  created datetime NOT NULL default '0000-00-00 00:00:00',
  creator int(11) NOT NULL default '0',
  revisor int(11) NOT NULL default '0',
  revised datetime NOT NULL default '0000-00-00 00:00:00',
  author int(11) NOT NULL default '0',
  revision int(11) NOT NULL default '0',
  owner int(11) NOT NULL default '0',
  customer int(11) NOT NULL default '0',
  affectsSaldo int(11) NOT NULL default '0',
  started int(11) NOT NULL default '0',
  finished int(11) NOT NULL default '0',
  start int(11) NOT NULL default '0',
  end int(11) NOT NULL default '0',
  description text NOT NULL default '',
  projectCode varchar(255) NOT NULL default '',
  pricePlugin varchar(255) NOT NULL default '',
  costPlugin varchar(255) NOT NULL default '',
  status int(11) NOT NULL default '0',
  manager int(11) NOT NULL default '0',
  expensesInvoiceableDefault int(11) NOT NULL default '0',
  priceBase float NOT NULL default '0',
  costBase float NOT NULL default '0',
  maxPrice float NOT NULL default '0',
  maxCost float NOT NULL default '0',
  hourCache float NOT NULL default '0',
  costCache float NOT NULL default '0',
  priceCache float NOT NULL default '0',
  plannedHours float NOT NULL default '0',

  orgOpenpsaObtype int(11) NOT NULL default '0',
  orgOpenpsaWgtype int(11) NOT NULL default '0',
  sitegroup int(11) NOT NULL default '0',
  PRIMARY KEY (id),
  KEY ootask_sitegroup_idx(sitegroup)
);
ALTER TABLE org_openpsa_task ADD COLUMN orgOpenpsaAccesstype int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_task ADD COLUMN orgOpenpsaOwnerWg VARCHAR(255);
ALTER TABLE org_openpsa_task ADD COLUMN newsTopic int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_task ADD COLUMN forumTopic int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_task ADD COLUMN acceptanceType int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_task add column guid varchar(80) NOT NULL default '';
ALTER TABLE org_openpsa_task ADD COLUMN hoursInvoiceableDefault int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_task ADD COLUMN agreement int(11) NOT NULL default '0';

CREATE TABLE org_openpsa_task_resource (
  id int(11) NOT NULL auto_increment,
  task int(11) NOT NULL default '0',
  person int(11) NOT NULL default '0',
  orgOpenpsaObtype int(11) NOT NULL default '0',
  orgOpenpsaWgtype int(11) NOT NULL default '0',
  sitegroup int(11) NOT NULL default '0',
  PRIMARY KEY (id),
  KEY ootaskresource_sitegroup_idx(sitegroup)
);
alter table org_openpsa_task_resource add column guid varchar(80) NOT NULL default '';

CREATE TABLE org_openpsa_expense (
  id int(11) NOT NULL auto_increment,
  created datetime NOT NULL default '0000-00-00 00:00:00',
  creator int(11) NOT NULL default '0',
  revisor int(11) NOT NULL default '0',
  revised datetime NOT NULL default '0000-00-00 00:00:00',

  task int(11) NOT NULL default '0',
  person int(11) NOT NULL default '0',
  pricePlugin varchar(255) NOT NULL default '',
  costPlugin varchar(255) NOT NULL default '',
  priceBase float NOT NULL default '0',
  costBase float NOT NULL default '0',
  costCache float NOT NULL default '0',
  priceCache float NOT NULL default '0',
  invoiceable int(11) NOT NULL default '0',
  reportType varchar(255) NOT NULL default '',
  description text NOT NULL default '',
  approved datetime NOT NULL default '0000-00-00 00:00:00',
  approver int(11) NOT NULL default '0',
  invoiced datetime NOT NULL default '0000-00-00 00:00:00',
  invoicer int(11) NOT NULL default '0',

  orgOpenpsaObtype int(11) NOT NULL default '0',
  orgOpenpsaWgtype int(11) NOT NULL default '0',
  sitegroup int(11) NOT NULL default '0',
  PRIMARY KEY (id),
  KEY ooexpense_sitegroup_idx(sitegroup)
);
ALTER TABLE org_openpsa_expense ADD COLUMN date int(11) NOT NULL default '0';
alter table org_openpsa_expense add column guid varchar(80) NOT NULL default '';


CREATE TABLE org_openpsa_hour_report (
  id int(11) NOT NULL auto_increment,
  created datetime NOT NULL default '0000-00-00 00:00:00',
  creator int(11) NOT NULL default '0',
  revisor int(11) NOT NULL default '0',
  revised datetime NOT NULL default '0000-00-00 00:00:00',

  task int(11) NOT NULL default '0',
  person int(11) NOT NULL default '0',
  pricePlugin varchar(255) NOT NULL default '',
  costPlugin varchar(255) NOT NULL default '',
  priceBase float NOT NULL default '0',
  costBase float NOT NULL default '0',
  costCache float NOT NULL default '0',
  priceCache float NOT NULL default '0',
  invoiceable int(11) NOT NULL default '0',
  reportType varchar(255) NOT NULL default '',
  description text NOT NULL default '',
  approved datetime NOT NULL default '0000-00-00 00:00:00',
  approver int(11) NOT NULL default '0',
  invoiced datetime NOT NULL default '0000-00-00 00:00:00',
  invoicer int(11) NOT NULL default '0',
  hours float NOT NULL default '0',

  orgOpenpsaObtype int(11) NOT NULL default '0',
  orgOpenpsaWgtype int(11) NOT NULL default '0',
  sitegroup int(11) NOT NULL default '0',
  PRIMARY KEY (id),
  KEY oohourreport_sitegroup_idx(sitegroup)
);
ALTER TABLE org_openpsa_hour_report ADD COLUMN date int(11) NOT NULL default '0';
alter table org_openpsa_hour_report add column guid varchar(80) NOT NULL default '';

CREATE TABLE org_openpsa_deliverable (
  id int(11) NOT NULL auto_increment,
  task int(11) NOT NULL default '0',
  orgOpenpsaObtype int(11) NOT NULL default '0',
  plugin varchar(255) NOT NULL default '',
  deliverable varchar(255) NOT NULL default '',
  sitegroup int(11) NOT NULL default '0',
  created datetime NOT NULL default '0000-00-00 00:00:00',
  creator int(11) NOT NULL default '0',
  revisor int(11) NOT NULL default '0',
  revised datetime NOT NULL default '0000-00-00 00:00:00',
  revision int(11) NOT NULL default '0',
  PRIMARY KEY (id),
  KEY oodeliverable_sitegroup_idx(sitegroup)
);
alter table org_openpsa_deliverable add column guid varchar(80) NOT NULL default '';

CREATE TABLE org_openpsa_task_status (
  id int(11) NOT NULL auto_increment,
  type int(11) NOT NULL default '0',
  task int(11) NOT NULL default '0',
  targetPerson int(11) NOT NULL default '0',
  sitegroup int(11) NOT NULL default '0',
  created datetime NOT NULL default '0000-00-00 00:00:00',
  creator int(11) NOT NULL default '0',
  revisor int(11) NOT NULL default '0',
  revised datetime NOT NULL default '0000-00-00 00:00:00',
  revision int(11) NOT NULL default '0',
  PRIMARY KEY (id),
  KEY oodeliverable_sitegroup_idx(sitegroup)
);
ALTER TABLE org_openpsa_task_status ADD COLUMN timestamp int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_task_status ADD COLUMN comment text NOT NULL default '';
alter table org_openpsa_task_status add column guid varchar(80) NOT NULL default '';


