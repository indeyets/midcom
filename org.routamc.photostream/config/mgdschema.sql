#Photo
CREATE TABLE org_routamc_photostream_photo (
  id int(11) NOT NULL auto_increment,
  sitegroup int(11) NOT NULL default 0,
  photographer int(11) NOT NULL default 0,
  taken int(11) NOT NULL default 0,
  rating int(11) NOT NULL default 0,
  created datetime NOT NULL default '0000-00-00 00:00:00',
  creator int(11) NOT NULL default 0,
  revised datetime NOT NULL default '0000-00-00 00:00:00',
  revisor int(11) NOT NULL default 0,
  node int(11) NOT NULL default 0,
  archival int(11) NOT NULL default 0,
  main int(11) NOT NULL default 0,
  thumb int(11) NOT NULL default 0,
  PRIMARY KEY  (id),
  KEY org_routamc_photostream_photo_sitegroup_idx (sitegroup),
  KEY org_routamc_photostream_photo_photographer_idx (photographer),
  KEY org_routamc_photostream_photo_taken_idx (taken),
  KEY org_routamc_photostream_photo_node_idx (node),
  KEY org_routamc_photostream_photo_rating_idx (rating)
);
ALTER TABLE org_routamc_photostream_photo ADD COLUMN externalid varchar(255) NOT NULL DEFAULT '';
CREATE INDEX org_routamc_photostream_photo_externalid_idx on org_routamc_photostream_photo (externalid(100));
ALTER TABLE org_routamc_photostream_photo ADD COLUMN status int(11) NOT NULL DEFAULT 0;
CREATE INDEX org_routamc_photostream_photo_status_idx on org_routamc_photostream_photo (status);

CREATE TABLE org_routamc_photostream_photo_i (
  id int(11) NOT NULL auto_increment,
  sid int(11) NOT NULL default 0,
  title varchar(255) NOT NULL default '',
  description longtext NOT NULL,
  created datetime NOT NULL default '0000-00-00 00:00:00',
  lang int(11) NOT NULL default 0,
  sitegroup int(11) NOT NULL default 0,
  PRIMARY KEY  (id),
  KEY org_routamc_photostream_photo_i_sitegroup_idx (sitegroup),
  KEY org_routamc_photostream_photo_i_sid_idx (sid,lang),
  KEY org_routamc_photostream_photo_i_lang_idx (lang)
);
CREATE INDEX org_routamc_photostream_photo_i_title_idx on org_routamc_photostream_photo_i(title(200));
