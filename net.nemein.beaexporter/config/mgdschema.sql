#state log
CREATE TABLE net_nemein_beaexporter_state (
  objectguid varchar(80) NOT NULL default '',
  objectaction varchar(15) NOT NULL default '',
  timestamp int(11) NOT NULL default '0',
  targeturl varchar(255) NOT NULL default '',
#
  id int(11) NOT NULL auto_increment,
  sitegroup int(11) NOT NULL default '0',
  guid varchar(80) NOT NULL default '',
  PRIMARY KEY (id),
  KEY net_nemein_beaexporter_state_guid_idx(guid),
  KEY net_nemein_beaexporter_state_objectaction_idx(objectaction),
  KEY net_nemein_beaexporter_state_timestamp_idx(timestamp)
);
#metadata fields
ALTER TABLE net_nemein_beaexporter_state ADD COLUMN created datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE net_nemein_beaexporter_state ADD COLUMN creator int(11) NOT NULL default '0';
ALTER TABLE net_nemein_beaexporter_state ADD COLUMN revisor int(11) NOT NULL default '0';
ALTER TABLE net_nemein_beaexporter_state ADD COLUMN revised datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE net_nemein_beaexporter_state ADD COLUMN revision int(11) NOT NULL default '0';
