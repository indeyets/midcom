CREATE TABLE net_nehmer_comments_comment_db (
  id int(11) NOT NULL auto_increment,
  author varchar(255) NOT NULL default '',
  objectguid varchar(80) NOT NULL default '',
  title varchar(255) NOT NULL default '',
  content text NOT NULL default '',
  ip varchar(15) NOT NULL default '',
  status int(11) NOT NULL default '0',
  creator int(11) NOT NULL default '0',
  created varchar(255) NOT NULL default '',
  revisor int(11) NOT NULL default '0',
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
  PRIMARY KEY  (id)
);

CREATE INDEX net_nehmer_comments_comment_db_objectguid_idx
    ON net_nehmer_comments_comment_db(objectguid);

ALTER TABLE net_nehmer_comments_comment_db ADD COLUMN rating INT(11) NOT NULL default 0;