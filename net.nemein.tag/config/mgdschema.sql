#Tag
CREATE TABLE net_nemein_tag (
  tag varchar(255) NOT NULL default '',
  url varchar(255) NOT NULL default '',
#
  id int(11) NOT NULL auto_increment,
  sitegroup int(11) NOT NULL default '0',
  guid varchar(80) NOT NULL default '',
  PRIMARY KEY (id),
  KEY net_nemein_tag_link_guid_idx(guid),
  KEY net_nemein_tag_link_sitegroup_idx(sitegroup),
  KEY net_nemein_tag_tag_idx(tag(30))
);
CREATE TABLE net_nemein_tag_link (
  fromComponent varchar(255) NOT NULL default '',
  fromClass varchar(255) NOT NULL default '',
  fromGuid varchar(80) NOT NULL default '',
  context varchar(255) NOT NULL default '',
  tag int(11) NOT NULL default '0',
#
  id int(11) NOT NULL auto_increment,
  sitegroup int(11) NOT NULL default '0',
  guid varchar(80) NOT NULL default '',
  PRIMARY KEY (id),
  KEY net_nemein_tag_link_guid_idx(guid),
  KEY net_nemein_tag_link_sitegroup_idx(sitegroup),
  KEY net_nemein_tag_link_fromComponent_idx(fromComponent),
  KEY net_nemein_tag_link_fromClass_idx(fromClass),
  KEY net_nemein_tag_link_fromGuid_idx(fromGuid),
  KEY net_nemein_tag_link_context_idx(context(30)),
  KEY net_nemein_tag_link_tag_idx(tag)
);
ALTER TABLE net_nemein_tag_link ADD COLUMN value varchar(255) NOT NULL default '';
CREATE INDEX net_nemein_tag_link_value_idx on net_nemein_tag_link (value(30));
