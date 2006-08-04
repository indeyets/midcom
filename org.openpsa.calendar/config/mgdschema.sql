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
ALTER TABLE eventmember ADD COLUMN sendNotes INT(11) NOT NULL default 0;
ALTER TABLE eventmember ADD COLUMN hoursReported INT(11) NOT NULL default 0;
