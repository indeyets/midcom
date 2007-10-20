CREATE TABLE net_nemein_featured_item (
  objectLocation varchar(255) NOT NULL default '',
  groupName varchar(255) NOT NULL default '',
  topicGuid varchar(255) NOT NULL default '',
  defaultStyle varchar(255) NOT NULL default '',
  itemOrder int(11) NOT NULL default '0',
  #
  id int(11) NOT NULL auto_increment,
  sitegroup int(11) NOT NULL default '0',
  guid varchar(80) NOT NULL default '',
  PRIMARY KEY (id),
  #
  KEY net_nemein_featured_item_objectLocation_idx(`objectLocation` (30)),
  KEY net_nemein_featured_item_groupName_idx(`groupName` (30)),
  KEY net_nemein_featured_item_topicGuid_idx(`topicGuid` (30)),
  KEY net_nemein_featured_item_defaultStyle_idx(`defaultStyle` (30)),
  KEY net_nemein_featured_item_itemOrder_idx(`itemOrder`)
);
ALTER TABLE net_nemein_featured_item ADD COLUMN title varchar(255) NOT NULL default '';