#
# NOTE: When adding new properties add them as ALTER TABLE, othewise upgrades become very manual process of comparing
# CREATE table statements to what is actually in each DB.
#
#Fix repligard table
alter table repligard modify realm varchar(255) NOT NULL default '';

#Create tables for org.openpsa.spammer
CREATE TABLE org_openpsa_campaign (
  id int(11) NOT NULL auto_increment,
  title varchar(255) NOT NULL default '',
  description text NOT NULL default '',
  archived int(11) NOT NULL default '0',
  orgOpenpsaObtype int(11) NOT NULL default '0',
  orgOpenpsaWgtype int(11) NOT NULL default '0',
  orgOpenpsaAccesstype int(11) NOT NULL default '0',
  orgOpenpsaOwnerWg VARCHAR(255) NOT NULL default '',
  sitegroup int(11) NOT NULL default '0',
  PRIMARY KEY (id),
  KEY oocmsg_sitegroup_idx(sitegroup)
);
alter table org_openpsa_campaign add column guid varchar(80) NOT NULL default '';
alter table org_openpsa_campaign add column rulesSerialized text NOT NULL default '';

CREATE TABLE org_openpsa_campaign_member (
  id int(11) NOT NULL auto_increment,
  campaign int(11) NOT NULL default '0',
  person int(11) NOT NULL default '0',
  orgOpenpsaObtype int(11) NOT NULL default '0',
  sitegroup int(11) NOT NULL default '0',
  PRIMARY KEY (id),
  KEY oocmember_sitegroup_idx(sitegroup)
);
alter table org_openpsa_campaign_member add column guid varchar(80) NOT NULL default '';
alter table org_openpsa_campaign_member add column suspended int(11) NOT NULL default '0';
create index org_openpsa_campaign_member_campaign_idx on org_openpsa_campaign_member(campaign);
create index org_openpsa_campaign_member_orgOpenpsaObtype_idx on org_openpsa_campaign_member(orgOpenpsaObtype);

CREATE TABLE org_openpsa_campaign_message (
  id int(11) NOT NULL auto_increment,
  campaign int(11) NOT NULL default '0',
  title varchar(255) NOT NULL default '',
  description text NOT NULL default '',
  sendStarted int(11) NOT NULL default '0',
  sendCompleted int(11) NOT NULL default '0',
  orgOpenpsaObtype int(11) NOT NULL default '0',
  orgOpenpsaAccesstype int(11) NOT NULL default '0',
  orgOpenpsaOwnerWg VARCHAR(255) NOT NULL default '',
  sitegroup int(11) NOT NULL default '0',
  PRIMARY KEY (id),
  KEY oocmsg_sitegroup_idx(sitegroup)
);
alter table org_openpsa_campaign_message add column guid varchar(80) NOT NULL default '';

CREATE TABLE org_openpsa_campaign_message_receipt (
  id int(11) NOT NULL auto_increment,
  message int(11) NOT NULL default '0',
  person int(11) NOT NULL default '0',
  timestamp int(11) NOT NULL default '0',
  orgOpenpsaObtype int(11) NOT NULL default '0',
  sitegroup int(11) NOT NULL default '0',
  PRIMARY KEY (id),
  KEY org_openpsa_campaign_message_receipt_sitegroup_idx(sitegroup)
);
alter table org_openpsa_campaign_message_receipt add column guid varchar(80) NOT NULL default '';
alter table org_openpsa_campaign_message_receipt add column token varchar(80) NOT NULL default '';
alter table org_openpsa_campaign_message_receipt add column bounced int(11) NOT NULL default '0';
create index org_openpsa_campaign_message_receipt_person_idx on org_openpsa_campaign_message_receipt(person);
create index org_openpsa_campaign_message_receipt_token_idx on org_openpsa_campaign_message_receipt(token);
create index org_openpsa_campaign_message_receipt_timestamp_idx on org_openpsa_campaign_message_receipt(timestamp);
create index org_openpsa_campaign_message_receipt_orgOpenpsaObtype_idx on org_openpsa_campaign_message_receipt(orgOpenpsaObtype);

CREATE TABLE org_openpsa_link_log (
  id int(11) NOT NULL auto_increment,
  person int(11) NOT NULL default '0',
  target VARCHAR(255) NOT NULL default '',
  referrer VARCHAR(255) NOT NULL default '',
  token varchar(80) NOT NULL default '',
  timestamp int(11) NOT NULL default '0',
  orgOpenpsaObtype int(11) NOT NULL default '0',
  sitegroup int(11) NOT NULL default '0',
  PRIMARY KEY (id),
  KEY org_openpsa_link_log_sitegroup_idx(sitegroup)
);
ALTER TABLE org_openpsa_link_log ADD COLUMN message int(11) NOT NULL default '0';
create index org_openpsa_link_log_person_idx on org_openpsa_link_log(person);
create index org_openpsa_link_log_message_idx on org_openpsa_link_log(message);
create index org_openpsa_link_log_token_idx on org_openpsa_link_log(token);
create index org_openpsa_link_log_timestamp_idx on org_openpsa_link_log(timestamp);
create index org_openpsa_link_log_orgOpenpsaObtype_idx on org_openpsa_link_log(orgOpenpsaObtype);
