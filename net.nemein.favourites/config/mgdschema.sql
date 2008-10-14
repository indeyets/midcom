CREATE TABLE net_nemein_favourites_favourite (
  objectType varchar(255) NOT NULL default '',
  objectGuid varchar(255) NOT NULL default '',
  objectTitle varchar(255) NOT NULL default '',
  bury int(1) NOT NULL default '0',
  #
  id int(11) NOT NULL auto_increment,
  sitegroup int(11) NOT NULL default '0',
  guid varchar(80) NOT NULL default '',
  PRIMARY KEY (id),
);
create index net_nemein_favourites_objectType_idx on net_nemein_favourites(objectType);
create index net_nemein_favourites_objectGuid_idx on net_nemein_favourites(objectGuid);
create index net_nemein_favourites_objectTitle_idx on net_nemein_favourites(objectTitle);