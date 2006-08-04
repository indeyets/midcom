#Creating columns created for org.openpsa.contacts
## org_openpsa_organization
ALTER TABLE grp ADD COLUMN country VARCHAR(255) NOT NULL default '';
ALTER TABLE grp ADD COLUMN phone VARCHAR(255) NOT NULL default '';
ALTER TABLE grp ADD COLUMN fax VARCHAR(255) NOT NULL default '';

ALTER TABLE grp ADD COLUMN postalStreet VARCHAR(255) NOT NULL default '';
ALTER TABLE grp ADD COLUMN postalPostcode VARCHAR(255) NOT NULL default '';
ALTER TABLE grp ADD COLUMN postalCity VARCHAR(255) NOT NULL default '';
ALTER TABLE grp ADD COLUMN postalCountry VARCHAR(255) NOT NULL default '';
ALTER TABLE grp ADD COLUMN invoiceStreet VARCHAR(255) NOT NULL default '';
ALTER TABLE grp ADD COLUMN invoicePostcode VARCHAR(255) NOT NULL default '';
ALTER TABLE grp ADD COLUMN invoiceCity VARCHAR(255) NOT NULL default '';
ALTER TABLE grp ADD COLUMN invoiceCountry VARCHAR(255) NOT NULL default '';

ALTER TABLE grp ADD COLUMN keywords VARCHAR(255) NOT NULL default '';
#Uniquenes checked with QB based on preference
ALTER TABLE grp ADD COLUMN customerId VARCHAR(255) NOT NULL default ''; 

#Used to determine object subtype (project vs task, etc)
ALTER TABLE grp ADD COLUMN orgOpenpsaObtype INT(11) NOT NULL default 0;
#will contain bitmask as integer
ALTER TABLE grp ADD COLUMN orgOpenpsaWgtype INT(11) NOT NULL default 0;
ALTER TABLE grp ADD COLUMN orgOpenpsaAccesstype int(11) NOT NULL default 0;

## org_openpsa_person
ALTER TABLE person ADD COLUMN fax VARCHAR(255) NOT NULL default '';
ALTER TABLE person ADD COLUMN country VARCHAR(255) NOT NULL default '';
#Used to determine object subtype (project vs task, etc)
ALTER TABLE person ADD COLUMN orgOpenpsaObtype INT(11) NOT NULL default 0;
#will contain bitmask as integer
ALTER TABLE person ADD COLUMN orgOpenpsaWgtype INT(11) NOT NULL default 0;
ALTER TABLE person ADD COLUMN orgOpenpsaAccesstype int(11) NOT NULL default 0;


#These will be implemented in Midgard core/data as well in the near future
#Increase username lenght
alter table person modify username varchar(255) NOT NULL default '';
#Increase parameter value size
alter table record_extension modify value text NOT NULL default '';
