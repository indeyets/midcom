#Creating columns created for org.openpsa.contacts
## org_openpsa_event
ALTER TABLE event ADD COLUMN location VARCHAR(255) NOT NULL default '';
ALTER TABLE event ADD COLUMN tentative INT(11) NOT NULL default 0;
ALTER TABLE event ADD COLUMN externalGuid VARCHAR(255) NOT NULL default '';
ALTER TABLE event ADD COLUMN vCalSerialized TEXT NOT NULL default '';

#Used to determine object subtype (project vs task, etc)
ALTER TABLE event ADD COLUMN orgOpenpsaObtype INT(11) NOT NULL default 0;
#will contain bitmask as integer
ALTER TABLE event ADD COLUMN orgOpenpsaWgtype INT(11) NOT NULL default 0;
ALTER TABLE event ADD COLUMN orgOpenpsaAccesstype INT(11) NOT NULL default 0;
ALTER TABLE event ADD COLUMN orgOpenpsaOwnerWg VARCHAR(255) NOT NULL default '';

## org_openpsa_eventmember
ALTER TABLE eventmember ADD COLUMN orgOpenpsaObtype INT(11) NOT NULL default 0;
ALTER TABLE eventmember ADD COLUMN hoursReported INT(11) NOT NULL default 0;
# 17 metadata
ALTER TABLE event ADD COLUMN created datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE event ADD COLUMN creator int(11) NOT NULL default '0';
ALTER TABLE event ADD COLUMN revisor int(11) NOT NULL default '0';
ALTER TABLE event ADD COLUMN revised datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE event ADD COLUMN revision int(11) NOT NULL default '0';
#
ALTER TABLE eventmember ADD COLUMN created datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE eventmember ADD COLUMN creator int(11) NOT NULL default '0';
ALTER TABLE eventmember ADD COLUMN revisor int(11) NOT NULL default '0';
ALTER TABLE eventmember ADD COLUMN revised datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE eventmember ADD COLUMN revision int(11) NOT NULL default '0';
#

# resources
# org_openpsa_calendar_resource
CREATE TABLE org_openpsa_calendar_resource (
  id int(11) NOT NULL auto_increment,
  created datetime NOT NULL default '0000-00-00 00:00:00',
  creator int(11) NOT NULL default '0',
  revisor int(11) NOT NULL default '0',
  revised datetime NOT NULL default '0000-00-00 00:00:00',
  title varchar(255) NOT NULL default '',
  name varchar(250) NOT NULL default '',
  location varchar(250) NOT NULL default '',
  type varchar(250) NOT NULL default '',
  owner int(11) NOT NULL default '0',
  period varchar(1) NOT NULL default '0',
  capacity float(11) NOT NULL default '0',
  description text NOT NULL default '',
#
  sitegroup int(11) NOT NULL default '0',
  guid varchar(80) NOT NULL default '',
  PRIMARY KEY (id),
  KEY org_openpsa_calendar_resource_sitegroup_idx(sitegroup),
  KEY org_openpsa_calendar_resource_guid_idx(guid),
  KEY org_openpsa_calendar_resource_name_idx(name),
  KEY org_openpsa_calendar_resource_location_idx(location),
  KEY org_openpsa_calendar_resource_type_idx(type)
);
ALTER TABLE org_openpsa_calendar_resource ADD COLUMN revision int(11) NOT NULL default '0';

# org_openpsa_calendar_event_resource
CREATE TABLE org_openpsa_calendar_event_resource (
  id int(11) NOT NULL auto_increment,
  created datetime NOT NULL default '0000-00-00 00:00:00',
  creator int(11) NOT NULL default '0',
  revisor int(11) NOT NULL default '0',
  revised datetime NOT NULL default '0000-00-00 00:00:00',
  resource int(11) NOT NULL default '0',
  event int(11) NOT NULL default '0',
  description text NOT NULL default '',
#
  sitegroup int(11) NOT NULL default '0',
  guid varchar(80) NOT NULL default '',
  PRIMARY KEY (id),
  KEY org_openpsa_calendar_event_resource_sitegroup_idx(sitegroup),
  KEY org_openpsa_calendar_event_resource_guid_idx(guid),
  KEY org_openpsa_calendar_event_resource_event_idx(event),
  KEY org_openpsa_calendar_event_resource_resource_idx(resource)
);
ALTER TABLE org_openpsa_calendar_event_resource ADD COLUMN revision int(11) NOT NULL default '0';
