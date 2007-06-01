CREATE TABLE net_nemein_netmon_host (
  id int(11) NOT NULL auto_increment,
  sitegroup int(11) NOT NULL default 0,
  name varchar(255) NOT NULL default '',
  title varchar(255) NOT NULL default '',
  description longtext NOT NULL default '',
  ipaddress varchar(255) NOT NULL default '',
  dnsname varchar(255) NOT NULL default '',
  `parent` int(11) NOT NULL default 0,
  nagiosextra longtext NOT NULL default '',
  contactgroup int(11) NOT NULL default 0,
# other fields/indexes as ALTER TABLE statements
  PRIMARY KEY  (id),
  KEY net_nemein_netmon_host_sitegroup_idx (sitegroup),
  KEY net_nemein_netmon_host_name_idx (name)
);

CREATE TABLE net_nemein_netmon_hostgroup (
  id int(11) NOT NULL auto_increment,
  sitegroup int(11) NOT NULL default 0,
  name varchar(255) NOT NULL default '',
  title varchar(255) NOT NULL default '',
  description longtext NOT NULL default '',
# other fields/indexes as ALTER TABLE statements
  PRIMARY KEY  (id),
  KEY net_nemein_netmon_hostgroup_sitegroup_idx (sitegroup),
  KEY net_nemein_netmon_hostgroup_name_idx (name)
);
ALTER TABLE net_nemein_netmon_hostgroup ADD COLUMN nagiosextra longtext NOT NULL default '';

CREATE TABLE net_nemein_netmon_hostgroup_member (
  id int(11) NOT NULL auto_increment,
  sitegroup int(11) NOT NULL default 0,
  hostgroup int(11) NOT NULL default 0,
  host int(11) NOT NULL default 0,
# other fields/indexes as ALTER TABLE statements
  PRIMARY KEY  (id),
  KEY net_nemein_netmon_hostgroup_member_sitegroup_idx (sitegroup),
  KEY net_nemein_netmon_hostgroup_member_hostgroup_idx (hostgroup),
  KEY net_nemein_netmon_hostgroup_member_host_idx (host)
);

