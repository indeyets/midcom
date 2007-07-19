CREATE TABLE net_nemein_bannedwords_word (
  bannedWord varchar(255) NOT NULL default '',
  #
  id int(11) NOT NULL auto_increment,
  sitegroup int(11) NOT NULL default '0',
  guid varchar(80) NOT NULL default '',
  PRIMARY KEY (id),
  #
  KEY net_nemein_bannedwords_word_idx (`bannedWord`(30)),
);
