CREATE TABLE net_nemein_favourites_favourite (
  objectType varchar(255) NOT NULL default '',
  objectGuid varchar(255) NOT NULL default '',
  objectTitle varchar(255) NOT NULL default '',
  #
  id int(11) NOT NULL auto_increment,
  sitegroup int(11) NOT NULL default '0',
  guid varchar(80) NOT NULL default '',
  PRIMARY KEY (id),
  KEY net_nemein_favourites_objectType_idx(«objectType«(30)),
  KEY net_nemein_favourites_objectGuid_idx(«objectGuid«(30)),
  KEY net_nemein_favourites_objectTitle_idx(«objectTitle«(30))
);

