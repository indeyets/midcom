#Article links
CREATE TABLE net_nehmer_static_link (
  id int(11) NOT NULL auto_increment,
  sitegroup int(11) NOT NULL default 0,
  topic int(11) NOT NULL default 0,
  article int(11) NOT NULL default 0,
# other fields/indexes as ALTER TABLE statements
  PRIMARY KEY  (id),
  KEY net_nehmer_static_link_sitegroup_idx (sitegroup),
  KEY net_nehmer_static_link_topic_idx (topic),
  KEY net_nehmer_static_link_article_idx (article)
);
