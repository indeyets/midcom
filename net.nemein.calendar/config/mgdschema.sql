#Creating columns created for net.nemein.calendar
## net_nemein_calendar_event
CREATE TABLE net_nemein_calendar_event (
  `id` int(11) NOT NULL auto_increment,
  `node` int(11) NOT NULL default '0',
  `up` int(11) NOT NULL default '0',
  `type` int(11) NOT NULL default '0',
  `sitegroup` int(11) NOT NULL,
  `guid` varchar(80) NOT NULL,
  `name` varchar(255) NOT NULL default '',
  `start` datetime NOT NULL default '0000-00-00 00:00:00',
  `end` datetime NOT NULL default '0000-00-00 00:00:00',
  `openregistration` datetime NOT NULL default '0000-00-00 00:00:00',
  `closeregistration` datetime NOT NULL default '0000-00-00 00:00:00',
  `location` varchar(255) NOT NULL default '',
  `extra` longtext NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `net_nemein_calendar_event_guid_idx` (`guid`),
  KEY `net_nemein_calendar_event_sitegroup_idx` (`sitegroup`)
);
CREATE INDEX `net_nemein_calendar_event_node_idx` ON net_nemein_calendar_event (`node`);
CREATE INDEX `net_nemein_calendar_event_up_idx` ON net_nemein_calendar_event (`up`);
CREATE INDEX `net_nemein_calendar_event_start_idx` ON net_nemein_calendar_event (`start`);
CREATE INDEX `net_nemein_calendar_event_end_idx` ON net_nemein_calendar_event (`end`);
CREATE INDEX `net_nemein_calendar_event_openregistration_idx` ON net_nemein_calendar_event (`openregistration`);
CREATE INDEX `net_nemein_calendar_event_closeregistration_idx` ON net_nemein_calendar_event (`closeregistration`);

CREATE TABLE net_nemein_calendar_event_i (
  id int(11) NOT NULL auto_increment,
  sid int(11) NOT NULL default 0,
  title varchar(255) NOT NULL default '',
  description longtext NOT NULL,
  created datetime NOT NULL default '0000-00-00 00:00:00',
  lang int(11) NOT NULL default 0,
  sitegroup int(11) NOT NULL default 0,
  PRIMARY KEY  (id),
  KEY net_nemein_calendar_event_i_sitegroup_idx (sitegroup),
  KEY net_nemein_calendar_event_i_sid_idx (sid,lang),
  KEY net_nemein_calendar_event_i_lang_idx (lang)
);