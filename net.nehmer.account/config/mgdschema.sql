CREATE TABLE net_nehmer_accounts_invites_invite (
  id int(11) NOT NULL auto_increment,
  hash varchar(255) NOT NULL default '',
  email varchar(255) NOT NULL default '',
  buddy varchar(255) NOT NULL default '',
  # other fields/indexes as ALTER TABLE statements
  PRIMARY KEY  (id),
  KEY net_nehmer_accounts_invites_invite_hash_idx (`hash`(30)),
  KEY net_nehmer_accounts_invites_invite_email_idx (`email`(30)),
  KEY net_nehmer_accounts_invites_invite_buddy_idx (`buddy`(30))
);
ALTER TABLE net_nehmer_accounts_invites_invite ADD COLUMN sitegroup int(11) NOT NULL DEFAULT 0;
CREATE INDEX net_nehmer_accounts_invites_invite_sitegroup_idx on net_nehmer_accounts_invites_invite(sitegroup);

