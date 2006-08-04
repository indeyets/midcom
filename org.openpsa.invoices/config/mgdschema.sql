#Invoice
CREATE TABLE org_openpsa_invoice (
  id int(11) NOT NULL auto_increment,
  sent int(11) NOT NULL default '0',  
  due int(11) NOT NULL default '0',
  paid int(11) NOT NULL default '0',
  invoiceNumber varchar(255) NOT NULL default '',
  description text NOT NULL default '',  
  sum float NOT NULL default '0',
  vat int(11) NOT NULL default '0',
  customer int(11) NOT NULL default '0',
  customerContact int(11) NOT NULL default '0',
  owner int(11) NOT NULL default '0',
  status int(11) NOT NULL default '0',
  sitegroup int(11) NOT NULL default '0',
  guid varchar(80) NOT NULL default '',
  PRIMARY KEY (id),
  KEY org_openpsa_invoice_sitegroup_idx(sitegroup),
  KEY org_openpsa_invoice_customer_idx(customer),
  KEY org_openpsa_invoice_due_idx(due),
  KEY org_openpsa_invoice_owner_idx(owner)
);
#metadata fields
ALTER TABLE org_openpsa_invoice ADD COLUMN created datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_invoice ADD COLUMN creator int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_invoice ADD COLUMN revisor int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_invoice ADD COLUMN revised datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_invoice ADD COLUMN revision int(11) NOT NULL default '0';

#Invoice hour member
CREATE TABLE org_openpsa_invoice_hour (
  id int(11) NOT NULL auto_increment,
  invoice int(11) NOT NULL default '0',
  hourReport int(11) NOT NULL default '0',
  sitegroup int(11) NOT NULL default '0',
  guid varchar(80) NOT NULL default '',
  PRIMARY KEY (id),
  KEY org_openpsa_invoice_hour_sitegroup_idx(sitegroup),
  KEY org_openpsa_invoice_hour_invoice_idx(invoice),
  KEY org_openpsa_invoice_hour_hour_report_idx(hourReport)
);
#metadata fields
ALTER TABLE org_openpsa_invoice_hour ADD COLUMN created datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_invoice_hour ADD COLUMN creator int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_invoice_hour ADD COLUMN revisor int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_invoice_hour ADD COLUMN revised datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_invoice_hour ADD COLUMN revision int(11) NOT NULL default '0';
