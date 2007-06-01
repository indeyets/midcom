CREATE TABLE fi_mik_flight (
  id int(11) NOT NULL auto_increment,
  start int(11) NOT NULL default '0',
  end int(11) NOT NULL default '0',

  origin varchar(4) NOT NULL default '',
  destination varchar(4) NOT NULL default '',

  aircraft int(11) NOT NULL default '0',
  pilot int(11) NOT NULL default '0',
  operator int(11) NOT NULL default '0',

  hours float NOT NULL default '0',

  description text NOT NULL default '',

  tentative int(11) NOT NULL default '0',
  externalGuid varchar(255) NOT NULL default '',  
  
  created varchar(255) NOT NULL default '',
  revisor int(11) NOT NULL default '0',
  creator int(11) NOT NULL default '0',
  revised varchar(255) NOT NULL default '',
  revision int(11) NOT NULL default '0',
  guid varchar(80) NOT NULL default '',
  sitegroup int(11) NOT NULL default '0',
  metadata_creator varchar(80) NOT NULL default '',
  metadata_created datetime NOT NULL default '0000-00-00 00:00:00',
  metadata_revisor varchar(80) NOT NULL default '',
  metadata_revised datetime NOT NULL default '0000-00-00 00:00:00',
  metadata_revision int(11) NOT NULL default '0',
  metadata_locker varchar(80) NOT NULL default '',
  metadata_locked datetime NOT NULL default '0000-00-00 00:00:00',
  metadata_approver varchar(80) NOT NULL default '',
  metadata_approved datetime NOT NULL default '0000-00-00 00:00:00',
  metadata_author varchar(80) NOT NULL default '',
  metadata_owner varchar(80) NOT NULL default '',
  metadata_schedule_start datetime NOT NULL default '0000-00-00 00:00:00',
  metadata_schedule_end datetime NOT NULL default '0000-00-00 00:00:00',
  metadata_hidden tinyint(1) default '0',
  metadata_nav_noentry tinyint(1) default '0',
  metadata_size int(11) NOT NULL default '0',
  PRIMARY KEY (id),
  KEY fi_mik_flight_sitegroup_idx(sitegroup),
  KEY fi_mik_flight_pilot_idx(pilot),
  KEY fi_mik_flight_operator_idx(operator),
  KEY fi_mik_flight_aircraft_idx(aircraft)
);
ALTER TABLE fi_mik_flight ADD COLUMN scoreorigin float NOT NULL default '0';
ALTER TABLE fi_mik_flight ADD COLUMN scoredestination float NOT NULL default '0';