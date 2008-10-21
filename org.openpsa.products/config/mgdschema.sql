#Product
CREATE TABLE org_openpsa_products_product (
  id int(11) NOT NULL auto_increment,
  productGroup int(11) NOT NULL default 0,
  code varchar(255) NOT NULL default '',
  price float NOT NULL default '0',
  unit varchar(255) NOT NULL default '',
  start int(11) NOT NULL default 0,
  end int(11) NOT NULL default 0,  
  owner int(11) NOT NULL default 0,
  supplier int(11) NOT NULL default 0,

  delivery int(11) NOT NULL default '0',

  orgOpenpsaObtype int(11) NOT NULL default 0,
  sitegroup int(11) NOT NULL default 0,
  guid varchar(80) NOT NULL default '',
  PRIMARY KEY (id),
  KEY org_openpsa_products_product_sitegroup_idx(sitegroup),
  KEY org_openpsa_products_product_group_idx(productGroup),
  KEY org_openpsa_products_product_type_idx(orgOpenpsaObtype),
  KEY org_openpsa_products_product_code_idx(code),
  KEY org_openpsa_products_product_customer_idx(supplier),
  KEY org_openpsa_products_product_start_idx(start),
  KEY org_openpsa_products_product_end_idx(end),
  KEY org_openpsa_products_product_owner_idx(owner)
);
ALTER TABLE org_openpsa_products_product ADD COLUMN cost varchar(255) NOT NULL default '';
ALTER TABLE org_openpsa_products_product ADD COLUMN costType varchar(1) NOT NULL default '';
#metadata fields
ALTER TABLE org_openpsa_products_product ADD COLUMN created datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_products_product ADD COLUMN creator int(11) NOT NULL default 0;
ALTER TABLE org_openpsa_products_product ADD COLUMN revisor int(11) NOT NULL default 0;
ALTER TABLE org_openpsa_products_product ADD COLUMN revised datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_products_product ADD COLUMN revision int(11) NOT NULL default 0;
# Multilang table
CREATE TABLE org_openpsa_products_product_i (
  id int(11) NOT NULL auto_increment,
  sid int(11) NOT NULL default 0,
  title varchar(255) NOT NULL default '',
  description longtext NOT NULL,
  created datetime NOT NULL default '0000-00-00 00:00:00',
  lang int(11) NOT NULL default 0,
  sitegroup int(11) NOT NULL default 0,
  PRIMARY KEY  (id),
  KEY org_openpsa_products_product_i_sitegroup_idx (sitegroup),
  KEY org_openpsa_products_product_i_sid_idx (sid,lang),
  KEY org_openpsa_products_product_i_lang_idx (lang)
);
# it seems just about everyone wants to query by this field...
CREATE INDEX org_openpsa_products_product_i_title_idx on org_openpsa_products_product_i(title(200));

#Product Group
CREATE TABLE org_openpsa_products_product_group (
  id int(11) NOT NULL auto_increment,
  up int(11) NOT NULL default 0,
  code varchar(255) NOT NULL default '',
  orgOpenpsaObtype int(11) NOT NULL default 0,
  sitegroup int(11) NOT NULL default 0,
  guid varchar(80) NOT NULL default '',
  PRIMARY KEY (id),
  KEY org_openpsa_products_product_group_sitegroup_idx(sitegroup),
  KEY org_openpsa_products_product_group_code_idx(code),
  KEY org_openpsa_products_product_group_up_idx(up)
);
#metadata fields
ALTER TABLE org_openpsa_products_product_group ADD COLUMN created datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_products_product_group ADD COLUMN creator int(11) NOT NULL default 0;
ALTER TABLE org_openpsa_products_product_group ADD COLUMN revisor int(11) NOT NULL default 0;
ALTER TABLE org_openpsa_products_product_group ADD COLUMN revised datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_products_product_group ADD COLUMN revision int(11) NOT NULL default 0;
# Multilang table
CREATE TABLE org_openpsa_products_product_group_i (
  id int(11) NOT NULL auto_increment,
  sid int(11) NOT NULL default 0,
  title varchar(255) NOT NULL default '',
  description longtext NOT NULL,
  created datetime NOT NULL default '0000-00-00 00:00:00',
  lang int(11) NOT NULL default 0,
  sitegroup int(11) NOT NULL default 0,
  PRIMARY KEY  (id),
  KEY org_openpsa_products_product_group_i_sitegroup_idx (sitegroup),
  KEY org_openpsa_products_product_group_i_sid_idx (sid,lang),
  KEY org_openpsa_products_product_group_i_lang_idx (lang)
);
# it seems just about everyone wants to query by this field...
CREATE INDEX org_openpsa_products_product_group_i_title_idx on org_openpsa_products_product_group_i(title(200));


#Product Member
CREATE TABLE org_openpsa_products_product_member (
  id int(11) NOT NULL auto_increment,
  product int(11) NOT NULL default 0,
  component int(11) NOT NULL default 0,
  pieces int(11) NOT NULL default 0,  
  description text NOT NULL default '',
  orgOpenpsaObtype int(11) NOT NULL default 0,
  sitegroup int(11) NOT NULL default 0,
  guid varchar(80) NOT NULL default '',
  PRIMARY KEY (id),
  KEY org_openpsa_products_product_member_sitegroup_idx(sitegroup),
  KEY org_openpsa_products_product_member_product_idx(product),
  KEY org_openpsa_products_product_member_component_idx(component)
);
#metadata fields
ALTER TABLE org_openpsa_products_product_member ADD COLUMN created datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_products_product_member ADD COLUMN creator int(11) NOT NULL default 0;
ALTER TABLE org_openpsa_products_product_member ADD COLUMN revisor int(11) NOT NULL default 0;
ALTER TABLE org_openpsa_products_product_member ADD COLUMN revised datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_products_product_member ADD COLUMN revision int(11) NOT NULL default 0;
ALTER TABLE org_openpsa_products_product_member ADD COLUMN componentGroup int(11) NOT NULL default 0;