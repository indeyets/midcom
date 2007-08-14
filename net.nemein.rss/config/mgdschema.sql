CREATE TABLE net_nemein_rss_feed (
  id int(11) NOT NULL auto_increment,
  title varchar(255) NOT NULL default '',
  url text NOT null default '',
  node int(11) NOT NULL default '0',
  keepremoved int(11) default '0',
  latestupdate int(11) default '0',
  latestfetch int(11) default '0',
  created varchar(255) NOT NULL default '',
  revisor int(11) NOT NULL default '0',
  creator int(11) NOT NULL default '0',
  revised varchar(255) NOT NULL default '',
  revision int(11) NOT NULL default '0',
  guid varchar(80) NOT NULL default '',
  sitegroup int(11) NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY net_nemein_rss_feed_guid_idx (guid),
  KEY net_nemein_rss_feed_sitegroup_idx (sitegroup),
  KEY net_nemein_rss_feed_node_idx (node),
  KEY net_nemein_rss_feed_latestupdate_idx (latestupdate)
);
ALTER TABLE net_nemein_rss_feed ADD COLUMN autoapprove int(11) NOT NULL default '0';
ALTER TABLE net_nemein_rss_feed ADD COLUMN defaultauthor int(11) NOT NULL default '0';
ALTER TABLE net_nemein_rss_feed ADD COLUMN forceauthor int(11) NOT NULL default '0';