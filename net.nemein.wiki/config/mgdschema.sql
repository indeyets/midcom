CREATE TABLE net_nemein_wiki_link (
  id int(11) NOT NULL auto_increment,
  frompage int(11) NOT NULL default '0',
  topage varchar(255) NOT NULL default '',
  topageid int(11) NOT NULL default '0',
  created varchar(255) NOT NULL default '',
  revisor int(11) NOT NULL default '0',
  creator int(11) NOT NULL default '0',
  revised varchar(255) NOT NULL default '',
  revision int(11) NOT NULL default '0',
  guid varchar(80) NOT NULL default '',
  sitegroup int(11) NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY net_nemein_wiki_link_guid_idx (guid),
  KEY net_nemein_wiki_link_sitegroup_idx (sitegroup),
  KEY net_nemein_wiki_link_frompage_idx (frompage),
  KEY net_nemein_wiki_link_topage_idx (topage),
  KEY net_nemein_wiki_link_topageid_idx (topageid)
);