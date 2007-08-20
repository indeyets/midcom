#City
CREATE TABLE org_routamc_positioning_city (
  city varchar(255) NOT NULL default '',
  country varchar(255) NOT NULL default '',
  region varchar(255) NOT NULL default '',
  latitude double NOT NULL default 0,
  longitude double NOT NULL default 0,
#
  id int(11) NOT NULL auto_increment,
  sitegroup int(11) NOT NULL default '0',
  guid varchar(80) NOT NULL default '',
  PRIMARY KEY (id),
  KEY org_routamc_positioning_city_city_idx(city),
  KEY org_routamc_positioning_city_country_idx(country),
  KEY org_routamc_positioning_city_region_idx(region),
  KEY org_routamc_positioning_city_latitude_idx(latitude),
  KEY org_routamc_positioning_city_longitude_idx(longitude)
);
ALTER TABLE org_routamc_positioning_city ADD COLUMN alternatenames text NOT NULL default '';
ALTER TABLE org_routamc_positioning_city ADD COLUMN population int(11) NOT NULL default '0';
ALTER TABLE org_routamc_positioning_city ADD COLUMN altitude int(11) NOT NULL default '0';
#metadata fields
ALTER TABLE org_routamc_positioning_city ADD COLUMN created datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_routamc_positioning_city ADD COLUMN creator int(11) NOT NULL default '0';
ALTER TABLE org_routamc_positioning_city ADD COLUMN revisor int(11) NOT NULL default '0';
ALTER TABLE org_routamc_positioning_city ADD COLUMN revised datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_routamc_positioning_city ADD COLUMN revision int(11) NOT NULL default '0';
##
#aerodrome
CREATE TABLE org_routamc_positioning_aerodrome (
  icao varchar(4) NOT NULL default '',
  iata varchar(3) NOT NULL default '',
  name varchar(255) NOT NULL default '',
  latitude double NOT NULL default 0,
  longitude double NOT NULL default 0,
  city int(11) NOT NULL default '0',
  altitude int(11) NOT NULL default '0',
#
  id int(11) NOT NULL auto_increment,
  sitegroup int(11) NOT NULL default '0',
  guid varchar(80) NOT NULL default '',
  PRIMARY KEY (id),
  KEY org_routamc_positioning_aerodrome_icao_idx(icao),
  KEY org_routamc_positioning_aerodrome_iata_idx(iata),
  KEY org_routamc_positioning_aerodrome_city_idx(city),
  KEY org_routamc_positioning_aerodrome_latitude_idx(latitude),
  KEY org_routamc_positioning_aerodrome_longitude_idx(longitude)
);
#metadata fields
ALTER TABLE org_routamc_positioning_aerodrome ADD COLUMN created datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_routamc_positioning_aerodrome ADD COLUMN creator int(11) NOT NULL default '0';
ALTER TABLE org_routamc_positioning_aerodrome ADD COLUMN revisor int(11) NOT NULL default '0';
ALTER TABLE org_routamc_positioning_aerodrome ADD COLUMN revised datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_routamc_positioning_aerodrome ADD COLUMN revision int(11) NOT NULL default '0';
ALTER TABLE org_routamc_positioning_aerodrome ADD COLUMN wmo varchar(255) NOT NULL default '';
ALTER TABLE org_routamc_positioning_aerodrome ADD COLUMN country varchar(2) NOT NULL default '';
##
#Log
CREATE TABLE org_routamc_positioning_log (
  latitude double NOT NULL default 0,
  longitude double NOT NULL default 0,
  person int(11) NOT NULL default '0',
  date int(11) NOT NULL default '0',
  accuracy int(11) NOT NULL default '0',
  altitude int(11) NOT NULL default '0',
  importer varchar(255) NOT NULL default '',
#
  id int(11) NOT NULL auto_increment,
  sitegroup int(11) NOT NULL default '0',
  guid varchar(80) NOT NULL default '',
  PRIMARY KEY (id),
  KEY org_routamc_positioning_log_person_idx(person),
  KEY org_routamc_positioning_log_date_idx(date),
  KEY org_routamc_positioning_log_latitude_idx(latitude),
  KEY org_routamc_positioning_log_longitude_idx(longitude)
);
ALTER TABLE org_routamc_positioning_log ADD COLUMN bearing int(3) NOT NULL default '0';
#metadata fields
ALTER TABLE org_routamc_positioning_log ADD COLUMN created datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_routamc_positioning_log ADD COLUMN creator int(11) NOT NULL default '0';
ALTER TABLE org_routamc_positioning_log ADD COLUMN revisor int(11) NOT NULL default '0';
ALTER TABLE org_routamc_positioning_log ADD COLUMN revised datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_routamc_positioning_log ADD COLUMN revision int(11) NOT NULL default '0';
##
#Location
CREATE TABLE org_routamc_positioning_location (
  latitude double NOT NULL default 0,
  longitude double NOT NULL default 0,
  log int(11) NOT NULL default '0',
  date int(11) NOT NULL default '0',
  relation int(11) NOT NULL default '0',
  altitude int(11) NOT NULL default '0',
  parentclass varchar(255) NOT NULL default '',
  parent varchar(80) NOT NULL default '',
  parentcomponent varchar(255) NOT NULL default '',
#
  id int(11) NOT NULL auto_increment,
  sitegroup int(11) NOT NULL default '0',
  guid varchar(80) NOT NULL default '',
  PRIMARY KEY (id),
  KEY org_routamc_positioning_location_log_idx(log),
  KEY org_routamc_positioning_location_date_idx(date),
  KEY org_routamc_positioning_location_parentclass_idx(parentclass),
  KEY org_routamc_positioning_location_parent_idx(parent),
  KEY org_routamc_positioning_location_parentcomponent_idx(parentcomponent),
  KEY org_routamc_positioning_location_latitude_idx(latitude),
  KEY org_routamc_positioning_location_longitude_idx(longitude)
);
#metadata fields
ALTER TABLE org_routamc_positioning_location ADD COLUMN created datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_routamc_positioning_location ADD COLUMN creator int(11) NOT NULL default '0';
ALTER TABLE org_routamc_positioning_location ADD COLUMN revisor int(11) NOT NULL default '0';
ALTER TABLE org_routamc_positioning_location ADD COLUMN revised datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_routamc_positioning_location ADD COLUMN revision int(11) NOT NULL default '0';
