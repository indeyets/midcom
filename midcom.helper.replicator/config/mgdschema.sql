#replicator_subscription
CREATE TABLE midcom_helper_replicator_subscription (
  id int(11) NOT NULL auto_increment,
  sitegroup int(11) NOT NULL default 0,
  status int(11) NOT NULL default 0,
  title varchar(255) NOT NULL default '',
  description longtext NOT NULL default '',
  exporter varchar(255) NOT NULL default '',
  transporter varchar(255) NOT NULL default '',
# other fields/indexes as ALTER TABLE statements
  PRIMARY KEY  (id),
  KEY midcom_helper_replicator_subscription_sitegroup_idx (sitegroup),
  KEY midcom_helper_replicator_subscription_status_idx (status)
);
ALTER TABLE midcom_helper_replicator_subscription ADD COLUMN filtersSerialized text NOT NULL default '';
