CREATE TABLE net_nemein_feedcollector_topic (
  id int(11) NOT NULL auto_increment,
  node int(11) NOT NULL default '0',
  feedtopic int(11) NOT NULL default '0',
  title varchar(255) NULL default '',
  categories varchar(255) NULL default '',
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
  metadata_owner varchar(80) NOT NULL default '',
  metadata_schedule_start datetime NOT NULL default '0000-00-00 00:00:00',
  metadata_schedule_end datetime NOT NULL default '0000-00-00 00:00:00',
  metadata_hidden tinyint(1) default '0',
  metadata_nav_noentry tinyint(1) default '0',
  metadata_size int(11) NOT NULL default '0',
  metadata_authors longtext NOT NULL default '',
  metadata_published datetime NOT NULL default '0000-00-00 00:00:00',
  metadata_imported datetime NOT NULL default '0000-00-00 00:00:00',
  metadata_exported datetime NOT NULL default '0000-00-00 00:00:00',
  metadata_deleted tinyint(1) NOT NULL default '0',
  metadata_score int(11) NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY net_nemein_feedcollector_topic_guid_idx (guid),
  KEY net_nemein_feedcollector_topic_sitegroup_idx (sitegroup),
  KEY net_nemein_feedcollector_topic_node_idx (node),
  KEY net_nemein_feedcollector_topic_feedtopic_idx (feedtopic),
  KEY net_nemein_feedcollector_topic_title_idx (title(125))
);