#Linkto photo
CREATE TABLE org_routamc_gallery_photolink (
  id int(11) NOT NULL auto_increment,
  sitegroup int(11) NOT NULL default 0,
  node int(11) NOT NULL default 0,
  photo int(11) NOT NULL default 0,
# other fields/indexes as ALTER TABLE statements
  PRIMARY KEY  (id),
  KEY org_routamc_gallery_photolink_sitegroup_idx (sitegroup),
  KEY org_routamc_gallery_photolink_node_idx (node),
  KEY org_routamc_gallery_photolink_photo_idx (photo)
);

# Change the misspelled column name
ALTER TABLE org_routamc_gallery_photolink CHANGE COLUMN cencored censored int(11) NOT NULL default 0;

# Try to create censored column if it doesn't exist
ALTER TABLE org_routamc_gallery_photolink ADD COLUMN censored int(11) NOT NULL default 0;

# Add backwards support for Midgard v1.7 branch
ALTER TABLE org_routamc_gallery_photolink ADD COLUMN score int(11) NOT NULL default 0;
CREATE INDEX org_routamc_gallery_photolink_censored_idx on org_routamc_gallery_photolink (censored);
