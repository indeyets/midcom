#Creating columns created for net.nemein.calendar
## net_nemein_calendar_event

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