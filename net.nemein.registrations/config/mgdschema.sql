#Creating columns created for net.nemein.calendar
## net_nemein_registrations_registration
CREATE TABLE net_nemein_registrations_registration (
  `id` int(11) NOT NULL auto_increment,
  `eid` int(11) NOT NULL default '0',
  `uid` int(11) NOT NULL default '0',
  `extra` longtext NOT NULL default '',
  `sitegroup` int(11) NOT NULL,
  `guid` varchar(80) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `net_nemein_registrations_registration_guid_idx` (`guid`),
  KEY `net_nemein_registrations_registration_sitegroup_idx` (`sitegroup`)
);
CREATE INDEX `net_nemein_registrations_registration_eid_idx` ON net_nemein_registrations_registration (`eid`);
CREATE INDEX `net_nemein_registrations_registration_uid_idx` ON net_nemein_registrations_registration (`uid`);
ALTER TABLE net_nemein_registrations_registration ADD COLUMN `state` int(11) NOT NULL default '0';
CREATE INDEX `net_nemein_registrations_registration_state_idx` ON net_nemein_registrations_registration (`state`);
